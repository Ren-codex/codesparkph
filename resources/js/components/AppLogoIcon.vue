<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 64 64"
        fill="none"
        :class="className"
        v-bind="$attrs"
    >
        <path
            d="M24 7c-6 0-6 6-6 12 0 8-5 11-8 13 3 2 8 5 8 13 0 6 0 12 6 12"
            stroke="currentColor"
            stroke-width="4"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <path
            d="M40 7c6 0 6 6 6 12 0 8 5 11 8 13-3 2-8 5-8 13 0 6 0 12-6 12"
            stroke="currentColor"
            stroke-width="4"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <path d="M37 9 21 35h9l-3 20 16-26h-9l3-20Z" fill="currentColor" />
    </svg>
</template>
