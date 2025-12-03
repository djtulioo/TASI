<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import DateRangePicker from '@/Components/Analysis/DateRangePicker.vue';
import SummaryView from '@/Components/Analysis/SummaryView.vue';
import ChatInterface from '@/Components/Analysis/ChatInterface.vue';

const activeTab = ref('summary');
const dateRange = ref({
    start: new Date(new Date().setMonth(new Date().getMonth() - 1)).toISOString().split('T')[0],
    end: new Date().toISOString().split('T')[0]
});

</script>

<template>
    <AppLayout title="Análise de Conversas">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Análise de Conversas com IA
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    
                    <!-- Controls -->
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <DateRangePicker v-model="dateRange" />
                        
                        <div class="flex space-x-2 bg-gray-100 p-1 rounded-lg">
                            <button 
                                @click="activeTab = 'summary'"
                                :class="{'bg-white shadow text-indigo-600': activeTab === 'summary', 'text-gray-500 hover:text-gray-700': activeTab !== 'summary'}"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                            >
                                Resumo Executivo
                            </button>
                            <button 
                                @click="activeTab = 'chat'"
                                :class="{'bg-white shadow text-indigo-600': activeTab === 'chat', 'text-gray-500 hover:text-gray-700': activeTab !== 'chat'}"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                            >
                                Chat Inteligente (RAG)
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div v-if="activeTab === 'summary'">
                        <SummaryView :date-range="dateRange" />
                    </div>
                    
                    <div v-else>
                        <ChatInterface :date-range="dateRange" />
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
