<template>
  <template v-if="proposal.cost_addons.length">
    <tr v-for="(addon,index) in proposal.cost_addons">
      <td class="pa-1">
        <TableInput
          type="text"
          class="w-100"
          variant="text"
          v-model="addon.name">
          <template #prepend-inner>
            <v-btn
              size="small"
              elevation="0"
              variant="text"
              icon="mdi-close"
              density="comfortable"
              @click="RemoveAddon(index)">
            </v-btn>
          </template>
        </TableInput>
      </td>
      <td>-</td>
      <td class="text-capitalize">{{addon.type}}</td>
      <td class="pa-1">
        <TableInput type="number" v-model.number="addon.qty"/>
      </td>
      <td class="pa-1">
        <TableInput icon="mdi-currency-usd" type="number" v-model.number="addon.price"/>
      </td>
      <td class="pa-1">
        <TableInput type="number" v-model.number="addon.price_margin"/>
      </td>
      <td class="pa-1">
        <TableInput
          type="number"
          :readonly="true"
          icon="mdi-currency-usd"
          :model-value="GetMarginPrice(addon.price * addon.qty,addon.price_margin).toFixed(2)">
        </TableInput>
      </td>
      <td class="pa-0" :style="td_border_style">
        <TableSelect variant="text" v-model="addon.charge_type" :items="custom_charge_types"/>
      </td>
      <td class="text-right">
        <TablePrice>{{SingleAddonTotal(proposal,addon).toFixed(2)}}</TablePrice>
      </td>
    </tr>
  </template>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals.js";
import {RemoveAddon} from "@/composables/ProductComposable.js";
import {charge_types} from "@/composables/ProductComposable.js";
import {GetMarginPrice} from "@/composables/ProductComposable.js";
import {SingleAddonTotal} from "@/composables/ProductComposable.js";
import {is_leasing, is_purchase, td_border_style} from "@/composables/ProposalComposable.js";
const {proposal} = storeToRefs(GlobalStore());
const custom_charge_types = computed(() => {
  const list = [];
  charge_types.value.forEach((charge_type) => {
    const this_type = {...charge_type}

    // Disable Monthly if Purchase
    if (is_purchase.value && charge_type.value === 'monthly') {
      this_type.props.disabled = true;
    }
    // Disable Fixed Monthly if leasing
    else if (!is_purchase.value && charge_type.value === 'fixed-monthly') {
      this_type.props.disabled = true;
    }
    /*// Remove One-Time if Leasing
    else if (is_leasing.value && charge_type.value === 'one-time') {
      return false;
    }*/
    else {
      this_type.props.disabled = false;
    }
    list.push(this_type);
  });
  return list;
})
</script>
