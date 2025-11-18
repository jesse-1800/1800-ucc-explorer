<template>
  <template v-if="proposal.is_global_print_cost==1">
    <!------Black Prints-->
    <tr>
      <td class="pa-1">
        <TableItemName icon="mdi-water-outline">
          Global Prints Included (Black)
        </TableItemName>
      </td>
      <td>-</td>
      <td>Print Cost</td>
      <td class="pa-1">
        <TableInput
          type="number"
          v-model.number="proposal.global_print_cost.black_prints_included">
        </TableInput>
      </td>
      <td class="pa-1">
        <TableInput
          type="number"
          icon="mdi-currency-usd"
          v-model.number="proposal.global_print_cost.black_prints_cost">
        </TableInput>
      </td>
      <td class="pa-1">
        <TableInput
          type="number"
          v-model.number="proposal.global_print_cost.black_prints_margin">
        </TableInput>
      </td>
      <td class="pa-1">
        <TableInput
          type="number"
          :readonly="true"
          icon="mdi-currency-usd"
          :model-value="GlobalBlackPrintTotalCost(proposal)">
        </TableInput>
      </td>
      <td class="pa-0" rowspan="2" :style="td_border_style">
        <TableSelect
          variant="text"
          :items="custom_charge_types"
          v-model="proposal.global_print_cost.charge_type">
        </TableSelect>
      </td>
      <td class="text-right">
        <TablePrice>{{GlobalBlackPrintTotalCost(proposal)}}</TablePrice>
      </td>
    </tr>
    <!------Color Prints-->
    <tr>
      <td class="pa-1">
        <TableItemName icon="mdi-water-outline">
          Global Prints Included (Color)
        </TableItemName>
      </td>
      <td>-</td>
      <td>Print Cost</td>
      <td class="pa-1">
        <TableInput
          type="number"
          v-model.number="proposal.global_print_cost.color_prints_included">
        </TableInput>
      </td>
      <td class="pa-1">
        <TableInput
          type="number"
          icon="mdi-currency-usd"
          v-model.number="proposal.global_print_cost.color_prints_cost">
        </TableInput>
      </td>
      <td class="pa-1">
        <TableInput
          type="number"
          v-model.number="proposal.global_print_cost.color_prints_margin">
        </TableInput>
      </td>
      <td class="pa-1">
        <TableInput
          type="number"
          :readonly="true"
          icon="mdi-currency-usd"
          :model-value="GlobalColorPrintTotalCost(proposal)">
        </TableInput>
      </td>
    <!--Occluded by rowspan above
      <td class="pa-1">
        <TableSelect
          variant="text"
          :items="charge_types"
          :disabled="at_least_one_copier_is_monthly"
          v-model="proposal.global_print_cost.charge_type">
        </TableSelect>
      </td>-->
      <td class="text-right">
        <TablePrice>
          {{GlobalColorPrintTotalCost(proposal)}}
        </TablePrice>
      </td>
    </tr>
  </template>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals.js";
import {charge_types, GlobalColorPrintTotalCost} from "@/composables/ProductComposable.js";
import {GlobalBlackPrintTotalCost} from "@/composables/ProductComposable.js";
import {is_leasing, is_purchase, td_border_style} from "@/composables/ProposalComposable.ts";

const {proposal} = storeToRefs(GlobalStore());
const custom_charge_types = computed(() => {
  if (is_leasing.value) {
    return charge_types.value.filter(type => {
      return type.value !== 'one-time';
    })
  }
  else if (is_purchase.value) {
    return charge_types.value.filter(type => {
      return type.value !== 'monthly';
    })
  }
})
</script>
