<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const isLoading = ref(false);
const progress = ref(0);

onMounted(() => {
    router.on('start', () => {
        isLoading.value = true;
        progress.value = 0;
        animateProgress();
    });

    router.on('progress', (event) => {
        if (event.detail.progress.percentage) {
            progress.value = event.detail.progress.percentage;
        }
    });

    router.on('finish', () => {
        progress.value = 100;
        setTimeout(() => {
            isLoading.value = false;
            progress.value = 0;
        }, 200);
    });

    router.on('exception', () => {
        isLoading.value = false;
        progress.value = 0;
    });
});

const animateProgress = () => {
    const interval = setInterval(() => {
        if (progress.value < 90 && isLoading.value) {
            progress.value += Math.random() * 10;
        } else {
            clearInterval(interval);
        }
    }, 200);
};
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isLoading"
            class="fixed top-0 left-0 right-0 z-[100]"
        >
            <div
                class="h-0.5 bg-indigo-300 transition-all duration-300 ease-out shadow-lg"
                :style="{ width: `${progress}%` }"
            >
            </div>
        </div>
    </Transition>
</template>

