<template>
  <AppLayout>
    <template #title>Buyers</template>
    <template #content>
      <v-card :style="theme_card_style">
        <v-card-text>
          <TableSearch @click="Refresh"/>
          <v-data-table
            :headers="headers"
            density="comfortable"
            :items="filtered_buyers"
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
import {useAuth0} from "@auth0/auth0-vue";

const store = GlobalStore();
const headers = [
  { title: 'ID',              value: 'id',            sortable: true },
  { title: 'Company',         value: 'buyer_company', sortable: true },
  { title: 'Phone',           value: 'buyer_phone',   sortable: true },
  { title: 'Fax',             value: 'buyer_fax',     sortable: true },
  { title: 'FIPS Code',       value: 'buyer_fips',    sortable: true },
  { title: 'SIC Code',        value: 'buyer_sic',     sortable: true },
  { title: 'SIC Description', value: 'buyer_sic_desc',sortable: true },
  { title: 'DUNS Number',     value: 'buyer_duns',    sortable: true },
];
const {getAccessTokenSilently} = useAuth0();
const {ucc_buyers,table_search} = storeToRefs(store);
const filtered_buyers = computed(() => {
  const search_term = table_search.value.toLowerCase().trim();

  // If search is empty, return all items
  if (!search_term) return ucc_buyers.value;

  // Search across all specified properties
  return ucc_buyers.value.filter((buyer: any) => {
    return (
      buyer.id?.toString().toLowerCase().includes(search_term) ||
      buyer.buyer_company?.toString().toLowerCase().includes(search_term) ||
      buyer.buyer_phone?.toString().toLowerCase().includes(search_term) ||
      buyer.buyer_fax?.toString().toLowerCase().includes(search_term) ||
      buyer.buyer_fips?.toString().toLowerCase().includes(search_term) ||
      buyer.buyer_sic?.toString().toLowerCase().includes(search_term) ||
      buyer.buyer_sic_desc?.toString().toLowerCase().includes(search_term) ||
      buyer.buyer_duns?.toString().toLowerCase().includes(search_term)
    );
  });
});
const Refresh = async() => {
  const token = await getAccessTokenSilently();
  store.FetchAllData(token);
}
</script>
