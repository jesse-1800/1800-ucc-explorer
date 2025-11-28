<template>
  <AppLayout>
    <template #title>Buyers</template>
    <template #content>
      <v-data-table :items="ucc_buyers" :headers="headers"/>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const {ucc_buyers} = storeToRefs(store);
const headers = [
  { title: 'ID',                  value: 'id',                  sortable: true },
  { title: 'Buyer Company',       value: 'buyer_company',       sortable: true },
  { title: 'Phone',               value: 'buyer_phone',         sortable: true },
  { title: 'Fax',                 value: 'buyer_fax',           sortable: true },
  { title: 'FIPS Code',           value: 'buyer_fips',          sortable: true },
  { title: 'SIC Code',            value: 'buyer_sic',           sortable: true },
  { title: 'SIC Description',     value: 'buyer_sic_desc',      sortable: true },
  { title: 'DUNS Number',         value: 'buyer_duns',          sortable: true },
]

</script>
