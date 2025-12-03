<template>

  <FlexedBetween class="mb-5">
    <h3 class="font-weight-light">
      Filters
    </h3>
    <v-btn
      size="small"
      variant="text"
      class="d-flex"
      @click="ClearFilters"
      text="Clear Filters"
      prepend-icon="mdi-close">
    </v-btn>
  </FlexedBetween>

  <v-text-field
    density="compact"
    variant="outlined"
    placeholder="Search"
    v-model="filters.search"
    prepend-inner-icon="mdi-folder-search"
    @click:append-inner="filters.search=''"
    :append-inner-icon="filters.search? 'mdi-close':''">
  </v-text-field>

  <v-select
    density="compact"
    variant="outlined"
    :items="state_centers"
    v-model="filters.buyer_state"
    label="State"
    item-title="abbrev"
    item-value="abbrev"
    :return-object="false"
    placeholder="State"
    @click:append-inner="filters.buyer_state=null"
    :append-inner-icon="filters.buyer_state?'mdi-close':''"
    prepend-inner-icon="mdi-city">
  </v-select>

</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import type {UccBuyersFiltersType} from "@/types/StoreTypes";
import {state_centers} from "@/composables/GlobalComposables";

const store = GlobalStore();
const {ucc_buyers_filters:filters} = storeToRefs(store);

const ClearFilters = () => {
  filters.value = <UccBuyersFiltersType>{
    search:   null,
    state:    null,
  }
}
</script>
