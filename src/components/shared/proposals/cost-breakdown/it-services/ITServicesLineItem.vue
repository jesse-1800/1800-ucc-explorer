<template>
  <!--First handle the TIERED Services-->
  <template v-if="cart_item.tier">
    <tr>
      <td class="pa-1">
        <TableItemName @onclick="RemoveTierCartItem(cart_index)" icon="mdi-close">
          {{ cart_item.tier.name }}
        </TableItemName>
      </td>
      <td>IT Service Tier (Package)</td>
      <td style="width:100px">
        --
      </td>
      <td>
        --
      </td>
      <td style="width:100px">
        --
      </td>
      <td>
        --
      </td>
      <td class="pa-0" :style="td_border_style">
        <TableSelect
          v-model="cart_item.tier.charge_type"
          variant="text" :items="it_svc_terms">
        </TableSelect>
      </td>
      <td style="max-width:120px" class="text-right" :style="td_border_style" :rowspan="cart_item.items.length+1" v-tooltip="`Includes product and accessories only`">
        <TablePrice>{{ITServiceLineItemSubtotal(cart_item)}}</TablePrice>
      </td>
    </tr>

    <!--Items under this TIER-->
    <ITServiceLineItemSingle
      :is_standalone="false"
      :line_item="line_item"
      :cart_item="cart_item"
      v-for="line_item in cart_item.items">
    </ITServiceLineItemSingle>
  </template>

  <!--The Individual IT Services Items-->
  <template v-else>
    <ITServiceLineItemSingle
      :is_standalone="true"
      :line_item="line_item"
      :cart_item="cart_item"
      v-for="line_item in cart_item.items"
      @remove="RemoveTierCartItem(cart_index)">
    </ITServiceLineItemSingle>
  </template>
</template>
<script setup lang="ts">
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals.ts";
import {it_svc_terms} from "@/composables/ProposalComposable.ts";
import {td_border_style} from "@/composables/ProposalComposable.ts";
import {RemoveTierCartItem} from "@/composables/ProposalComposable.ts";
import {ITServiceLineItemSubtotal} from "@/composables/ProposalComposable.ts";

const store = GlobalStore();
const {proposal} = storeToRefs(store);
const props = defineProps(['cart_item','cart_index']);

// This watcher will update all the charge types if the tier charge type is changed
watch(()=>props.cart_item.tier?.charge_type,new_term => {
  proposal.value.it_service_items.forEach((this_cart_item) => {
    // Change it for every Tier
    if (this_cart_item.tier) {
      this_cart_item.tier.charge_type = new_term;
    }

    // Change it for every line item
    this_cart_item.items.forEach((this_item) => {
      this_item.charge_type = new_term;
    });
  });
});
</script>
