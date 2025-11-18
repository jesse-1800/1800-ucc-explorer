<template>
  <tr>
    <td class="pa-1">
      <TableItemName @onclick="RemoveProduct(index)" icon="mdi-close">
        {{FindProduct(cart_item.product.id).name}}
      </TableItemName>
    </td>
    <td>{{FindProductSKU(cart_item.product.id)}}</td>
    <td>Product</td>
    <td class="pa-1" style="width:150px">
      <TableInput type="number" v-model.number="cart_item.product.qty"/>
    </td>
    <td class="pa-1" style="width:150px">
      <TableInput type="number" v-model.number="cart_item.product.price" icon="mdi-currency-usd"/>
    </td>
    <td class="pa-1" style="width:100px">
      <TableInput type="number" v-model.number="cart_item.product.price_margin"/>
    </td>
    <td class="pa-1" style="width:150px">
      <TableInput
        type="number"
        :readonly="true"
        icon="mdi-currency-usd"
        :model-value="GetMarginPrice(
          cart_item.product.price * cart_item.product.qty,
          cart_item.product.price_margin
        )">
      </TableInput>
    </td>
    <td class="pa-0" :rowspan="RowSpan(cart_item)" :style="td_border_style">
      <TableSelect
        variant="text"
        :disabled="true"
        :items="custom_charge_types"
        v-model="cart_item.product.charge_type">
      </TableSelect>
    </td>
    <td :rowspan="RowSpan(cart_item)" class="text-right" v-tooltip="`Includes product and accessories only`">
      <TablePrice v-if="cart_item.product.charge_type=='monthly'">{{SingleEquipmentMonthlyCost(proposal,cart_item)}}</TablePrice>
      <TablePrice v-else>{{SingleEquipmentOneTimeCost(cart_item)}}</TablePrice>
    </td>
  </tr>
</template>
<script setup lang="ts">
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals.ts";
import {FindProduct, FindProductSKU} from "@/composables/ProductComposable.ts";
import {charge_types} from "@/composables/ProductComposable.ts";
import {RemoveProduct} from "@/composables/ProductComposable.ts";
import {GetMarginPrice} from "@/composables/ProductComposable.ts";
import {GetAttachedDatasets} from "@/composables/ProductComposable.ts";
import {SingleEquipmentMonthlyCost} from "@/composables/ProductComposable.ts";
import {SingleEquipmentOneTimeCost} from "@/composables/ProductComposable.ts";
import {is_leasing,td_border_style} from "@/composables/ProposalComposable.ts";

const {proposal} = storeToRefs(GlobalStore());
const {cart_item,index} = defineProps(['cart_item','index']);
const RowSpan = (cart_item: any) => {
  return GetAttachedDatasets(cart_item).length + 1
}
const custom_charge_types = computed(() => {
  return charge_types.value.filter(type => {
    if (type.value === 'fixed-monthly') return false
    if (type.value === 'one-time' && is_leasing.value) return false
    return true
  })
})
</script>
