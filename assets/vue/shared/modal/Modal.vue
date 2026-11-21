<script setup lang="ts">

import {ref} from "vue";
import {ModalState} from "../../composables/modalState";
import Backdrop from "./Backdrop.vue";

defineProps<{ modalState: ModalState, originHtml: string }>();
const emits = defineEmits(["click"])

const modalRef = ref<HTMLElement | null>(null);
const rootEl = ref<HTMLElement | null>(null);

defineExpose({rootEl})
</script>

<template>
    <div ref="rootEl" v-html="originHtml" @click="emits('click')"/>
    <Backdrop :modalRef :modalState/>
    <Teleport to="body">
        <div v-if="modalState.isOpen"
             class="flex flex-col gap-4 min-w-100 min-h-50 bg-gray-100 border border-gray-800 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 p-4 rounded-md max-h-[90dvh] max-w-150"
             ref="modalRef">

            <slot></slot>
        </div>
    </Teleport>
</template>
