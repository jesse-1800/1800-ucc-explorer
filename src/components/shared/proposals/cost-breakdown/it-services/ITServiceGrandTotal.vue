<template>
  <!--Total One-time-->
  <tr>
    <td colspan="7">Upfront Total (One-Time)</td>
    <td class="text-right pt-1 pb-1">
      <h3 class="text-info">${{ITServiceLineItemOneTimeGrandTotal()}}</h3>
      <small class="font-weight-light"><i>One-time</i></small>
    </td>
  </tr>

  <!--Total Monthly-->
  <tr>
    <td colspan="7">Grand Total (Recurring)</td>
    <td class="text-right pt-1 pb-1">
      <h3 class="text-info">${{ITServiceLineItemRecurringGrandTotal()}}</h3>
      <small class="font-weight-light"><i>Per month</i></small>
    </td>
  </tr>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals.js";
import {
  ITServiceLineItemOneTimeGrandTotal,
  ITServiceLineItemRecurringGrandTotal
} from "@/composables/ProposalComposable.js";

const {proposal} = storeToRefs(GlobalStore());
</script>
