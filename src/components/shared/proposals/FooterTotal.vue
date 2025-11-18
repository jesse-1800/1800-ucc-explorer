<template>
  <div v-if="OfferedMonthlyCost(proposal)>0">
    <small class="d-none d-md-block">Monthly Payment</small>
    <div style="font-size:16px">
      ${{OfferedMonthlyCost(proposal).toFixed(2)}} / month
    </div>
  </div>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {OfferedMonthlyCost} from "@/composables/ProductComposable";

const store = GlobalStore();
const {proposal} = storeToRefs(store);
</script>
