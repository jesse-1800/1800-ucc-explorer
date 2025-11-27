<template>
  <!-- UCC Filing (always visible) -->
  <v-expansion-panels :model-value="0" elevation="0" class="mb-5">
    <panel class="border" title="UCC Filing">
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
    </panel>
  </v-expansion-panels>

  <!-- Panels Row -->
  <v-row>
    <!-- Equipments -->
    <v-col cols="12">
      <v-expansion-panels :model-value="0" elevation="0">
        <panel class="border" title="Equipments">
          <v-card-text>
            <v-data-table
              :style="theme_table_style"
              :headers="equipment_headers"
              :items="equipments"
              density="comfortable">
            </v-data-table>
          </v-card-text>
        </panel>
      </v-expansion-panels>
    </v-col>

    <!-- Buyer -->
    <v-col cols="12" md="6">
      <v-expansion-panels :model-value="0" elevation="0">
        <panel class="border" title="Buyer">
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
        </panel>
      </v-expansion-panels>
    </v-col>

    <v-col cols="12" md="6">
      <!-- Provider -->
      <v-expansion-panels :model-value="0" elevation="0">
        <panel class="border" title="Service Provider">
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
        </panel>
      </v-expansion-panels>

      <!-- Assignee -->
      <v-expansion-panels :model-value="0" elevation="0" class="mt-md-8 mt-5">
        <panel class="border" title="Assignee">
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
        </panel>
      </v-expansion-panels>
    </v-col>

    <!-- Contacts -->
    <v-col cols="12">
      <v-expansion-panels :model-value="0" elevation="0">
        <panel class="border" title="Contacts">
          <v-card-text>
            <v-data-table
              :style="theme_table_style"
              :headers="contact_headers"
              :items="mapped_contacts"
              density="comfortable">
              <template #item.actions="{ item }">
                <v-btn size="small" variant="text">
                  View
                </v-btn>
              </template>
              <template #footer.prepend>
                <v-btn
                  size="small"
                  color="primary"
                  variant="flat"
                  @click="AddContact">
                  Add Contact
                </v-btn>
                <v-spacer/>
              </template>
            </v-data-table>
          </v-card-text>
        </panel>
      </v-expansion-panels>
    </v-col>
  </v-row>
</template>

<script setup>
import {GlobalStore} from "@/stores/globals";
import {storeToRefs} from "pinia";
import {theme_table_style} from "@/composables/GlobalComposables.js";

const store = GlobalStore();
const ucc_filing = computed(() => {
  return ucc_filings.value.find(ucc => ucc.id === props.ucc_file_id)
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
const props = defineProps(['ucc_file_id']);
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
const equipment_headers = [
  { title: 'Unit',         value: 'equipment_unit',     sortable: true },
  { title: 'UCC Year',     value: 'equipment_ucc_year', sortable: true },
  { title: 'Number',       value: 'equipment_number',   sortable: true },
  { title: 'Brand',        value: 'equipment_brand',    sortable: true },
  { title: 'Model',        value: 'equipment_model',    sortable: true },
  { title: 'Description',  value: 'equipment_desc',     sortable: true },
  { title: 'Code',         value: 'equipment_code',     sortable: true },
  { title: 'Serial Number',value: 'equipment_serial_no',sortable: true },
  { title: 'Size',         value: 'equipment_size',     sortable: true },
  { title: 'End Year',     value: 'equipment_end_year', sortable: true },
  { title: 'Value',        value: 'equipment_value',    sortable: true },
]
const {
  ucc_buyers,
  ucc_filings,
  ucc_contacts,
  ucc_providers,
  ucc_assignees,
  ucc_equipments,
} = storeToRefs(store);
</script>
