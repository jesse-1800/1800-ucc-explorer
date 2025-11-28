<template>
  <AppLayout>
    <template #title>Providers</template>
    <template #content>
      <v-card :style="theme_card_style">
        <v-card-text>
          <v-data-table
            :headers="headers"
            density="comfortable"
            :items="ucc_providers"
            :style="theme_table_style">
          </v-data-table>
        </v-card-text>
      </v-card>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {theme_card_style, theme_table_style} from "@/composables/GlobalComposables.ts";

const store = GlobalStore();
const {ucc_providers} = storeToRefs(store);
const headers = [
  {title: 'ID',    value: 'id',              sortable: true},
  {title: 'Name',  value: 'provider_company',sortable: true},
  {title: 'Class', value: 'provider_class',  sortable: true},
  {title: 'City',  value: 'provider_city',   sortable: true},
  {title: 'State', value: 'provider_state',  sortable: true},
]
</script>
