<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import {
    Bars3Icon,
    XMarkIcon,
    HomeIcon,
    UsersIcon,
    FolderIcon,
    CalendarIcon,
    DocumentDuplicateIcon,
    ChartPieIcon,
    ChatBubbleBottomCenterTextIcon,
    PlusIcon,
} from '@heroicons/vue/24/outline';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import InertiaLoader from '@/Components/InertiaLoader.vue';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
const sidebarOpen = ref(false);

const page = usePage();

const navigation = computed(() => {
    const items = [
        { name: 'Dashboard', href: route('dashboard'), icon: HomeIcon, current: route().current('dashboard') },
        // { name: 'Chat', href: route('chat'), icon: ChatBubbleBottomCenterTextIcon, current: route().current('chat') },
        // { name: 'Kanban', href: route('kanban'), icon: FolderIcon, current: route().current('kanban') },
        // { name: 'Contacts', href: route('contacts'), icon: UsersIcon, current: route().current('contacts') },
        // { name: 'Flow', href: route('flow'), icon: ChartPieIcon, current: route().current('flow') },
        // { name: 'Chatbots', href: route('chatbots'), icon: DocumentDuplicateIcon, current: route().current('chatbots') },
    ];



    return items;
});




const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const switchToChannel = (channel) => {
    router.put(route('current-channel.update'), {
        channel_id: channel.id,
    }, {
        preserveState: true,
    });
};

