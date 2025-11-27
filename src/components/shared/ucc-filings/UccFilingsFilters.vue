<template>
  <h3 class="font-weight-light mb-3">
    Filters
  </h3>

  <v-text-field
    density="compact"
    variant="outlined"
    placeholder="Search"
    v-model="filters.search"
    prepend-inner-icon="mdi-folder-search"
    @click:append-inner="filters.search=''"
    :append-inner-icon="filters.search.length? 'mdi-close':''">
  </v-text-field>
  <v-text-field
    :readonly="true"
    density="compact"
    variant="outlined"
    @click="date_modal=true"
    placeholder="Date range..."
    prepend-inner-icon="mdi-calendar-month"
    :append-inner-icon="filters.start_date.length? 'mdi-close':''">
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

  <MyModal color="none" max_width="700" v-model="date_modal" title="Select the date range...">
    <v-row>
      <v-col cols="6">
        <v-date-picker v-model="filters.start_date" hide-header>

        </v-date-picker>
      </v-col>
      <v-col cols="6">
        <v-date-picker v-model="filters.end_date" hide-header>

        </v-date-picker>
      </v-col>
    </v-row>
  </MyModal>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const date_modal = ref(false);
const {ucc_filing_filters:filters} = storeToRefs(store);
const {ucc_providers,ucc_assignees} = storeToRefs(store);
</script>
