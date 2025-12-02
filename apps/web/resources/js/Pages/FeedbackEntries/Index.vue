<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { 
    MagnifyingGlassIcon, 
    FunnelIcon,
    CheckCircleIcon,
    ClockIcon,
    ExclamationCircleIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    feedbackEntries: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const searchTerm = ref('');
const selectedTipo = ref(props.filters.tipo || '');
const selectedStatus = ref(props.filters.status || '');

const tipos = [
    { value: '', label: 'Todos os tipos' },
    { value: 'reclamacao', label: 'Reclamação' },
    { value: 'sugestao', label: 'Sugestão' },
    { value: 'elogio', label: 'Elogio' },
    { value: 'duvida', label: 'Dúvida' },
];

const statusOptions = [
    { value: '', label: 'Todos os status' },
    { value: 'pendente', label: 'Pendente' },
    { value: 'em_analise', label: 'Em Análise' },
    { value: 'resolvido', label: 'Resolvido' },
    { value: 'cancelado', label: 'Cancelado' },
];

const applyFilters = () => {
    router.get(route('feedback-entries.index'), {
        tipo: selectedTipo.value,
        status: selectedStatus.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getStatusColor = (status) => {
    const colors = {
        pendente: 'bg-yellow-100 text-yellow-800',
        em_analise: 'bg-blue-100 text-blue-800',
        resolvido: 'bg-green-100 text-green-800',
        cancelado: 'bg-gray-100 text-gray-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getStatusIcon = (status) => {
    const icons = {
        pendente: ClockIcon,
        em_analise: ExclamationCircleIcon,
        resolvido: CheckCircleIcon,
        cancelado: XCircleIcon,
    };
    return icons[status] || ClockIcon;
};

const getTipoColor = (tipo) => {
    const colors = {
        reclamacao: 'bg-red-100 text-red-800',
        sugestao: 'bg-purple-100 text-purple-800',
        elogio: 'bg-green-100 text-green-800',
        duvida: 'bg-blue-100 text-blue-800',
    };
    return colors[tipo] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const filteredEntries = computed(() => {
    if (!searchTerm.value) {
        return props.feedbackEntries.data;
    }
    
    return props.feedbackEntries.data.filter(entry => {
        const search = searchTerm.value.toLowerCase();
        return entry.titulo?.toLowerCase().includes(search) ||
               entry.descricao?.toLowerCase().includes(search) ||
               entry.sender_identifier?.toLowerCase().includes(search);
    });
});
</script>

<template>
    <AppLayout title="Ouvidoria">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Ouvidoria
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Header com filtros -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <!-- Busca -->
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        v-model="searchTerm"
                                        type="text"
                                        placeholder="Buscar por título, descrição ou remetente..."
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    />
                                </div>
                            </div>

                            <!-- Filtro por Tipo -->
                            <div class="w-full sm:w-48">
                                <select
                                    v-model="selectedTipo"
                                    @change="applyFilters"
                                    class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                >
                                    <option v-for="tipo in tipos" :key="tipo.value" :value="tipo.value">
                                        {{ tipo.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Filtro por Status -->
                            <div class="w-full sm:w-48">
                                <select
                                    v-model="selectedStatus"
                                    @change="applyFilters"
                                    class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                >
                                    <option v-for="status in statusOptions" :key="status.value" :value="status.value">
                                        {{ status.label }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Estatísticas -->
                        <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
                            <span>Total: {{ feedbackEntries.total }} registro(s)</span>
                            <span v-if="searchTerm">Mostrando {{ filteredEntries.length }} de {{ feedbackEntries.data.length }}</span>
                        </div>
                    </div>

                    <!-- Lista de Feedback Entries -->
                    <div class="divide-y divide-gray-200">
                        <div v-if="filteredEntries.length === 0" class="p-8 text-center text-gray-500">
                            <p class="text-lg">Nenhum registro encontrado</p>
                            <p class="text-sm mt-2">Tente ajustar os filtros ou realizar uma nova busca</p>
                        </div>

                        <Link
                            v-for="entry in filteredEntries"
                            :key="entry.id"
                            :href="route('feedback-entries.show', entry.id)"
                            class="block hover:bg-gray-50 transition-colors duration-150"
                        >
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <!-- Título e badges -->
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="text-lg font-medium text-gray-900 truncate">
                                                {{ entry.titulo || 'Sem título' }}
                                            </h3>
                                            <span 
                                                :class="[getTipoColor(entry.tipo), 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium']"
                                            >
                                                {{ entry.tipo }}
                                            </span>
                                        </div>

                                        <!-- Descrição -->
                                        <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                                            {{ entry.descricao }}
                                        </p>

                                        <!-- Metadados -->
                                        <div class="flex items-center gap-4 text-sm text-gray-500">
                                            <span class="flex items-center">
                                                <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                                </svg>
                                                {{ entry.sender_identifier || 'Desconhecido' }}
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                </svg>
                                                {{ formatDate(entry.created_at) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Status badge -->
                                    <div class="ml-4 flex-shrink-0">
                                        <span 
                                            :class="[getStatusColor(entry.status), 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium']"
                                        >
                                            <component 
                                                :is="getStatusIcon(entry.status)" 
                                                class="mr-1.5 h-4 w-4"
                                            />
                                            {{ entry.status.replace('_', ' ') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Paginação -->
                    <div v-if="feedbackEntries.links && feedbackEntries.links.length > 3" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex flex-wrap justify-center gap-1">
                            <Link
                                v-for="(link, index) in feedbackEntries.links"
                                :key="index"
                                :href="link.url"
                                :class="[
                                    'px-3 py-2 text-sm font-medium rounded-md',
                                    link.active
                                        ? 'bg-indigo-600 text-white'
                                        : link.url
                                        ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                ]"
                                :preserve-scroll="true"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

