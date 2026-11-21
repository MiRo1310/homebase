<script setup lang="ts">
import {ModalState} from "../../composables/modalState";

const props = defineProps<{ modalRef: HTMLElement | null, modalState: ModalState }>();


const clickHandler = (e: MouseEvent) => {
    const modalRef = props.modalRef
    if (!modalRef) return

    const modalDomRect = modalRef.getBoundingClientRect()
    const clickIsInModal = e.clientX > modalDomRect.left && e.clientX < modalDomRect.right && e.clientY > modalDomRect.top && e.clientY < modalDomRect.bottom

    if (clickIsInModal) return;
    props.modalState.close();
}
</script>

<template>
    <div v-if="modalRef" class="bg-black opacity-50 fixed top-0 left-0 w-full h-full" @click="clickHandler"/>
</template>

