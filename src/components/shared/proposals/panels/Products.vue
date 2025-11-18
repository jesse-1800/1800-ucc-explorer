<template>
  <v-expansion-panels :style="theme_border_radius" :model-value="panels.products" @update:model-value="store.SetPanel('products',$event)" class="mb-4">
    <panel class="border" :style="theme_border_radius" icon="mdi-cart-variant">
      <template #icon>
        <DragHandle/>
      </template>

      <!--Products Title-->
      <template #title>
        &nbsp; Products
        <v-chip size="small" class="ml-3 mr-3" color="info" variant="elevated">{{proposal.cart_items.length}}</v-chip>
      </template>

      <!--Selector for IT Services-->
      <template v-if="it_service_category">
        <ITServiceSelector/>
      </template>

      <!--Selector for Copiers-->
      <template v-if="copier_lease_category">
        <ProductSelector/>
      </template>


      <!--PRODUCTS AND IT SERVICES LISTING-->
      <v-card-text>
        <!--Product Editor-->
        <template v-if="copier_lease_category">
          <ProductEditor
            :index="index"
            :item="cart_item"
            v-for="(cart_item,index) in proposal.cart_items">
          </ProductEditor>
        </template>

        <!--IT Service Editor-->
        <template v-if="it_service_category">
          <ITServiceEditor
            :index="cart_index"
            :cart_item="cart_item"
            :cart_index="cart_index"
            v-for="(cart_item,cart_index) in proposal.it_service_items">
          </ITServiceEditor>
        </template>

        <!--Placeholder Message-->
        <NoProductsYet/>
      </v-card-text>
    </panel>
  </v-expansion-panels>
</template>
<script setup lang="ts">

import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {it_service_category, theme_border_radius} from "@/composables/GlobalComposables";
import {copier_lease_category} from "@/composables/GlobalComposables";

const store = GlobalStore();
const {proposal,is_reordering,panels} = storeToRefs(store);

watch(is_reordering, (newVal) => {
  if (newVal) {
    const new_panels = { ...panels.value }
    new_panels.products = 1
    store.SetState({ panels: new_panels })
  }
});
</script>
