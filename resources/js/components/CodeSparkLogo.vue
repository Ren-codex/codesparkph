<script setup lang="ts">
withDefaults(defineProps<{ class?: string }>(), { class: 'h-9 w-9' });
</script>

<template>
    <svg
        :class="$props.class"
        viewBox="0 0 64 64"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        role="img"
        aria-label="CodeSpark PH"
    >
        <defs>
            <linearGradient
                id="codespark-spark"
                x1="18"
                y1="6"
                x2="48"
                y2="58"
                gradientUnits="userSpaceOnUse"
            >
                <stop stop-color="#38e8ff" />
                <stop offset="0.5" stop-color="#7c6bff" />
                <stop offset="1" stop-color="#e454ff" />
            </linearGradient>
        </defs>

        <path
            d="M24 7c-6 0-6 6-6 12 0 8-5 11-8 13 3 2 8 5 8 13 0 6 0 12 6 12"
            stroke="url(#codespark-spark)"
            stroke-width="3.5"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <path
            d="M40 7c6 0 6 6 6 12 0 8 5 11 8 13-3 2-8 5-8 13 0 6 0 12-6 12"
            stroke="url(#codespark-spark)"
            stroke-width="3.5"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <path
            d="M37 9 21 35h9l-3 20 16-26h-9l3-20Z"
            fill="url(#codespark-spark)"
        />
    </svg>
</template>
