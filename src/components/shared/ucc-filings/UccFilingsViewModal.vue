<template>
  <MyModal color="transparent" v-model="modals.ucc_filing_viewer" title="UCC File Viewer">
    <UccFilingsViewer :ucc_file_id="ucc_filing_id"/>
  </MyModal>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const {modals} = storeToRefs(store);
const props = defineProps(['ucc_filing_id']);
</script>
