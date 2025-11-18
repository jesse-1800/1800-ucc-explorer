<template>
  <v-expansion-panels :model-value="0" class="mb-4 animate-moveFromRight" :style="GetStyle">
    <panel class="border" icon="mdi-printer-pos-edit-outline">
      <template #title>
        &nbsp; {{FindProduct(item.product.id)?.name}}
        <v-chip
          color="info"
          size="small"
          class="ml-3 mr-3"
          variant="elevated"
          :text="item.product.qty">
        </v-chip>
      </template>

      <v-card>
        <v-card-text>
          <v-row>
            <v-col cols="12" lg="7" md="7" sm="12">
              <ProductDetails :item="item" :index="index"/>
              <AccessoriesTable :accessories="item.accessories" :cart_index="index"/>
              <PrintCostEditor :item="item"/>
            </v-col>
            <v-col cols="12" lg="5" md="5" sm="12">
              <image-stacks :product="FindProduct(item.product.id)" :attachments="GetAttachedDatasets(item)"/>
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
          @click="RemoveProduct(index)"
          prepend-icon="mdi-close-circle"
          :style="theme_border_radius"
          class="d-flex ma-auto mt-2 mb-1 mr-1">
        </v-btn>
        <v-btn
          size="small"
          color="primary"
          text="Duplicate"
          variant="outlined"
          @click="DuplicateProduct(item)"
          prepend-icon="mdi-close-circle"
          :style="theme_border_radius"
          class="d-flex ma-auto mt-2 mb-1 ml-1">
        </v-btn>
        <v-spacer/>
      </template>
    </panel>
  </v-expansion-panels>
</template>
<script setup lang="ts">
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals.ts";
import {FindProduct} from "@/composables/ProductComposable";
import {RemoveProduct} from "@/composables/ProductComposable";
import {DuplicateProduct} from "@/composables/ProductComposable";
import {GetAttachedDatasets} from "@/composables/ProductComposable";
import {theme_border_radius} from "@/composables/GlobalComposables.ts";

const store = GlobalStore();
const {proposal} = storeToRefs(store);
const {cart_items} = proposal.value;
const {item,index} = defineProps<{item: any,index: number}>()
const is_last_item = computed(() => {
  return cart_items.length == index + 1;
});
const GetStyle = computed(() => {
  if (is_last_item.value) {
    return 'margin-bottom: 0px;padding-bottom:1px';
  } else {
    return 'margin-bottom: 10px;padding-bottom:1px';
  }
});
</script>
