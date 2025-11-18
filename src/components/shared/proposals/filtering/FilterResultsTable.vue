<template>
  <p class="mb-5 text-center">A total of ({{ filtered_products.length }}) Results</p>
  <div class="border overflow-y-auto pr-1">
    <v-data-table
      density="compact"
      :items="filtered_products"
      :style="theme_table_style"
      class="filter-product-table"
      :loading="proposal_filter_loading"
      :headers="[{title:'Product',value:'name',sortable:true}]">
      <template #loading>
        <h3 class="font-weight-light py-15">Let me find something great for you...</h3>
      </template>
      <template #item="{item:product}:any">
        <td class="pl-3 d-flex justify-space-between align-center">
          <img width="40" height="40" :src="GetProductImage(product)" alt="">
          <div class="ml-2">{{ product.name }}</div>
          <v-spacer/>
          <v-btn
            size="small"
            text="Add"
            class="mr-2"
            color="primary"
            :style="theme_btn_style"
            @click="AddProduct(product)"
            prepend-icon="mdi-plus-circle">
          </v-btn>
        </td>
      </template>
    </v-data-table>
  </div>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {GetOption} from "@/composables/GlobalComposables";
import {is_purchase} from "@/composables/ProposalComposable";
import {theme_btn_style} from "@/composables/GlobalComposables";
import {theme_table_style} from "@/composables/GlobalComposables";
import {GetProductImage} from "@/composables/ProductComposable";
import {FindCostPerPrint} from "@/composables/ProductComposable";
import {FindProductPrice} from "@/composables/ProductComposable";
import type {PrintCostType, ProductType} from "@/types/StoreTypes";
import {GetAccessoriesReference} from "@/composables/ProductComposable";

const store = GlobalStore();
const {proposal,proposal_filter_loading} = storeToRefs(store);
const {filtered_products} = defineProps(['filtered_products']);

const AddProduct = (the_product: any) => {
  const product_id = the_product.id;

  // Covers both copier and accessories
  const charge_type = is_purchase.value ? 'one-time' : 'monthly';

  proposal.value.cart_items.push({
    // Type distinction
    type: 'copier',

    // Product
    product: <ProductType>{
      id: product_id, qty: 1, price: FindProductPrice(product_id) ?? 0, price_margin: 20, charge_type: charge_type,
    },

    // per-copier print cost
    print_cost: <PrintCostType>{
      charge_type: charge_type,
      black_prints_included: 0,
      color_prints_included: 0,
      black_prints_cost: FindCostPerPrint('Black', product_id),
      color_prints_cost: FindCostPerPrint('Color', product_id),
      black_prints_margin: 0,
      color_prints_margin: 0,
      black_overage_cost: GetOption('global_overage_cost_black') ?? 0,
      color_overage_cost: GetOption('global_overage_cost_color') ?? 0,
    },

    // Accessories
    accessories: GetAccessoriesReference(the_product),
  });
}
</script>
<style lang="scss">
.filter-product-table {
  .v-data-table-footer {
    padding: 0 !important;
  }
}
</style>
