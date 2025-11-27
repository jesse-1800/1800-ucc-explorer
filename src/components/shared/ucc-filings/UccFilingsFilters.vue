<template>
  <h3 class="font-weight-light mb-3">
    Filters
  </h3>

  <v-text-field
    density="compact"
    variant="outlined"
    placeholder="Search"
    prepend-inner-icon="mdi-folder-search">
  </v-text-field>
  <v-text-field
    :readonly="true"
    density="compact"
    variant="outlined"
    placeholder="Date range..."
    prepend-inner-icon="mdi-calendar-month">
  </v-text-field>
  <v-combobox
    item-value="id"
    density="compact"
    variant="outlined"
    item-title="provider_company"
    :items="ucc_providers"
    placeholder="Service Provider"
    prepend-inner-icon="mdi-briefcase-outline">
  </v-combobox>
  <v-combobox
    item-value="id"
    density="compact"
    variant="outlined"
    item-title="assignee_company"
    :items="ucc_assignees"
    placeholder="Assignee"
    prepend-inner-icon="mdi-bank">
  </v-combobox>
</template>

<script setup>
import {GlobalStore} from "@/stores/globals";
import {storeToRefs} from "pinia";

const store = GlobalStore();
const {ucc_providers,ucc_assignees} = storeToRefs(store);
</script>
