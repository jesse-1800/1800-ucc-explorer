<template>
  <div v-if="!proposal.cart_items.length && !proposal.it_service_items.length" class="mt-4">
    <div class="text-center">
      <h1 class="font-weight-light">You haven't added any Products yet.</h1>
    </div>
  </div>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const {proposal} = storeToRefs(store);
</script>
