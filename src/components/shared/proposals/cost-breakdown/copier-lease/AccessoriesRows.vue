<template>
  <tr v-for="accessory in GetAttachedDatasets(cart_item)">
    <td class="text-grey-darken-1">
      <span class="clip-text-1-lines" :title="GetAccessoryObject(accessory.accessory_id).name">
        <v-icon>mdi-circle-small</v-icon> {{GetAccessoryObject(accessory.accessory_id).name}}
      </span>
    </td>
    <td>{{ FindAccessorySKU(accessory.accessory_id) }}</td>
    <td>Accessory</td>
    <td class="pa-1">
      <TableInput type="number" v-model.number="accessory.qty"/>
    </td>
    <td class="pa-1">
      <TableInput
        type="number"
        icon="mdi-currency-usd"
        v-model.number="accessory.accessory_price">
      </TableInput>
    </td>
    <td class="pa-1">
      <TableInput type="number" v-model.number="accessory.price_margin"/>
    </td>
    <td class="pa-1">
      <TableInput
        type="number"
        :readonly="true"
        icon="mdi-currency-usd"
        :model-value="GetMarginPrice(
          accessory.accessory_price *
          accessory.qty,accessory.price_margin
        )">
      </TableInput>
    </td>
    <!--columns is occupied by product's rowspan-->
  </tr>
</template>

<script setup>
import {FindAccessorySKU, GetMarginPrice} from "@/composables/ProductComposable.js";
import {GetAccessoryObject} from "@/composables/ProductComposable.js";
import {GetAttachedDatasets} from "@/composables/ProductComposable.js";

const {cart_item} = defineProps(['cart_item'])
</script>
