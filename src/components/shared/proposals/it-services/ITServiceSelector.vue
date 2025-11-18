<template>
  <v-card-text>
    <ColumnOffset columns="6" offset="3">

      <!--Selector switcheroo-->
      <div class="text-center">
        <v-btn-toggle
          divided
          rounded="xl"
          color="info"
          elevation="3"
          density="compact"
          v-model="selector"
          class="w-64 ma-auto">
          <v-btn :style="selector=='tiered' ? theme_switch_background:''" value="tiered" class="flex-1">Tiered Services</v-btn>
          <v-btn :style="selector=='items' ? theme_switch_background:''" value="items" class="flex-1">IT Service Items</v-btn>
        </v-btn-toggle>
      </div>

      <!--Tiered Selector-->
      <div v-if="selector==='tiered'" class="mt-5 d-flex align-center">
        <v-select
          hide-details
          return-object
          item-title="name"
          variant="outlined"
          v-model="tier_model"
          density="comfortable"
          label="Tiered Services"
          :items="my_it_service_tiers">
        </v-select>
        <v-btn
          height="48"
          class="ml-2"
          color="info"
          size="large"
          text="Select"
          :style="theme_btn_style"
          :disabled="!tier_model"
          @click="InsertTier">
        </v-btn>
      </div>

      <!--Items Selector-->
      <div v-else class="mt-5 d-flex align-center">
        <v-select
          hide-details
          return-object
          item-title="name"
          variant="outlined"
          v-model="item_model"
          density="comfortable"
          label="IT Services Items"
          :items="my_it_service_items">
        </v-select>
        <v-btn
          class="ml-2"
          height="48"
          size="large"
          color="info"
          text="Select"
          :style="theme_btn_style"
          :disabled="!item_model"
          @click="InsertItem">
        </v-btn>
      </div>

    </ColumnOffset>
  </v-card-text>
</template>

<script lang="ts" setup>
import {my_it_service_tiers, theme_btn_style, theme_switch_background} from "@/composables/GlobalComposables.ts";
import {my_it_service_items} from "@/composables/GlobalComposables.ts";
import {GlobalStore} from "@/stores/globals.ts";
import {storeToRefs} from "pinia";
import type {ITServiceItemType, ITServiceTierType} from "@/types/StoreTypes.ts";

const store = GlobalStore();
const {proposal} = storeToRefs(store);

// Holds temporary models
const tier_model = ref<null|ITServiceTierType>(null);
const item_model = ref<null|ITServiceItemType>(null);

// Which select component is active
const selector   = ref('tiered');

// For inserting the selected item
const InsertTier = () => {
  const find_items = my_it_service_items.value.filter((item:any) => {
    return item.tier_id == tier_model.value!.id
  });

  if (find_items.length <= 0) {
    tier_model.value = null;
    return store.ShowError("No items found for this Tier List");
  }

  proposal.value.it_service_items.push({
    tier: {...tier_model.value},
    items: find_items,
  });

  tier_model.value = null;
}

// For inserting the selected tier
const InsertItem = () => {
  proposal.value.it_service_items.push({
    tier: null,
    items: [{...item_model.value}],
  });
  item_model.value = null;
}

</script>
