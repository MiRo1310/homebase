<script setup lang="ts">
import {X} from '@lucide/vue'
import {ModalState} from "../../composables/modalState";

defineProps<{ title: string, goBack?: boolean, modalState: ModalState }>();
const emits = defineEmits(["goBack"]);
</script>

<template>
    <div class="flex items-center gap-2">
        <span v-if="goBack" @click="emits('goBack')"></span>
        <p class="truncate flex-1">{{ title }}</p>
        <div>
            <X class="text-gray-600 hover:text-black cursor-pointer" @click="modalState.close()"/>
        </div>

    </div>
</template>
