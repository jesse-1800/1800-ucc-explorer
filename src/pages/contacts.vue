<template>
  <AppLayout>
    <template #title>Contacts</template>
    <template #content>
      <v-card :style="theme_card_style">
        <v-card-text>
          <TableSearch @click="Refresh">
            <v-btn
              text="New Contact"
              color="primary"
              @click="$emit('refresh')"
              prepend-icon="mdi-plus-circle">
            </v-btn>
          </TableSearch>
          <v-data-table density="comfortable" :items="mapped_contacts" :headers="headers"/>
        </v-card-text>
      </v-card>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {FindUccBuyer, theme_card_style} from "@/composables/GlobalComposables.ts";
import {useAuth0} from "@auth0/auth0-vue";

const store = GlobalStore();
const headers = [
  {title: 'ID',       value: 'id',       sortable: true},
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
const {getAccessTokenSilently} = useAuth0();
const {ucc_contacts,table_search} = storeToRefs(store);
const mapped_contacts = computed(() => {
  const search_term = table_search.value.toLowerCase().trim();

  // Map the contacts first
  const mapped = ucc_contacts.value.map((c: any) => ({
    ...c,
    name: `${c.firstname} ${c.lastname}`,
    company: FindUccBuyer(c.buyer_id)?.buyer_company || '(None)',
  }));

  // If search is empty, return all mapped items
  if (!search_term) return mapped;

  // Search across all specified properties
  return mapped.filter((contact: any) => {
    return (
      contact.id?.toString().toLowerCase().includes(search_term) ||
      contact.company?.toString().toLowerCase().includes(search_term) ||
      contact.name?.toString().toLowerCase().includes(search_term) ||
      contact.title?.toString().toLowerCase().includes(search_term) ||
      contact.email?.toString().toLowerCase().includes(search_term) ||
      contact.phone?.toString().toLowerCase().includes(search_term) ||
      contact.address?.toString().toLowerCase().includes(search_term) ||
      contact.city?.toString().toLowerCase().includes(search_term) ||
      contact.state?.toString().toLowerCase().includes(search_term) ||
      contact.zip?.toString().toLowerCase().includes(search_term)
    );
  });
});
const Refresh = async() => {
  const token = await getAccessTokenSilently();
  store.FetchAllData(token);
}
</script>
