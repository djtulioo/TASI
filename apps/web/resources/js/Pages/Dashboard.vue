<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { 
    ClockIcon, 
    CheckCircleIcon, 
    InboxIcon, 
    ChartBarIcon 
} from '@heroicons/vue/24/outline';

defineProps({
    stats: Object,
});
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Visão Geral
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 border-l-4 border-indigo-500">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                                <InboxIcon class="h-6 w-6" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total de Chamados</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.total }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pendentes -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                <ClockIcon class="h-6 w-6" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Pendentes</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.pending }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Em Análise -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 border-l-4 border-blue-500">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <ChartBarIcon class="h-6 w-6" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Em Análise</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.analyzing }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Resolvidos -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 border-l-4 border-green-500">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <CheckCircleIcon class="h-6 w-6" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Resolvidos</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.resolved }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Chamados Recentes</h3>
                        <Link :href="route('feedback-entries.index')" class="text-sm text-indigo-600 hover:text-indigo-900 hover:underline">
                            Ver todos
                        </Link>
                    </div>
                    <div class="p-6">
                        <div v-if="stats.recent.length === 0" class="text-center text-gray-500 py-4">
                            Nenhum chamado recente.
                        </div>
                        <ul v-else class="divide-y divide-gray-200">
                            <li v-for="ticket in stats.recent" :key="ticket.id" class="py-4 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-indigo-600 truncate">{{ ticket.title }}</p>
                                    <p class="text-sm text-gray-500">{{ ticket.description }}</p>
                                    <p class="text-xs text-gray-400 mt-1">Recebido {{ ticket.date }} • Tipo: {{ ticket.type }}</p>
                                </div>
                                <div>
                                    <span :class="{
                                        'bg-yellow-100 text-yellow-800': ticket.status === 'pendente',
                                        'bg-blue-100 text-blue-800': ticket.status === 'em_analise',
                                        'bg-green-100 text-green-800': ticket.status === 'resolvido',
                                        'bg-gray-100 text-gray-800': ticket.status === 'cancelado'
                                    }" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ ticket.status }}
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
