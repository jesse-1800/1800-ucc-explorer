<template>
  <!-- UCC Filing (always visible) -->
  <v-tabs v-model="active_tab" grow>
    <v-tab value="ucc-filing">UCC Filing</v-tab>
    <v-tab value="equipments">Equipments</v-tab>
    <v-tab value="buyer">Buyer</v-tab>
    <v-tab value="provider">Service Provider</v-tab>
    <v-tab value="assignee">Assignee</v-tab>
    <v-tab value="contacts">Contacts</v-tab>
  </v-tabs>

  <v-window v-model="active_tab">
    <v-window-item value="ucc-filing">
      <v-card>
        <v-card-text>
          <v-row>
            <v-col cols="12" sm="4">
              <v-text-field variant="underlined" label="Transaction ID" :model-value="ucc_filing.ucc_transaction_id" readonly/>
            </v-col>

            <v-col cols="12" sm="4">
              <v-text-field variant="underlined" label="Filing Date" :model-value="ucc_filing.ucc_date" readonly/>
            </v-col>

            <v-col cols="12" sm="4">
              <v-text-field variant="underlined" label="Lease Acq. Date" :model-value="ucc_filing.ucc_lease_acqui_date" readonly/>
            </v-col>

            <v-col cols="12" sm="4">
              <v-text-field variant="underlined" label="Status" :model-value="ucc_filing.ucc_status" readonly/>
            </v-col>

            <v-col cols="12" sm="4">
              <v-text-field variant="underlined" label="Lien" :model-value="ucc_filing.ucc_lien" readonly/>
            </v-col>

            <v-col cols="12" sm="4">
              <v-text-field variant="underlined" label="Batch" :model-value="ucc_filing.ucc_batch" readonly/>
            </v-col>

            <v-col cols="12">
              <v-textarea variant="outlined" rows="2" label="Comments" :model-value="ucc_filing.ucc_comments" readonly/>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-window-item>
    <v-window-item value="equipments">
      <v-card>
        <v-card-text>
          <EquipmentsTable :equipments="equipments"/>
        </v-card-text>
      </v-card>
    </v-window-item>
    <v-window-item value="buyer">
      <v-card class="pb-10">
        <v-card-text>
          <v-row>
            <v-col cols="12">
              <v-text-field variant="underlined" label="Company" :model-value="buyer.buyer_company" readonly/>
            </v-col>
            <v-col cols="12">
              <v-text-field variant="underlined" label="Address" :model-value="buyer.buyer_adress1" readonly/>
            </v-col>
            <v-col cols="12" sm="4">
              <v-text-field variant="underlined" label="City" :model-value="buyer.buyer_city" readonly/>
            </v-col>
            <v-col cols="12" sm="4">
              <v-text-field variant="underlined" label="State" :model-value="buyer.buyer_state" readonly/>
            </v-col>
            <v-col cols="12" sm="4">
              <v-text-field variant="underlined" label="ZIP" :model-value="buyer.buyer_zip" readonly/>
            </v-col>
            <v-col cols="12">
              <v-text-field variant="underlined" label="Phone" :model-value="buyer.buyer_phone" readonly/>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-window-item>
    <v-window-item value="provider">
      <v-card class="pb-10">
        <v-card-text v-if="provider">
          <v-text-field variant="underlined" label="Provider Company" :model-value="provider.provider_company" readonly/>
          <v-text-field
            variant="underlined"
            label="Location"
            hide-details
            :model-value="`${provider.provider_city}, ${provider.provider_state}`"
            readonly
          />
        </v-card-text>
        <v-card-text v-else>
          <h2 class="my-10 text-center font-weight-light">No Service Provider found</h2>
        </v-card-text>
      </v-card>
    </v-window-item>
    <v-window-item value="assignee">
      <v-card class="pb-10">
        <v-card-text v-if="assignee">
          <v-text-field variant="underlined" label="Assignee Company" :model-value="assignee.assignee_company" readonly/>
          <v-text-field
            variant="underlined"
            label="Location"
            hide-details
            :model-value="`${assignee.assignee_city}, ${assignee.assignee_state}`"
            readonly
          />
        </v-card-text>
        <v-card-text v-else>
          <h2 class="my-10 text-center font-weight-light">No assignee found</h2>
        </v-card-text>
      </v-card>
    </v-window-item>
    <v-window-item value="contacts">
      <v-card>
        <v-card-text>
          <v-data-table
            :style="theme_table_style"
            :headers="contact_headers"
            :items="mapped_contacts"
            density="comfortable">
            <template #item.actions="{ item }">
              <v-btn
                text="Edit"
                size="small"
                color="primary"
                variant="outlined"
                prepend-icon="mdi-pencil"
                @click="EditContact(item)">
              </v-btn>
            </template>
            <template #footer.prepend>
              <v-btn
                size="small"
                variant="flat"
                color="primary"
                @click="AddContact"
                prepend-icon="mdi-plus">
                Add Contact
              </v-btn>
              <v-spacer/>
            </template>
          </v-data-table>
        </v-card-text>
      </v-card>
    </v-window-item>
  </v-window>

  <ContactForm :buyer_id="buyer?.id" :edit_contact="edit_contact"/>
</template>

<script setup>
import {GlobalStore} from "@/stores/globals";
import {storeToRefs} from "pinia";
import {theme_main_background, theme_table_style} from "@/composables/GlobalComposables.js";
import EquipmentsTable from "@/components/shared/equipments/EquipmentsTable.vue";

const store = GlobalStore();
const active_tab = ref("ucc-filing")
const ucc_filing = computed(() => {
  return ucc_filings.value.find(ucc => ucc.id === props.ucc_filing_id)
});
const contacts = computed(() => {
  return ucc_contacts.value.filter(c => c.buyer_id === ucc_filing.value.buyer_id);
});
const buyer = computed(() => {
  return ucc_buyers.value.find(b => b.id === ucc_filing.value.buyer_id);
});
const provider = computed(() => {
  return ucc_providers.value.find(p => p.id === ucc_filing.value.provider_id);
});
const assignee = computed(() => {
  return ucc_assignees.value.find(a => a.id === ucc_filing.value.assignee_id);
});
const equipments = computed(() => {
  return ucc_equipments.value.filter(e => e.ucc_filing_id === ucc_filing.value.id)
});
const props = defineProps(['ucc_filing_id']);
const mapped_contacts = computed(() => {
  return contacts.value.map(c => ({
    ...c,
    name: `${c.firstname} ${c.lastname}`,
  }))
})
const contact_headers = [
  {title: 'Name',   value: 'name',sortable:true},
  {title: 'Title',  value: 'title',sortable:true},
  {title: 'Email',  value: 'email',sortable:true},
  {title: 'Phone',  value: 'phone',sortable:true},
  {title: 'Actions',value: 'actions', sortable: false},
]
const edit_contact = ref(null);
const {
  modals,
  ucc_buyers,
  ucc_filings,
  ucc_contacts,
  ucc_providers,
  ucc_assignees,
  ucc_equipments,
} = storeToRefs(store);

const EditContact = (item) => {
  edit_contact.value = ucc_contacts.value.find(c => c.id === item.id);
  modals.value.contact_form = true;
}
const AddContact = () => {
  edit_contact.value = null;
  modals.value.contact_form = true;
}
</script>
