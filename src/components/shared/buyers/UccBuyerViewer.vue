<template>
  <MyModal
    max_width="800"
    color="transparent"
    v-model="modals.customer_profile"
    title="Customer Profile">
    <v-card-text class="border rounded-lg p-5">
      <BuyerForm :condensed="true" :buyer="buyer"/>
    </v-card-text>

    <v-expansion-panels elevation="0" class="mt-5">
      <panel icon="mdi-contacts" title="Contacts">
        <ContactsTable class="border" :contacts="contacts" :buyer_id="buyer.id"/>
      </panel>
      <panel icon="mdi-text-box-multiple-outline" :title="`UCC Filings (${ucc_filings.length})`">
        <UccFilingTable :ucc_filings="ucc_filings"/>
      </panel>
    </v-expansion-panels>
  </MyModal>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {UccServer} from "@/plugins/ucc-server";
import BuyerForm from "@/components/shared/shared-forms/BuyerForm.vue";
import UccFilingTable from "@/components/shared/shared-forms/UccFilingTable.vue";

const buyer = ref(null);
const contacts = ref([]);
const ucc_filings = ref([]);

const {modals} = storeToRefs(GlobalStore());
const {getAccessTokenSilently} = useAuth0();
const {buyer_id} = defineProps(['buyer_id']);

const FindBuyerProfile = async () => {
  const token = await getAccessTokenSilently();
  UccServer(token).get(`/buyers/buyer-profile/${buyer_id}`).then(res=>{
    buyer.value = res.data.buyer;
    contacts.value = res.data.contacts;
    ucc_filings.value = res.data.ucc_filings;
  });
}

watch(()=>buyer_id,(new_buyer_id) => {
  if (new_buyer_id) FindBuyerProfile();
});
</script>
