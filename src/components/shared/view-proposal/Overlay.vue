<template>
  <v-overlay
    persistent
    :z-index="1000"
    v-model="global_loading"
    class="d-flex align-center justify-center">
    <v-progress-circular
      color="primary"
      indeterminate
      size="100"
      width="6">
    </v-progress-circular>
  </v-overlay>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const {global_loading} = storeToRefs(store);
</script>
