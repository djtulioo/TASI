<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { 
    ArrowLeftIcon,
    CheckCircleIcon,
    ClockIcon,
    ExclamationCircleIcon,
    XCircleIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    feedbackEntry: {
        type: Object,
        required: true
    }
});

const form = useForm({
    status: props.feedbackEntry.status,
    titulo: props.feedbackEntry.titulo,
});

const isEditing = ref(false);

const statusOptions = [
    { value: 'pendente', label: 'Pendente', color: 'bg-yellow-100 text-yellow-800' },
    { value: 'em_analise', label: 'Em Análise', color: 'bg-blue-100 text-blue-800' },
    { value: 'resolvido', label: 'Resolvido', color: 'bg-green-100 text-green-800' },
    { value: 'cancelado', label: 'Cancelado', color: 'bg-gray-100 text-gray-800' },
];

const updateFeedback = () => {
    form.put(route('feedback-entries.update', props.feedbackEntry.id), {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};

const deleteFeedback = () => {
    if (confirm('Tem certeza que deseja excluir este feedback?')) {
        router.delete(route('feedback-entries.destroy', props.feedbackEntry.id));
    }
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
</script>

<template>
    <AppLayout title="Detalhes do Feedback">
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <button
                        @click="router.visit(route('feedback-entries.index'))"
                        class="mr-4 text-gray-600 hover:text-gray-900"
                    >
                        <ArrowLeftIcon class="h-6 w-6" />
                    </button>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Detalhes do Feedback
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Header do Card -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span 
                                        :class="[getTipoColor(feedbackEntry.tipo), 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium']"
                                    >
                                        {{ feedbackEntry.tipo }}
                                    </span>
                                    <span 
                                        :class="[getStatusColor(feedbackEntry.status), 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium']"
                                    >
                                        {{ feedbackEntry.status.replace('_', ' ') }}
                                    </span>
                                </div>
                                <h1 class="text-2xl font-bold text-gray-900 mt-2">
                                    {{ feedbackEntry.titulo || 'Sem título' }}
                                </h1>
                            </div>
                            <button
                                @click="deleteFeedback"
                                class="ml-4 p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md transition-colors"
                                title="Excluir feedback"
                            >
                                <TrashIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Informações Principais -->
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">
                            Descrição
                        </h3>
                        <p class="text-gray-900 whitespace-pre-wrap leading-relaxed">
                            {{ feedbackEntry.descricao }}
                        </p>
                    </div>

                    <!-- Metadados -->
                    <div class="p-6 bg-gray-50 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Remetente</h4>
                            <p class="text-gray-900 flex items-center">
                                <svg class="mr-2 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                                {{ feedbackEntry.sender_identifier || 'Desconhecido' }}
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Canal</h4>
                            <p class="text-gray-900 flex items-center">
                                <svg class="mr-2 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z" />
                                    <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z" />
                                </svg>
                                {{ feedbackEntry.channel?.name || 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Data de Criação</h4>
                            <p class="text-gray-900 flex items-center">
                                <svg class="mr-2 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                </svg>
                                {{ formatDate(feedbackEntry.created_at) }}
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Última Atualização</h4>
                            <p class="text-gray-900 flex items-center">
                                <svg class="mr-2 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                </svg>
                                {{ formatDate(feedbackEntry.updated_at) }}
                            </p>
                        </div>

                        <div v-if="feedbackEntry.conversation_id" class="md:col-span-2">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">ID da Conversa</h4>
                            <p class="text-gray-900 font-mono text-sm">
                                #{{ feedbackEntry.conversation_id }}
                            </p>
                        </div>
                    </div>

                    <!-- Seção de Edição de Status -->
                    <div class="p-6 border-t border-gray-200">
                        <div v-if="!isEditing" class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Gerenciar Status</h3>
                            <button
                                @click="isEditing = true"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors"
                            >
                                Editar Status
                            </button>
                        </div>

                        <form v-else @submit.prevent="updateFeedback" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Título
                                </label>
                                <input
                                    v-model="form.titulo"
                                    type="text"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="Digite um título (opcional)"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Status
                                </label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <label
                                        v-for="option in statusOptions"
                                        :key="option.value"
                                        :class="[
                                            'relative flex items-center justify-center px-4 py-3 border rounded-lg cursor-pointer focus:outline-none transition-all',
                                            form.status === option.value
                                                ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-500'
                                                : 'border-gray-300 hover:border-gray-400'
                                        ]"
                                    >
                                        <input
                                            v-model="form.status"
                                            type="radio"
                                            :value="option.value"
                                            class="sr-only"
                                        />
                                        <span :class="[option.color, 'text-sm font-medium px-2 py-1 rounded']">
                                            {{ option.label }}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 pt-4">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
                                </button>
                                <button
                                    type="button"
                                    @click="isEditing = false; form.reset()"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors"
                                >
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

