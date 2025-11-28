<template>
  <AppLayout>
    <template #title>Buyers</template>
    <template #content>
      <v-card :style="theme_card_style">
        <v-card-text>
          <TableSearch @click="Refresh"/>
          <v-data-table :items="ucc_buyers" :headers="headers"/>
        </v-card-text>
      </v-card>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {theme_card_style} from "@/composables/GlobalComposables.ts";
import {useAuth0} from "@auth0/auth0-vue";

const store = GlobalStore();
const headers = [
  { title: 'ID',                  value: 'id',                  sortable: true },
  { title: 'Buyer Company',       value: 'buyer_company',       sortable: true },
  { title: 'Phone',               value: 'buyer_phone',         sortable: true },
  { title: 'Fax',                 value: 'buyer_fax',           sortable: true },
  { title: 'FIPS Code',           value: 'buyer_fips',          sortable: true },
  { title: 'SIC Code',            value: 'buyer_sic',           sortable: true },
  { title: 'SIC Description',     value: 'buyer_sic_desc',      sortable: true },
  { title: 'DUNS Number',         value: 'buyer_duns',          sortable: true },
];
const {ucc_buyers} = storeToRefs(store);
const {getAccessTokenSilently} = useAuth0();
const Refresh = async() => {
  const token = await getAccessTokenSilently();
  store.FetchAllData(token);
}
</script>
