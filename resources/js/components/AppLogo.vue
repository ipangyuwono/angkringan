<script setup lang="ts">
import { Coffee } from 'lucide-vue-next';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-emerald-600 text-white"
    >
        <Coffee class="size-5" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-bold text-emerald-600"
            >Angkringan AK</span
        >
        <span class="text-[10px] text-gray-500 font-medium tracking-tight">Klaten Edition</span>
    </div>
</template>
