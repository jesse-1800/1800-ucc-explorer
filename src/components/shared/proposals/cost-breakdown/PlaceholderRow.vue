<template>
  <template v-if="!proposal.cart_items.length">
    <tr>
      <td :colspan="it_service_category ? 8 : 9" class="text-center py-5">
        <h1 class="font-weight-light">You haven't added any Products yet.</h1>
      </td>
    </tr>
  </template>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {it_service_category} from "@/composables/GlobalComposables.js";
const {proposal} = storeToRefs(GlobalStore());
</script>
