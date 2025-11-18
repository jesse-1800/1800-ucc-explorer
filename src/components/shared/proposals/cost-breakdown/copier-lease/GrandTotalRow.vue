<template>
  <!--Total One-time-->
  <tr>
    <td colspan="8">Upfront Total (One-Time)</td>
    <td class="text-right pt-1 pb-1">
      <h3 class="text-info">${{Number(OfferedOneTimeCost(proposal)).toFixed(2)}}</h3>
      <small class="font-weight-light"><i>One-time</i></small>
    </td>
  </tr>

  <!--Total Monthly-->
  <tr>
    <td colspan="8">Monthly Total (Recurring)</td>
    <td class="text-right pt-1 pb-1">
      <h3 class="text-info">${{Number(OfferedMonthlyCost(proposal)).toFixed(2)}}</h3>
      <small class="font-weight-light"><i>Per month</i></small>
    </td>
  </tr>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals.js";
import {OfferedMonthlyCost} from "@/composables/ProductComposable.js";
import {OfferedOneTimeCost} from "@/composables/ProductComposable.js";

const {proposal} = storeToRefs(GlobalStore());
</script>
