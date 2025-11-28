<template>
  <AppLayout>
    <template #title>Contacts</template>
    <template #content>
      <v-data-table :items="mapped_contacts" :headers="headers"/>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {FindUccBuyer} from "@/composables/GlobalComposables.ts";

const store = GlobalStore();
const {ucc_contacts} = storeToRefs(store);
const headers = [
  {title: 'id',       value: 'id',       sortable: true},
  {title: 'Company',  value: 'company',  sortable: true},
  {title: 'Name',     value: 'name',     sortable: true},
  {title: 'Title',    value: 'title',    sortable: true},
  {title: 'Email',    value: 'email',    sortable: true},
  {title: 'Phone',    value: 'phone',    sortable: true},
  {title: 'Address',  value: 'address',  sortable: true},
  {title: 'City',     value: 'city',     sortable: true},
  {title: 'State',    value: 'state',    sortable: true},
  {title: 'Zip',      value: 'zip',      sortable: true},
];

const mapped_contacts = computed(() => {
  return ucc_contacts.value.map((c:any) => ({
    ...c,
    name: `${c.firstname} ${c.lastname}`,
    company: FindUccBuyer(c.buyer_id)?.buyer_company || '(None)',
  }))
})
</script>