const getChannelInitials = (name) => {
    if (!name) return '';
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>
        <Head :title="title" />

        <InertiaLoader />

        <Banner />

        <div class="min-h-screen">
            <!-- Top navigation bar - Fixed -->
            <nav class="fixed top-0 left-0 right-0 z-50 border-b border-gray-100 bg-indigo-400">
                <!-- Primary Navigation Menu -->
                <div class="mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16">
                        <div class="flex items-center">
                            <!-- Mobile menu button -->
                            <button v-if="navigation.length > 0" type="button" class="-m-2.5 p-2.5 text-gray-700 hover:text-gray-900 lg:hidden" @click="sidebarOpen = true">
                                <span class="sr-only">Open sidebar</span>
                                <Bars3Icon class="size-6" aria-hidden="true" />
                            </button>

                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <!-- <ApplicationMark class="block h-9 w-auto" /> -->
                                    <ApplicationMark class="block h-5 w-auto" />
                                </Link>
                            </div>
                        </div>

                        <!-- Navigation Links - Centralized -->
                        <div class="flex-1 flex justify-center">
                            <div class="hidden space-x-8 sm:-my-px sm:flex">
                                <!-- <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Home
                                </NavLink>
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Configurações
                                </NavLink> -->
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6 sm:gap-3">
                            <!-- Channels List -->
                            <div v-if="page.props.channels && page.props.channels.length > 0" class="flex items-center gap-2">
                                <button
                                    v-for="channel in page.props.channels"
                                    :key="channel.id"
                                    @click="switchToChannel(channel)"
                                    :title="channel.name"
                                    :class="[
                                        'inline-flex items-center justify-center size-10 rounded-full text-white text-sm font-semibold transition-all duration-200',
                                        page.props.currentChannel?.id === channel.id
                                            ? 'bg-white/30 ring-2 ring-white/70 shadow-lg scale-110 font-bold'
                                            : 'bg-white/10 hover:bg-white/20 hover:scale-105'
                                    ]"
                                >
                                    {{ getChannelInitials(channel.name) }}
                                </button>
                            </div>

                            <!-- Add Channel Button -->
                            <Link :href="route('channels.create')" title="Adicionar Canal" class="inline-flex items-center justify-center size-10 rounded-full bg-white/10 hover:bg-white/20 text-white transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-indigo-400">
                                <PlusIcon class="size-5" aria-hidden="true" />
                            </Link>

                            <div class="ms-0 relative">
                                <!-- Teams Dropdown -->
                                <Dropdown v-if="page.props.jetstream?.hasTeamFeatures" align="right" width="60" class="z-[60]">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-white/10 hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/50 focus:bg-white/20 active:bg-white/30 transition ease-in-out duration-150">
                                                {{ page.props.auth?.user?.current_team?.name }}

                                                <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="w-60">
                                            <!-- Team Management -->
                                            <div class="block px-4 py-2 text-xs text-gray-400">
                                                Manage Team
                                            </div>

                                            <!-- Team Settings -->
                                            <DropdownLink :href="route('teams.show', page.props.auth?.user?.current_team)">
                                                Team Settings
                                            </DropdownLink>

                                            <DropdownLink v-if="page.props.jetstream?.canCreateTeams" :href="route('teams.create')">
                                                Create New Team
                                            </DropdownLink>

                                            <!-- Team Switcher -->
                                            <template v-if="page.props.auth?.user?.all_teams?.length > 1">
                                                <div class="border-t border-gray-200" />

                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Switch Teams
                                                </div>

                                                <template v-for="team in page.props.auth?.user?.all_teams" :key="team.id">
                                                    <form @submit.prevent="switchToTeam(team)">
                                                        <DropdownLink as="button">
                                                            <div class="flex items-center">
                                                                <svg v-if="team.id == page.props.auth?.user?.current_team_id" class="me-2 size-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>

                                                                <div>{{ team.name }}</div>
                                                            </div>
                                                        </DropdownLink>
                                                    </form>
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48" class="z-[60]">
                                    <template #trigger>
                                        <button v-if="page.props.jetstream?.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                            <img class="size-8 rounded-full object-cover" :src="page.props.auth?.user?.profile_photo_url" :alt="page.props.auth?.user?.name">
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 text-white bg-white/10 hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/50 focus:bg-white/20 active:bg-white/30 transition ease-in-out duration-150">
                                                {{ page.props.auth?.user?.name }}

                                                <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Manage Account
                                        </div>

                                        <DropdownLink :href="route('profile.show')">
                                            Profile
                                        </DropdownLink>

                                        <DropdownLink v-if="page.props.jetstream?.hasApiFeatures" :href="route('api-tokens.index')">
                                            API Tokens
                                        </DropdownLink>

                                        <div class="border-t border-gray-200" />

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Mobile Channels & Hamburger -->
                        <div class="-me-2 flex items-center gap-2 sm:hidden">
                            <!-- Mobile Channels List -->
                            <div v-if="page.props.channels && page.props.channels.length > 0" class="flex items-center gap-1.5">
                                <button
                                    v-for="channel in page.props.channels"
                                    :key="channel.id"
                                    @click="switchToChannel(channel)"
                                    :title="channel.name"
                                    :class="[
                                        'inline-flex items-center justify-center size-8 rounded-full text-white text-xs font-semibold transition-all duration-200',
                                        page.props.currentChannel?.id === channel.id
                                            ? 'bg-white/30 ring-2 ring-white/70 shadow-lg scale-110 font-bold'
                                            : 'bg-white/10'
                                    ]"
                                >
                                    {{ getChannelInitials(channel.name) }}
                                </button>
                            </div>

                            <!-- Mobile Add Channel Button -->
                            <Link :href="route('channels.create')" title="Adicionar Canal" class="inline-flex items-center justify-center size-8 rounded-full bg-white/10 text-white">
                                <PlusIcon class="size-4" aria-hidden="true" />
                            </Link>

                            <!-- Hamburger -->
                            <button class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out" @click="showingNavigationDropdown = ! showingNavigationDropdown">
                                <svg
                                    class="size-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="flex items-center px-4">
                            <div v-if="page.props.jetstream?.managesProfilePhotos" class="shrink-0 me-3">
                                <img class="size-10 rounded-full object-cover" :src="page.props.auth?.user?.profile_photo_url" :alt="page.props.auth?.user?.name">
                            </div>

                            <div>
                                <div class="font-medium text-base text-gray-800">
                                    {{ page.props.auth?.user?.name }}
                                </div>
                                <div class="font-medium text-sm text-gray-500">
                                    {{ page.props.auth?.user?.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                                Profile
                            </ResponsiveNavLink>

                            <ResponsiveNavLink v-if="page.props.jetstream?.hasApiFeatures" :href="route('api-tokens.index')" :active="route().current('api-tokens.index')">
                                API Tokens
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    Log Out
                                </ResponsiveNavLink>
                            </form>

                            <!-- Team Management -->
                            <template v-if="page.props.jetstream?.hasTeamFeatures">
                                <div class="border-t border-gray-200" />

                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    Manage Team
                                </div>

                                <!-- Team Settings -->
                                <ResponsiveNavLink :href="route('teams.show', page.props.auth?.user?.current_team)" :active="route().current('teams.show')">
                                    Team Settings
                                </ResponsiveNavLink>

                                <ResponsiveNavLink v-if="page.props.jetstream?.canCreateTeams" :href="route('teams.create')" :active="route().current('teams.create')">
                                    Create New Team
                                </ResponsiveNavLink>

                                <!-- Team Switcher -->
                                <template v-if="page.props.auth?.user?.all_teams?.length > 1">
                                    <div class="border-t border-gray-200" />

                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        Switch Teams
                                    </div>

                                    <template v-for="team in page.props.auth?.user?.all_teams" :key="team.id">
                                        <form @submit.prevent="switchToTeam(team)">
                                            <ResponsiveNavLink as="button">
                                                <div class="flex items-center">
                                                    <svg v-if="team.id == page.props.auth?.user?.current_team_id" class="me-2 size-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <div>{{ team.name }}</div>
                                                </div>
                                            </ResponsiveNavLink>
                                        </form>
                                    </template>
                                </template>
                            </template>

                        </div>
                    </div>
                </div>
            </nav>

            <!-- Mobile sidebar -->
            <TransitionRoot v-if="navigation.length > 0" as="template" :show="sidebarOpen">
                <Dialog class="relative z-50 lg:hidden" @close="sidebarOpen = false">
                    <TransitionChild as="template" enter="transition-opacity ease-linear duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="transition-opacity ease-linear duration-300" leave-from="opacity-100" leave-to="opacity-0">
                        <div class="fixed inset-0 bg-gray-900/80" />
                    </TransitionChild>

                    <div class="fixed inset-0 flex">
                        <TransitionChild as="template" enter="transition ease-in-out duration-300 transform" enter-from="-translate-x-full" enter-to="translate-x-0" leave="transition ease-in-out duration-300 transform" leave-from="translate-x-0" leave-to="-translate-x-full">
                            <DialogPanel class="relative mr-16 flex w-full max-w-xs flex-1">
                                <TransitionChild as="template" enter="ease-in-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in-out duration-300" leave-from="opacity-100" leave-to="opacity-0">
                                    <div class="absolute top-0 left-full flex w-16 justify-center pt-5">
                                        <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                                            <span class="sr-only">Close sidebar</span>
                                            <XMarkIcon class="size-6 text-white" aria-hidden="true" />
                                        </button>
                                    </div>
                                </TransitionChild>

                                <!-- Mobile Sidebar component -->
                                <div class="relative flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-2">
                                    <div class="relative flex h-16 shrink-0 items-center">
                                        <span class="text-lg font-semibold text-gray-900">Menu</span>
                                    </div>
                                    <nav class="relative flex flex-1 flex-col">
                                        <ul role="list" class="flex flex-1 flex-col gap-y-3">
                                            <li>
                                                <ul role="list" class="grid grid-cols-2 gap-2">
                                                    <li v-for="item in navigation" :key="item.name">
                                                        <Link :href="item.href" :class="[item.current ? 'bg-gray-200 text-indigo-600' : 'text-gray-700 hover:bg-gray-200 hover:text-indigo-600', 'group flex flex-col items-center justify-center rounded-md p-3 h-20 text-xs font-medium transition-colors duration-150']">
                                                            <component :is="item.icon" :class="[item.current ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600', 'size-6 mb-1 shrink-0']" aria-hidden="true" />
                                                            <span class="text-center leading-tight truncate w-full">{{ item.name }}</span>
                                                        </Link>
                                                    </li>
                                                </ul>
                                            </li>

                                        </ul>
                                    </nav>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </Dialog>
            </TransitionRoot>

            <!-- Static sidebar for desktop - Fixed -->
            <div v-if="navigation.length > 0" class="hidden lg:fixed lg:inset-y-0 lg:z-40 lg:flex lg:w-30 lg:flex-col" style="top: 64px;">
                <!-- Sidebar component -->
                <div class="flex grow flex-col gap-y-3 overflow-y-auto border-r border-gray-200 bg-gray-100 px-2 shadow-inner">
                    <nav class="flex flex-1 flex-col mt-5">
                        <ul role="list" class="flex flex-1 flex-col gap-y-2">
                            <li>
                                <ul role="list" class="grid grid-cols-1 gap-2">
                                    <li v-for="item in navigation" :key="item.name">
                                        <Link :href="item.href" :class="[item.current ? 'bg-gray-200 text-indigo-600' : 'text-gray-700 hover:bg-gray-200 hover:text-indigo-600', 'group flex flex-col items-center justify-center rounded-md p-2 w-full h-20 text-xs font-medium transition-colors duration-150']">
                                            <component :is="item.icon" :class="[item.current ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600', 'size-6 mb-1 shrink-0']" aria-hidden="true" />
                                            <span class="text-center leading-tight truncate w-full">{{ item.name }}</span>
                                        </Link>
                                    </li>
                                </ul>
                            </li>

                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Main content area with proper spacing -->
            <div :class="['pt-16', navigation.length > 0 ? 'lg:pl-30' : '']">
                <!-- Page Heading -->
                <header v-if="$slots.header" class="bg-white shadow">
                    <div class="mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <!-- <main class="py-10">
                    <div class="px-4 sm:px-6 lg:px-8">
                        <slot />
                    </div>
                </main> -->
                <main>
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
