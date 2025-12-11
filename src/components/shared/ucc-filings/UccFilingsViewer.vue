<template>
  <!-- UCC Filing (always visible) -->
  <v-tabs v-model="active_tab" grow>
    <v-tab value="ucc-filing">UCC Filing</v-tab>
    <v-tab value="equipments">Equipments</v-tab>
    <v-tab value="buyer">Customer</v-tab>
    <v-tab value="provider">Service Provider</v-tab>
    <v-tab value="assignee">Assignee</v-tab>
    <v-tab value="contacts">Contacts</v-tab>
  </v-tabs>

  <template v-if="ucc_filing">
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
            <BuyerForm :buyer="buyer"/>
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
            <ContactsTable :contacts="contacts" :buyer_id="buyer?.id"/>
          </v-card-text>
        </v-card>
      </v-window-item>
    </v-window>
  </template>
  <template v-else>
    <div class="text-center my-16">
      <v-progress-circular size="100" indeterminate/>
      <p class="mt-3">Loading data, please wait...</p>
    </div>
  </template>

  <ContactForm @onUpdate="FetchUccData" :buyer_id="buyer?.id" :edit_contact="edit_contact"/>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {UccServer} from "@/plugins/ucc-server";
import {theme_table_style} from "@/composables/GlobalComposables";
import EquipmentsTable from "@/components/shared/equipments/EquipmentsTable";
import BuyerForm from "@/components/shared/shared-forms/BuyerForm.vue";

const store = GlobalStore();
const is_loading = ref(false);
const active_tab = ref("ucc-filing")

const ucc_filing = ref(null);
const buyer      = ref(null);
const provider   = ref(null);
const assignee   = ref(null);
const contacts   = ref([]);
const equipments = ref([]);

const {ucc_filing_id} = defineProps(['ucc_filing_id']);
const contact_headers = [
  {title: 'Name',   value: 'name',sortable:true},
  {title: 'Title',  value: 'title',sortable:true},
  {title: 'Email',  value: 'email',sortable:true},
  {title: 'Phone',  value: 'phone',sortable:true},
  {title: 'Actions',value: 'actions', sortable: false},
];
const edit_contact = ref(null);
const {modals} = storeToRefs(store);
const {getAccessTokenSilently} = useAuth0();

const EditContact = (item) => {
  const the_contact = {...item};
  delete the_contact.name;
  edit_contact.value = the_contact;
  modals.value.contact_form = true;
}
const AddContact = () => {
  edit_contact.value = null;
  modals.value.contact_form = true;
}
const FetchUccData = async() => {
  is_loading.value = true;
  const token = await getAccessTokenSilently();
  UccServer(token).get(`/data/find_ucc_data/${ucc_filing_id}`).then(res=>{
    console.log(res.data);
    ucc_filing.value = res.data.ucc_filing;
    buyer.value      = res.data.buyer;
    contacts.value   = res.data.contacts;
    provider.value   = res.data.provider;
    assignee.value   = res.data.assignee;
    equipments.value = res.data.equipments;
  }).finally(()=>{
    is_loading.value = false;
  });
}

watch(()=>ucc_filing_id,(v)=> {
  if (v) FetchUccData();
},{immediate:true});
</script>
