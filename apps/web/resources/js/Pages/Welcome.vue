<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Pulsar - Ouvidoria Inteligente" />

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Header -->
        <header class="fixed top-0 w-full bg-white/90 backdrop-blur-sm shadow-sm z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <img
                            src="../../images/pulsar-logo-horizontal.png"
                            alt="Pulsar Logo"
                            class="h-12 w-auto"
                        />
                    </div>

                    <!-- Navigation -->
                    <nav v-if="canLogin" class="flex items-center gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-6 py-2.5 bg-[#0e5f5a] text-white rounded-lg font-medium hover:bg-[#0d5450] transition-all duration-200 shadow-md hover:shadow-lg"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-6 py-2.5 text-gray-700 font-medium hover:text-[#0e5f5a] transition-colors duration-200"
                            >
                                Login
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-6 py-2.5 bg-[#0e5f5a] text-white rounded-lg font-medium hover:bg-[#0d5450] transition-all duration-200 shadow-md hover:shadow-lg"
                            >
                                Cadastrar
                            </Link>
                        </template>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-[#0e5f5a]/10 rounded-full mb-8">
                        <div class="w-2 h-2 bg-[#0e5f5a] rounded-full animate-pulse"></div>
                        <span class="text-[#0e5f5a] font-medium text-sm">Ouvidoria Inteligente com IA</span>
                    </div>

                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold text-gray-900 mb-6 leading-tight">
                        Transforme Escuta em
                        <span class="text-[#0e5f5a] block">Inteligência Acionável</span>
                    </h1>

                    <p class="text-xl text-gray-600 mb-10 max-w-3xl mx-auto leading-relaxed">
                        Conecte-se diretamente com sua comunidade através do WhatsApp.
                        O Pulsar coleta, organiza e analisa feedbacks automaticamente,
                        gerando insights valiosos para gestores.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link
                            v-if="canRegister && !$page.props.auth.user"
                            :href="route('register')"
                            class="px-8 py-4 bg-[#0e5f5a] text-white rounded-lg font-semibold text-lg hover:bg-[#0d5450] transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5"
                        >
                            Começar Agora
                        </Link>
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-8 py-4 bg-[#0e5f5a] text-white rounded-lg font-semibold text-lg hover:bg-[#0d5450] transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5"
                        >
                            Ir para Dashboard
                        </Link>
                        <a
                            href="#sobre"
                            class="px-8 py-4 bg-white text-gray-700 rounded-lg font-semibold text-lg hover:bg-gray-50 transition-all duration-200 shadow-lg hover:shadow-xl border-2 border-gray-200"
                        >
                            Saiba Mais
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-white">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Como Funciona
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Uma plataforma completa para transformar feedbacks em ação
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="p-8 rounded-2xl bg-gradient-to-br from-[#0e5f5a]/5 to-[#0e5f5a]/10 hover:shadow-xl transition-all duration-300 border border-[#0e5f5a]/10">
                        <div class="w-14 h-14 bg-[#0e5f5a] rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">
                            Chatbot Conversacional
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            Integração direta com WhatsApp para coletar reclamações,
                            feedbacks e alertas de forma natural e acessível.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-8 rounded-2xl bg-gradient-to-br from-[#0e5f5a]/5 to-[#0e5f5a]/10 hover:shadow-xl transition-all duration-300 border border-[#0e5f5a]/10">
                        <div class="w-14 h-14 bg-[#0e5f5a] rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">
                            Análise com IA
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            Inteligência artificial organiza e analisa dados automaticamente,
                            gerando métricas e insights em tempo real.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-8 rounded-2xl bg-gradient-to-br from-[#0e5f5a]/5 to-[#0e5f5a]/10 hover:shadow-xl transition-all duration-300 border border-[#0e5f5a]/10">
                        <div class="w-14 h-14 bg-[#0e5f5a] rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">
                            Insights Acionáveis
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            Sugestões de melhoria contínuas para que gestores possam
                            tomar decisões baseadas em dados reais.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="sobre" class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-[#0e5f5a]/5 to-white">
            <div class="max-w-7xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-4xl font-bold text-gray-900 mb-6">
                            Sinta o Pulso da Sua Comunidade
                        </h2>
                        <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                            O Pulsar é mais do que uma simples ouvidoria. É uma plataforma
                            inteligente que conecta você diretamente com quem importa:
                            cidadãos, clientes ou colaboradores.
                        </p>
                        <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                            Através da análise contínua de feedbacks, transformamos escuta
                            em inteligência acionável, ajudando gestores a compreender o que
                            realmente "pulsa" em sua comunidade ou organização.
                        </p>

                        <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-[#0e5f5a]">
                            <p class="text-gray-700 mb-2">
                                <span class="font-semibold text-[#0e5f5a]">Projeto Acadêmico</span>
                            </p>
                            <p class="text-gray-600">
                                Desenvolvido como parte da disciplina IF1006 - Transformação
                                Digital com IA, da Universidade Federal de Pernambuco (UFPE).
                            </p>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="bg-white p-6 rounded-2xl shadow-lg">
                                <div class="text-4xl font-bold text-[#0e5f5a] mb-2">100%</div>
                                <p class="text-gray-600 font-medium">Automatizado</p>
                            </div>
                            <div class="bg-white p-6 rounded-2xl shadow-lg mt-8">
                                <div class="text-4xl font-bold text-[#0e5f5a] mb-2">24/7</div>
                                <p class="text-gray-600 font-medium">Disponível</p>
                            </div>
                            <div class="bg-white p-6 rounded-2xl shadow-lg">
                                <div class="text-4xl font-bold text-[#0e5f5a] mb-2">IA</div>
                                <p class="text-gray-600 font-medium">Powered</p>
                            </div>
                            <div class="bg-white p-6 rounded-2xl shadow-lg mt-8">
                                <div class="text-4xl font-bold text-[#0e5f5a] mb-2">Real</div>
                                <p class="text-gray-600 font-medium">Time</p>
                            </div>
                        </div>

                        <!-- Decorative element -->
                        <div class="absolute -z-10 -top-8 -right-8 w-72 h-72 bg-[#0e5f5a]/10 rounded-full blur-3xl"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-[#0e5f5a] to-[#0a4a46]">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-4xl font-bold text-white mb-6">
                    Pronto para Transformar sua Ouvidoria?
                </h2>
                <p class="text-xl text-white/90 mb-10">
                    Comece a coletar insights valiosos da sua comunidade hoje mesmo.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        v-if="canRegister && !$page.props.auth.user"
                        :href="route('register')"
                        class="px-8 py-4 bg-white text-[#0e5f5a] rounded-lg font-semibold text-lg hover:bg-gray-50 transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5"
                    >
                        Criar Conta Grátis
                    </Link>
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="px-8 py-4 bg-white text-[#0e5f5a] rounded-lg font-semibold text-lg hover:bg-gray-50 transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5"
                    >
                        Acessar Plataforma
                    </Link>
                    <Link
                        v-if="canLogin && !$page.props.auth.user"
                        :href="route('login')"
                        class="px-8 py-4 bg-transparent text-white rounded-lg font-semibold text-lg hover:bg-white/10 transition-all duration-200 border-2 border-white"
                    >
                        Já Tenho Conta
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid md:grid-cols-3 gap-8 mb-8">
                    <div>
                        <img
                            src="../../images/pulsar-logo-horizontal.png"
                            alt="Pulsar Logo"
                            class="h-10 w-auto mb-4 brightness-0 invert"
                        />
                        <p class="text-gray-400 text-sm">
                            Plataforma de Ouvidoria Inteligente com IA
                        </p>
                    </div>

                    <div>
                        <h4 class="font-semibold mb-4 text-lg">Projeto</h4>
                        <ul class="space-y-2 text-gray-400 text-sm">
                            <li>IF1006 - Transformação Digital com IA</li>
                            <li>Universidade Federal de Pernambuco</li>
                            <li>Centro de Informática (CIn)</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-semibold mb-4 text-lg">Links</h4>
                        <ul class="space-y-2">
                            <li v-if="canLogin && !$page.props.auth.user">
                                <Link :href="route('login')" class="text-gray-400 hover:text-white transition-colors text-sm">
                                    Login
                                </Link>
                            </li>
                            <li v-if="canRegister && !$page.props.auth.user">
                                <Link :href="route('register')" class="text-gray-400 hover:text-white transition-colors text-sm">
                                    Cadastrar
                                </Link>
                            </li>
                            <li v-if="$page.props.auth.user">
                                <Link :href="route('dashboard')" class="text-gray-400 hover:text-white transition-colors text-sm">
                                    Dashboard
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm mb-4 md:mb-0">
                        © 2054 Pulsar. Todos os direitos reservados.
                    </p>
                    <p class="text-gray-500 text-xs">
                        Laravel v{{ laravelVersion }} • PHP v{{ phpVersion }}
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
