<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import { marked } from 'marked';

const props = defineProps({
    dateRange: {
        type: Object,
        required: true
    }
});

const summary = ref('');
const loading = ref(false);
const error = ref(null);

const formattedSummary = computed(() => {
    return summary.value ? marked(summary.value) : '';
});

async function fetchSummary(includeToday = false) {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.post(route('analysis.summary'), {
            start_date: props.dateRange.start,
            end_date: props.dateRange.end,
            include_today: includeToday
        });
        summary.value = response.data.summary;
    } catch (e) {
        error.value = 'Erro ao gerar resumo. Tente novamente.';
        console.error(e);
    } finally {
        loading.value = false;
    }
}

// Recarrega quando as datas mudam
watch(() => props.dateRange, () => {
    fetchSummary(false);
}, { deep: true, immediate: true });

</script>

<template>
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Resumo do Período</h3>
            <button 
                @click="fetchSummary(true)" 
                :disabled="loading"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition"
            >
                <span v-if="loading">Gerando...</span>
                <span v-else>Atualizar com Dados de Hoje</span>
            </button>
        </div>

        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            {{ error }}
        </div>

        <div v-if="loading && !summary" class="animate-pulse flex space-x-4">
            <div class="flex-1 space-y-4 py-1">
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                <div class="space-y-2">
                    <div class="h-4 bg-gray-200 rounded"></div>
                    <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                </div>
            </div>
        </div>

        <div v-else class="prose max-w-none bg-gray-50 p-6 rounded-lg border border-gray-200" v-html="formattedSummary"></div>
    </div>
</template>
