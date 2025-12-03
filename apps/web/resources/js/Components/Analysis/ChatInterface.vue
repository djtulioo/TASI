<script setup>
import { ref, watch, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
    dateRange: {
        type: Object,
        required: true
    }
});

const messages = ref([
    { role: 'assistant', content: 'Olá! Sou seu assistente de análise. Faça perguntas sobre as conversas do período selecionado.' }
]);
const userInput = ref('');
const loading = ref(false);
const messagesContainer = ref(null);

// Limpa o chat se mudar drasticamente o período (opcional, aqui mantemos o histórico)
watch(() => props.dateRange, () => {
    messages.value.push({ 
        role: 'system', 
        content: `Período de análise alterado para: ${props.dateRange.start} até ${props.dateRange.end}` 
    });
});

async function sendMessage() {
    if (!userInput.value.trim() || loading.value) return;

    const userMsg = userInput.value;
    messages.value.push({ role: 'user', content: userMsg });
    userInput.value = '';
    loading.value = true;
    scrollToBottom();

    try {
        const response = await axios.post(route('analysis.chat'), {
            message: userMsg,
            start_date: props.dateRange.start,
            end_date: props.dateRange.end,
            history: messages.value.filter(m => m.role !== 'system') // Envia histórico limpo
        });

        messages.value.push({ role: 'assistant', content: response.data.response });
    } catch (e) {
        messages.value.push({ role: 'system', content: 'Erro ao processar mensagem. Tente novamente.' });
        console.error(e);
    } finally {
        loading.value = false;
        scrollToBottom();
    }
}

function scrollToBottom() {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
}
</script>

<template>
    <div class="flex flex-col h-[600px] border border-gray-200 rounded-lg bg-gray-50">
        <!-- Messages Area -->
        <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
            <div v-for="(msg, index) in messages" :key="index" :class="{'text-center': msg.role === 'system'}">
                
                <div v-if="msg.role === 'system'" class="text-xs text-gray-400 italic">
                    {{ msg.content }}
                </div>

                <div v-else class="flex" :class="{'justify-end': msg.role === 'user', 'justify-start': msg.role === 'assistant'}">
                    <div 
                        class="max-w-[80%] rounded-lg px-4 py-2 shadow-sm"
                        :class="{
                            'bg-indigo-600 text-white': msg.role === 'user',
                            'bg-white text-gray-800 border border-gray-200': msg.role === 'assistant'
                        }"
                    >
                        <p class="whitespace-pre-wrap text-sm">{{ msg.content }}</p>
                    </div>
                </div>

            </div>
            
            <div v-if="loading" class="flex justify-start">
                <div class="bg-white border border-gray-200 rounded-lg px-4 py-2 shadow-sm">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-4 bg-white border-t border-gray-200 rounded-b-lg">
            <div class="flex space-x-2">
                <input 
                    v-model="userInput" 
                    @keyup.enter="sendMessage"
                    type="text" 
                    placeholder="Pergunte sobre as conversas..." 
                    class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                    :disabled="loading"
                >
                <button 
                    @click="sendMessage" 
                    :disabled="loading || !userInput.trim()"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition"
                >
                    Enviar
                </button>
            </div>
        </div>
    </div>
</template>
