<template>
  <AppLayout>
    <template #title>Assignees</template>
    <template #content>
      <v-card :style="theme_card_style">
        <v-card-text>
          <v-data-table
            :headers="headers"
            :items="ucc_assignees"
            density="comfortable"
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
const {ucc_assignees} = storeToRefs(store);
const headers = [
  {title: 'ID',    value: 'id',              sortable: true},
  {title: 'Name',  value: 'assignee_company',sortable: true},
  {title: 'Class', value: 'assignee_class',  sortable: true},
  {title: 'City',  value: 'assignee_city',   sortable: true},
  {title: 'State', value: 'assignee_state',  sortable: true},
]
</script>
