<template>
  <v-card elevation="0">
    <v-card-text>
      <offset-columns columns="8" offset="2">
        <v-row>
          <v-col cols="12" lg="5" md="5" sm="12">
            <v-select
              @update:model-value="temporary_product=null"
              v-model.number="manufacturer_id"
              :items="my_manufacturers"
              label="Select a Brand"
              density="comfortable"
              variant="outlined"
              item-title="name"
              item-value="id"
              hide-details>
            </v-select>
          </v-col>
          <v-col cols="12" lg="5" md="5" sm="12">
            <v-combobox
              v-model="temporary_product"
              label="Select a Product"
              density="comfortable"
              :items="ListProducts"
              variant="outlined"
              item-title="name"
              return-object
              hide-details>
            </v-combobox>
          </v-col>
          <v-col cols="12" lg="2" md="2" sm="12">
            <v-btn
              size="large"
              class="w-100"
              text="Select"
              color="primary"
              @click="AddProduct"
              :style="theme_btn_style"
              prepend-icon="mdi-progress-check">
            </v-btn>
          </v-col>
        </v-row>
      </offset-columns>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {is_purchase} from "@/composables/ProposalComposable";
import {FindCostPerPrint, FindProductPrice} from "@/composables/ProductComposable";
import {GetOption, my_manufacturers, theme_btn_style} from "@/composables/GlobalComposables";
import {my_partner_object} from "@/composables/GlobalComposables";
import type {PrintCostType, ProductType} from "@/types/StoreTypes";
import {GetAccessoriesReference} from "@/composables/ProductComposable";

// Local states
const store = GlobalStore();
const {SetState,ShowError} = store;
const temporary_product = ref<any|null>(null);
const {products,proposal} = storeToRefs(store);
const manufacturer_id = ref<number|null>(null);

// Local functions & computed properties
const AddProduct = () => {
  if (!temporary_product.value) {
    return ShowError("Please select a product to add.");
  }

  const proposal_copy = {...proposal.value};
  const product_id = temporary_product.value.id;

  // Covers both copier and accessories
  const charge_type = is_purchase.value ? 'one-time' : 'monthly';

  proposal_copy.cart_items.push({
    // Type distinction
    type: 'copier',

    // Product
    product:<ProductType>{
      id: product_id,
      qty: 1,
      price: FindProductPrice(product_id) ?? 0,
      price_margin: 20,
      charge_type: charge_type,
    },

    // per-copier print cost
    print_cost: <PrintCostType>{
      charge_type: charge_type,
      black_prints_included: 0,
      color_prints_included: 0,
      black_prints_cost:     FindCostPerPrint('Black',product_id),
      color_prints_cost:     FindCostPerPrint('Color',product_id),
      black_prints_margin:   0,
      color_prints_margin:   0,
      black_overage_cost:    GetOption('global_overage_cost_black') ?? 0,
      color_overage_cost:    GetOption('global_overage_cost_color') ?? 0,
    },

    // Accessories
    accessories: GetAccessoriesReference(
      temporary_product.value
    ),
  });
  SetState({proposal: proposal_copy});
  temporary_product.value = null;
}

// Computed properties
const ListProducts = computed(() => {
  const my_brand_ids = my_partner_object.value?.supported_brands ?? [];

  // Filtered only be supported_brands
  const my_products = products.value.filter((p: any) => {
    return my_brand_ids?.includes(p.manufacturer_id)
  });

  if (!manufacturer_id.value) {
    return my_products;
  }

  // Filtered by manufacturer_id
  return my_products.filter((p: any) => {
    return p.manufacturer_id === manufacturer_id.value
  });
});
</script>
