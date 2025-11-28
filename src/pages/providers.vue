<template>
  <AppLayout>
    <template #title>Providers</template>
    <template #content>
      <v-data-table :items="ucc_providers" :headers="headers"/>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";

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
