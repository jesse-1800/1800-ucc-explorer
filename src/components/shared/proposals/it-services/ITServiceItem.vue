<template>
  <v-expansion-panels :model-value="is_standalone==true?0:1" class="mb-4 animate-moveFromRight">
    <panel class="border" icon="mdi-semantic-web">
      <template #title>
        <span class="ml-3 mr-3">{{line_item.name}}</span>
        <v-spacer/>
        <v-chip v-if="is_standalone" size="x-small" class="mr-5">STANDALONE</v-chip>
      </template>
      <v-card>
        <v-card-text>
          <v-row>
            <v-col cols="12" lg="6" md="6" sm="12">
              <v-text-field variant="outlined" label="Unit Price" v-model.number="line_item.unit_price" type="number"/>
            </v-col>
            <v-col cols="12" lg="6" md="6" sm="12">
              <v-text-field variant="outlined" label="Quantity" v-model.number="line_item.quantity" type="number"/>
            </v-col>
            <v-col cols="12" lg="6" md="6" sm="12">
              <v-text-field variant="outlined" label="Margin (%)" v-model.number="line_item.price_margin" type="number" hide-details/>
            </v-col>
            <v-col cols="12" lg="6" md="6" sm="12">
              <v-select :disabled="!is_standalone" variant="outlined" label="Term" v-model="line_item.charge_type" :items="it_svc_terms" hide-details/>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
      <template #footer>
        <v-spacer/>
        <v-btn
          color="red"
          width="120"
          size="small"
          text="Remove"
          variant="outlined"
          @click="RemoveItem"
          :style="theme_border_radius"
          prepend-icon="mdi-close-circle"
          class="d-flex ma-auto mt-2 mb-1 mr-1">
        </v-btn>
        <v-spacer/>
      </template>
    </panel>
  </v-expansion-panels>
</template>
<script setup lang="ts">
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {it_svc_terms} from "@/composables/ProposalComposable";
import {RemoveTierCartItem} from "@/composables/ProposalComposable";
import {theme_border_radius} from "@/composables/GlobalComposables";

const store = GlobalStore();
const {proposal} = storeToRefs(store);
const props = defineProps([
  'cart_index',
  'line_item',
  'is_standalone',
  'line_item_index',
]);

const RemoveItem = () => {
  proposal.value.it_service_items[props.cart_index].items.splice(props.line_item_index,1);

  // If there are no more items in this tier, remove the entire tier
  if (proposal.value.it_service_items[props.cart_index].items.length <= 0) {
    RemoveTierCartItem(props.cart_index);
  }
}
</script>
