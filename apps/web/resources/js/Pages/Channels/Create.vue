<script setup>
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    teams: {
        type: Array,
        default: () => []
    }
});

const page = usePage();

const form = useForm({
    team_id: page.props.auth?.user?.current_team_id || '',
    name: '',
    type: 'whatsapp', // Default to WhatsApp
    telegram_bot_token: '',
    official_whatsapp_number: '',
    app_id: '',
    app_secret: '',
    access_token: '',
    phone_number_id: '',
    other_api_params: '',
    chatbot_config: { context: '' },
});

const createChannel = () => {
    console.log('Tentando criar canal...', form.data());

    form.post(route('channels.store'), {
        errorBag: 'createChannel',
        preserveScroll: true,
        onSuccess: (response) => {
            console.log('Canal criado com sucesso!', response);
            form.reset();
        },
        onError: (errors) => {
            console.error('Erros de validação:', errors);
        },
        onFinish: () => {
            console.log('Requisição finalizada');
        }
    });
};
</script>

<template>
    <AppLayout title="Criar Canal">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Criar Novo Canal
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Error Messages -->
                <div v-if="form.hasErrors" class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
                    <p class="font-semibold mb-2">Por favor, corrija os seguintes erros:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>

                <FormSection @submitted="createChannel">
                    <template #title>
                        Informações do Canal
                    </template>

                    <template #description>
                        Configure um novo canal de WhatsApp para sua equipe. Forneça todas as informações necessárias para integração com a API do WhatsApp.
                    </template>

                    <template #form>
                        <!-- Team -->
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="team_id" value="Equipe" />
                            <select
                                id="team_id"
                                v-model="form.team_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">Selecione uma equipe</option>
                                <option v-for="team in teams" :key="team.id" :value="team.id">
                                    {{ team.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.team_id" class="mt-2" />
                        </div>

                        <!-- Channel Type -->
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="type" value="Tipo de Canal" />
                            <select
                                id="type"
                                v-model="form.type"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="whatsapp">WhatsApp Cloud API</option>
                                <option value="telegram">Telegram Bot</option>
                            </select>
                            <InputError :message="form.errors.type" class="mt-2" />
                        </div>

                        <!-- Name -->
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="name" value="Nome do Canal" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                autocomplete="name"
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <!-- Telegram Fields -->
                        <div v-if="form.type === 'telegram'" class="col-span-6 sm:col-span-4">
                            <InputLabel for="telegram_bot_token" value="Token do Bot (Telegram)" />
                            <TextInput
                                id="telegram_bot_token"
                                v-model="form.telegram_bot_token"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz"
                            />
                            <InputError :message="form.errors.telegram_bot_token" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">
                                Crie seu bot com o <a href="https://t.me/BotFather" target="_blank" class="text-indigo-600 hover:text-indigo-900">@BotFather</a> e cole o token aqui.
                            </p>
                        </div>

                        <!-- WhatsApp Fields -->
                        <template v-if="form.type === 'whatsapp'">
                            <!-- Official WhatsApp Number -->
                            <div class="col-span-6 sm:col-span-4">
                                <InputLabel for="official_whatsapp_number" value="Número WhatsApp Oficial" />
                                <TextInput
                                    id="official_whatsapp_number"
                                    v-model="form.official_whatsapp_number"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="+55 11 99999-9999"
                                />
                                <InputError :message="form.errors.official_whatsapp_number" class="mt-2" />
                            </div>

                            <!-- App ID -->
                            <div class="col-span-6 sm:col-span-4">
                                <InputLabel for="app_id" value="App ID" />
                                <TextInput
                                    id="app_id"
                                    v-model="form.app_id"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.app_id" class="mt-2" />
                            </div>

                            <!-- App Secret -->
                            <div class="col-span-6 sm:col-span-4">
                                <InputLabel for="app_secret" value="App Secret" />
                                <TextInput
                                    id="app_secret"
                                    v-model="form.app_secret"
                                    type="password"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.app_secret" class="mt-2" />
                            </div>

                            <!-- Access Token -->
                            <div class="col-span-6 sm:col-span-4">
                                <InputLabel for="access_token" value="Token de Acesso" />
                                <textarea
                                    id="access_token"
                                    v-model="form.access_token"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                />
                                <InputError :message="form.errors.access_token" class="mt-2" />
                            </div>

                            <!-- Phone Number ID -->
                            <div class="col-span-6 sm:col-span-4">
                                <InputLabel for="phone_number_id" value="Phone Number ID" />
                                <TextInput
                                    id="phone_number_id"
                                    v-model="form.phone_number_id"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.phone_number_id" class="mt-2" />
                            </div>
                        </template>

                        <!-- Other API Params (Optional) -->
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="other_api_params" value="Outros Parâmetros da API (JSON - Opcional)" />
                            <textarea
                                id="other_api_params"
                                v-model="form.other_api_params"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm font-mono text-sm"
                                placeholder='{"param1": "value1", "param2": "value2"}'
                            />
                            <InputError :message="form.errors.other_api_params" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">Formato JSON válido para parâmetros adicionais da API</p>
                        </div>

                        <!-- Contexto do Assistente -->
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="context" value="Contexto do Assistente (Base de Conhecimento)" />
                            <textarea
                                id="context"
                                v-model="form.chatbot_config.context"
                                rows="6"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                placeholder="Descreva aqui o que é a ouvidoria, quais são os procedimentos padrão, FAQs, ou qualquer informação que ajude o bot a responder melhor."
                            />
                            <InputError :message="form.errors['chatbot_config.context']" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">Essas informações serão usadas como "cérebro" do assistente para responder às dúvidas.</p>
                        </div>
                    </template>

                    <template #actions>
                        <SecondaryButton
                            type="button"
                            @click="$inertia.visit(route('dashboard'))"
                        >
                            Cancelar
                        </SecondaryButton>

                        <PrimaryButton
                            class="ms-3"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing">Criando...</span>
                            <span v-else>Criar Canal</span>
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>

