<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    initialConversations: {
        type: Array,
        default: () => []
    },
    channelId: {
        type: Number,
        required: true
    }
});

const conversations = ref(props.initialConversations);
const selectedConversation = ref(conversations.value.length > 0 ? conversations.value[0] : null);
const messages = ref([]);
const newMessage = ref('');

const fetchMessages = async (senderId) => {
    try {
        const response = await axios.get(route('chat.messages', { senderId }));
        messages.value = response.data;
    } catch (error) {
        console.error('Erro ao buscar mensagens:', error);
    }
};

// Carregar mensagens da primeira conversa ao iniciar
if (selectedConversation.value) {
    fetchMessages(selectedConversation.value.id);
}

// Observar mudanças nas conversas iniciais (ex: troca de canal)
import { watch } from 'vue';

watch(() => props.initialConversations, (newConversations) => {
    conversations.value = newConversations;
    selectedConversation.value = newConversations.length > 0 ? newConversations[0] : null;
    
    if (selectedConversation.value) {
        fetchMessages(selectedConversation.value.id);
    } else {
        messages.value = [];
    }
});



const selectChat = (chat) => {
    selectedConversation.value = chat;
    fetchMessages(chat.id);
};

const sendMessage = async () => {
    if (!newMessage.value.trim() || !selectedConversation.value) return;

    const messageText = newMessage.value;
    newMessage.value = ''; // Limpar input imediatamente para UX

    try {
        const response = await axios.post(route('chat.send', { senderId: selectedConversation.value.id }), {
            message: messageText
        });

        // Adicionar mensagem retornada pelo backend na lista
        messages.value.push(response.data);
    } catch (error) {
        console.error('Erro ao enviar mensagem:', error);
        // Opcional: Mostrar erro para o usuário ou restaurar o texto no input
        newMessage.value = messageText;
    }
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
            <div class="flex-1 flex flex-col" v-if="selectedConversation">
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
                        v-for="message in messages"
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
            
            <!-- Placeholder quando nenhuma conversa selecionada -->
            <div class="flex-1 flex items-center justify-center bg-gray-50" v-else>
                <div class="text-center">
                    <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        💬
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Nenhuma conversa selecionada</h3>
                    <p class="text-gray-500 mt-1">Selecione um contato para ver as mensagens</p>
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
