<template>
  <template v-if="proposal.is_global_print_cost!=1">
    <!------Black Prints-->
    <tr>
      <td class="text-grey-darken-1">
        <v-icon>mdi-circle-small</v-icon> Prints Included (Black)
      </td>
      <td>-</td>
      <td>Print Cost</td>
      <td class="pa-1">
        <TableInput
          type="number"
          v-model.number="cart_item.print_cost.black_prints_included">
        </TableInput>
      </td>
      <td class="pa-1">
        <TableInput
          type="number"
          icon="mdi-currency-usd"
          v-model.number="cart_item.print_cost.black_prints_cost">
        </TableInput>
      </td>
      <td class="pa-1">
        <TableInput
          type="number"
          v-model.number="cart_item.print_cost.black_prints_margin">
        </TableInput>
      </td>
      <td class="pa-1">
        <TableInput
          type="number"
          :readonly="true"
          icon="mdi-currency-usd"
          :model-value="CopierBlackPrintTotalCost(proposal,cart_item)">
        </TableInput>
      </td>
      <td class="pa-0" :style="td_border_style" rowspan="2">
        <TableSelect
          variant="text"
          v-model="cart_item.print_cost.charge_type"
          :items="custom_charge_types">
        </TableSelect>
      </td>
      <td class="text-right">
        <TablePrice>
          {{CopierBlackPrintTotalCost(proposal,cart_item)}}
        </TablePrice>
      </td>
    </tr>
    <!------Color Prints-->
    <tr>
      <td class="text-grey-darken-1">
        <v-icon>mdi-circle-small</v-icon>
        Prints Included (Color)
      </td>
      <td>-</td>
      <td>Print Cost</td>
      <td class="pa-1">
        <TableInput
          type="number"
          v-model.number="cart_item.print_cost.color_prints_included">
        </TableInput>
      </td>
      <td class="pa-1">
        <TableInput
          type="number"
          icon="mdi-currency-usd"
          v-model.number="cart_item.print_cost.color_prints_cost">
        </TableInput>
      </td>
      <td class="pa-1">
        <TableInput
          type="number"
          v-model.number="cart_item.print_cost.color_prints_margin">
        </TableInput>
      </td>
      <td class="pa-1">
        <TableInput
          type="number"
          :readonly="true"
          icon="mdi-currency-usd"
          :model-value="CopierColorPrintTotalCost(proposal,cart_item)">
        </TableInput>
      </td>
    <!--Occluded by rowspan above
      <td class="pa-1">
        <TableSelect
          variant="text"
          :disabled="cart_item.product.charge_type=='monthly'"
          v-model="cart_item.print_cost.charge_type"
          :items="charge_types">
        </TableSelect>
      </td>-->
      <td class="text-right">
        <TablePrice>
          {{CopierColorPrintTotalCost(proposal,cart_item)}}
        </TablePrice>
      </td>
    </tr>
  </template>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals.ts";
import {charge_types} from "@/composables/ProductComposable.ts";
import {CopierColorPrintTotalCost} from "@/composables/ProductComposable.ts";
import {CopierBlackPrintTotalCost} from "@/composables/ProductComposable.ts";
import {is_leasing,is_purchase,td_border_style} from "@/composables/ProposalComposable.ts";

const {cart_item} = defineProps(['cart_item']);
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

// When one item's charge type is changed, change all items to match
watch(()=>cart_item.print_cost.charge_type, (new_charge_type) => {
  proposal.value.cart_items.forEach(cart_item => {
    cart_item.print_cost.charge_type = new_charge_type;
  });
});
</script>
