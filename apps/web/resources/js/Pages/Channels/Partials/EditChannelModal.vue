<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    show: Boolean,
    channel: Object,
});

const emit = defineEmits(['close']);

const activeTab = ref('settings');
const avatarPreview = ref(null);

const form = useForm({
    name: '',
    avatar: null,
    chatbot_config: {
        context: '',
        urls: [],
        files: []
    },
});

watch(() => props.channel, (newChannel) => {
    if (newChannel) {
        form.name = newChannel.name;
        avatarPreview.value = newChannel.avatar_url;

        // Ensure chatbot_config is an object and has context
        let config = newChannel.chatbot_config;
        if (!config) {
            config = { context: '', urls: [], files: [] };
        } else if (typeof config === 'string') {
            try {
                config = JSON.parse(config);
            } catch (e) {
                config = { context: '', urls: [], files: [] };
            }
        }

        if (!config.context) config.context = '';
        if (!config.urls) config.urls = [];
        if (!config.files) config.files = [];

        form.chatbot_config = config;
    }
}, { immediate: true });

const handleAvatarChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.avatar = file;
        avatarPreview.value = URL.createObjectURL(file);
    }
};

const addUrl = () => {
    form.chatbot_config.urls.push('');
};

const removeUrl = (index) => {
    form.chatbot_config.urls.splice(index, 1);
};

const handleFileUpload = (event) => {
    const files = Array.from(event.target.files);
    files.forEach(file => {
        form.chatbot_config.files.push({
            name: file.name,
            file: file
        });
    });
};

const removeFile = (index) => {
    form.chatbot_config.files.splice(index, 1);
};

const updateChannel = () => {
    // Serializar chatbot_config como JSON string antes de enviar
    const formData = {
        name: form.name,
        chatbot_config: JSON.stringify(form.chatbot_config),
        _method: 'PUT', // Method spoofing para Laravel
    };

    // Adicionar avatar se houver um arquivo novo
    if (form.avatar instanceof File) {
        formData.avatar = form.avatar;
    }

    // Usar POST com method spoofing em vez de PUT quando há arquivos
    form.transform(() => formData)
        .post(route('channels.update', props.channel.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('close');
            },
            forceFormData: true,
        });
};

const deleteChannel = () => {
    if (confirm('Tem certeza que deseja excluir este canal? Todas as conversas serão perdidas.')) {
        form.delete(route('channels.destroy', props.channel.id), {
            preserveScroll: true,
            onSuccess: () => emit('close'),
        });
    }
};

const close = () => {
    emit('close');
    form.reset();
    form.clearErrors();
    avatarPreview.value = null;
    activeTab.value = 'settings';
};
</script>

<template>
    <DialogModal :show="show" @close="close" max-width="3xl">
        <template #title>
            Editar Canal
        </template>

        <template #content>
            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button
                        @click="activeTab = 'settings'"
                        :class="[
                            activeTab === 'settings'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                    >
                        Configurações
                    </button>
                    <button
                        @click="activeTab = 'knowledge'"
                        :class="[
                            activeTab === 'knowledge'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                    >
                        Base de Conhecimento
                    </button>
                </nav>
            </div>

            <!-- Settings Tab -->
            <div v-show="activeTab === 'settings'" class="space-y-6">
                <!-- Name -->
                <div>
                    <InputLabel for="name" value="Nome do Canal" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.name" class="mt-2" />
                </div>

                <!-- Avatar -->
                <div>
                    <InputLabel for="avatar" value="Avatar do Canal" />
                    <div class="mt-2 flex items-center gap-4">
                        <div class="size-20 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                            <img v-if="avatarPreview" :src="avatarPreview" alt="Avatar" class="size-full object-cover" />
                            <span v-else class="text-2xl text-gray-400">{{ form.name ? form.name[0].toUpperCase() : '?' }}</span>
                        </div>
                        <div class="flex-1">
                            <input
                                id="avatar"
                                type="file"
                                accept="image/*"
                                @change="handleAvatarChange"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            />
                            <p class="mt-1 text-xs text-gray-500">PNG, JPG ou GIF (máx. 2MB)</p>
                        </div>
                    </div>
                    <InputError :message="form.errors.avatar" class="mt-2" />
                </div>
            </div>

            <!-- Knowledge Base Tab -->
            <div v-show="activeTab === 'knowledge'" class="space-y-6">
                <!-- Context -->
                <div>
                    <InputLabel for="context" value="Contexto do Assistente" />
                    <textarea
                        id="context"
                        v-model="form.chatbot_config.context"
                        rows="6"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                        placeholder="Descreva aqui o que é a ouvidoria, quais são os procedimentos padrão, FAQs, ou qualquer informação que ajude o bot a responder melhor."
                    />
                    <InputError :message="form.errors['chatbot_config.context']" class="mt-2" />
                    <p class="mt-1 text-sm text-gray-500">Essas informações serão usadas como "cérebro" do assistente.</p>
                </div>

                <!-- URLs -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <InputLabel value="URLs de Referência" />
                        <button
                            type="button"
                            @click="addUrl"
                            class="text-sm text-indigo-600 hover:text-indigo-700 font-medium"
                        >
                            + Adicionar URL
                        </button>
                    </div>
                    <div v-if="form.chatbot_config.urls.length > 0" class="space-y-2">
                        <div v-for="(url, index) in form.chatbot_config.urls" :key="index" class="flex gap-2">
                            <TextInput
                                v-model="form.chatbot_config.urls[index]"
                                type="url"
                                placeholder="https://exemplo.com/documento"
                                class="flex-1"
                            />
                            <button
                                type="button"
                                @click="removeUrl(index)"
                                class="px-3 py-2 text-red-600 hover:text-red-700"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">O sistema tentará extrair o conteúdo dessas URLs para alimentar o contexto.</p>
                </div>

                <!-- Files -->
                <div>
                    <InputLabel for="files" value="Arquivos de Referência" />
                    <input
                        id="files"
                        type="file"
                        multiple
                        accept=".txt,.pdf,.doc,.docx"
                        @change="handleFileUpload"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                    />
                    <p class="mt-1 text-sm text-gray-500">TXT, PDF, DOC, DOCX (máx. 10MB cada)</p>

                    <div v-if="form.chatbot_config.files.length > 0" class="mt-3 space-y-2">
                        <div v-for="(file, index) in form.chatbot_config.files" :key="index" class="flex items-center justify-between p-2 bg-gray-50 rounded">
                            <span class="text-sm text-gray-700">{{ file.name }}</span>
                            <button
                                type="button"
                                @click="removeFile(index)"
                                class="text-red-600 hover:text-red-700 text-sm"
                            >
                                Remover
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="close">
                Cancelar
            </SecondaryButton>

            <DangerButton
                class="ms-3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                @click="deleteChannel"
            >
                Excluir Canal
            </DangerButton>

            <PrimaryButton
                class="ms-3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                @click="updateChannel"
            >
                Salvar
            </PrimaryButton>
        </template>
    </DialogModal>
</template>
