<template>
  <!--The ITEMS in this TIER package-->
  <tr>
    <td class="pa-1">
      <TableItemName @click="$emit('remove')" v-if="is_standalone" icon="mdi-close">
        {{ line_item.name }}
      </TableItemName>
      <TableItemName v-else icon="mdi-circle-small">
        {{ line_item.name }}
      </TableItemName>
    </td>
    <td>
      <template v-if="is_standalone">IT Service Item (Standalone)</template>
      <template v-else>IT Service Item</template>
    </td>
    <td class="pa-1" style="width:100px">
      <TableInput type="number" v-model.number="line_item.quantity"/>
    </td>
    <td class="pa-1" style="width:100px">
      <TableInput type="number" v-model.number="line_item.unit_price"/>
    </td>
    <td class="pa-1" style="width:100px">
      <TableInput type="number" v-model.number="line_item.price_margin"/>
    </td>
    <td class="pa-1">
      <TableInput
        :readonly="true"
        icon="mdi-currency-usd"
        :model-value="GetMarginPrice(
          line_item.unit_price * line_item.quantity,
          line_item.price_margin
        )">
      </TableInput>
    </td>
    <td class="pa-0" :style="td_border_style">
      <TableSelect
        :disabled="!is_standalone"
        variant="text" :items="it_svc_terms"
        v-model="line_item.charge_type">
      </TableSelect>
    </td>

    <!--ROW OCCUPIED BY TIER ROWSPAN -->
    <!--This TD only shows if item is STANDALONE -->
    <td v-if="is_standalone" style="max-width:120px" class="text-right">
      <TablePrice>
        {{ITServiceLineItemSubtotal(cart_item).toFixed(2)}}
      </TablePrice>
    </td>
  </tr>
</template>
<script setup lang="ts">
import {it_svc_terms, ITServiceLineItemSubtotal} from "@/composables/ProposalComposable.ts";
import {GetMarginPrice} from "@/composables/ProductComposable.ts";
import {td_border_style} from "@/composables/ProposalComposable.ts";

defineProps(['cart_item','line_item','is_standalone']);
</script>
