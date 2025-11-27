<template>
  <MyModal color="transparent" :fullscreen="fullscreen" v-model="modals.ucc_filing_viewer" title="UCC File Viewer">
    <template #append-toolbar-icon>
      <v-btn icon="mdi-open-in-new" :href="`/ucc-filing/${ucc_filing_id}`"></v-btn>
      <v-btn icon="mdi-fullscreen" @click="fullscreen=!fullscreen"></v-btn>
    </template>
    <UccFilingsViewer :ucc_filing_id="ucc_filing_id"/>
  </MyModal>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const fullscreen = ref(true);
const {modals} = storeToRefs(store);
const props = defineProps(['ucc_filing_id']);
</script>
