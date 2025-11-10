<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';

// Mock data - 3 conversas
const conversations = ref([
    {
        id: 1,
        name: 'Equipe de Desenvolvimento',
        avatar: '👨‍💻',
        lastMessage: 'Vamos revisar o código amanhã',
        timestamp: '10:30'
    },
    {
        id: 2,
        name: 'Projeto Frontend',
        avatar: '🎨',
        lastMessage: 'O design ficou ótimo!',
        timestamp: '09:15'
    },
    {
        id: 3,
        name: 'Suporte Técnico',
        avatar: '🔧',
        lastMessage: 'Problema resolvido',
        timestamp: 'Ontem'
    }
]);

// Mock messages para cada conversa
const allMessages = ref({
    1: [
        { id: 1, text: 'Olá pessoal, como está o andamento do projeto?', direction: 'in', timestamp: '10:25' },
        { id: 2, text: 'Está indo bem! Terminei a parte de autenticação.', direction: 'out', timestamp: '10:27' },
        { id: 3, text: 'Ótimo trabalho! Vamos revisar o código amanhã', direction: 'in', timestamp: '10:30' }
    ],
    2: [
        { id: 4, text: 'Enviei a nova versão do design', direction: 'in', timestamp: '09:10' },
        { id: 5, text: 'Já recebi, vou dar uma olhada', direction: 'out', timestamp: '09:12' },
        { id: 6, text: 'O design ficou ótimo!', direction: 'out', timestamp: '09:15' }
    ],
    3: [
        { id: 7, text: 'Estou com um problema no servidor', direction: 'in', timestamp: 'Ontem' },
        { id: 8, text: 'Pode me dar mais detalhes?', direction: 'out', timestamp: 'Ontem' },
        { id: 9, text: 'Problema resolvido', direction: 'in', timestamp: 'Ontem' }
    ]
});

const selectedConversation = ref(conversations.value[0]);
const newMessage = ref('');

const selectedMessages = computed(() => {
    return allMessages.value[selectedConversation.value.id] || [];
});

const selectChat = (chat) => {
    selectedConversation.value = chat;
};

const sendMessage = () => {
    if (!newMessage.value.trim()) return;

    const message = {
        id: Date.now(),
        text: newMessage.value,
        direction: 'out',
        timestamp: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
    };

    allMessages.value[selectedConversation.value.id].push(message);
    newMessage.value = '';
};
</script>

<template>
    <AppLayout title="Chat">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Chat
            </h2>
        </template>

        <div class="flex h-[calc(100vh-140px)] bg-white rounded-lg shadow-lg overflow-hidden max-w-full border-t border-gray-200">
            <!-- Sidebar com lista de chats -->
            <div class="w-80 bg-gray-50 border-r border-gray-200 flex flex-col">
                <!-- Header do sidebar -->
                <div class="p-4 border-b border-gray-200 bg-white">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm">
                            💬
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Conversas</h3>
                            <p class="text-sm text-gray-500">{{ conversations.length }} conversas</p>
                        </div>
                    </div>
                </div>

                <!-- Lista de chats -->
                <div class="flex-1 overflow-y-auto">
                    <div
                        v-for="chat in conversations"
                        :key="chat.id"
                        @click="selectChat(chat)"
                        :class="[
                            'p-4 border-b border-gray-100 cursor-pointer transition-colors hover:bg-gray-100',
                            selectedConversation?.id === chat.id ? 'bg-blue-50 border-blue-200' : ''
                        ]"
                    >
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-lg">
                                {{ chat.avatar }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-900">
                                        {{ chat.name }}
                                    </h4>
                                    <span class="text-xs text-gray-500">{{ chat.timestamp }}</span>
                                </div>
                                <p class="text-sm text-gray-600 truncate">{{ chat.lastMessage }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Área principal do chat -->
            <div class="flex-1 flex flex-col">
                <!-- Header do chat selecionado -->
                <div class="p-4 border-b border-gray-200 bg-white">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm">
                            {{ selectedConversation.avatar }}
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">{{ selectedConversation.name }}</h3>
                            <p class="text-sm text-gray-500">Online agora</p>
                        </div>
                    </div>
                </div>

                <!-- Área de mensagens -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
                    <div
                        v-for="message in selectedMessages"
                        :key="message.id"
                        :class="[
                            'flex',
                            message.direction === 'out' ? 'justify-end' : 'justify-start'
                        ]"
                    >
                        <div
                            :class="[
                                'max-w-xs lg:max-w-md px-4 py-2 rounded-lg',
                                message.direction === 'out'
                                    ? 'bg-blue-500 text-white'
                                    : 'bg-white text-gray-800 border border-gray-200'
                            ]"
                        >
                            <p class="text-sm break-words">{{ message.text }}</p>
                            <p class="text-xs opacity-70 mt-1">{{ message.timestamp }}</p>
                        </div>
                    </div>
                </div>

                <!-- Input para nova mensagem -->
                <div class="p-4 border-t border-gray-200 bg-white">
                    <div class="flex space-x-3">
                        <input
                            v-model="newMessage"
                            @keyup.enter="sendMessage"
                            type="text"
                            placeholder="Digite sua mensagem..."
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                        />
                        <button
                            @click="sendMessage"
                            class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            Enviar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Scrollbar personalizada */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
