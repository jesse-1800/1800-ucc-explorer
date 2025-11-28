<template>
  <AppLayout>
    <template #title>Assignees</template>
    <template #content>
      <v-data-table :items="ucc_assignees" :headers="headers"/>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";

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
