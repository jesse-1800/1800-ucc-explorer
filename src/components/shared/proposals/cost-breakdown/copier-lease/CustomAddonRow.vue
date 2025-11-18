<template>
  <tr>
    <td class="pa-1" colspan="9">
      <v-btn
        size="small"
        variant="text"
        @click="AddCustomItem"
        text="Add a Custom Item..."
        prepend-icon="mdi-plus-circle">
      </v-btn>
    </td>
  </tr>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import type {AddonType} from "@/types/StoreTypes";

const store = GlobalStore();
const {proposal} = storeToRefs(store);
const AddCustomItem = () => {
  proposal.value.cost_addons.push(<AddonType>{
    name: "New Custom Item",
    type: "custom",
    price: 0,
    qty: 1,
    price_margin: 0,
    charge_type: 'monthly',
  });
};
</script>
