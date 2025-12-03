<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update:modelValue']);

function updateStart(event) {
    emit('update:modelValue', { ...props.modelValue, start: event.target.value });
}

function updateEnd(event) {
    emit('update:modelValue', { ...props.modelValue, end: event.target.value });
}
</script>

<template>
    <div class="flex items-center space-x-2">
        <div class="flex flex-col">
            <label class="text-xs text-gray-500 mb-1">Início</label>
            <input 
                type="date" 
                :value="modelValue.start" 
                @input="updateStart"
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
            >
        </div>
        <span class="text-gray-400 mt-5">→</span>
        <div class="flex flex-col">
            <label class="text-xs text-gray-500 mb-1">Fim</label>
            <input 
                type="date" 
                :value="modelValue.end" 
                @input="updateEnd"
                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
            >
        </div>
    </div>
</template>
