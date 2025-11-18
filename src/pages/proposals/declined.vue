<template>
  <ProposalsTable :selected_proposals="DeclinedProposals"/>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {my_partner_id} from "@/composables/GlobalComposables";
import ProposalsTable from "@/components/shared/proposals/ProposalsTable.vue";

const store = GlobalStore();
const {proposals} = storeToRefs(store);

// Local computed properties
const DeclinedProposals = computed(() => {
  return proposals.value.filter(p => {
    return p.partner_id === my_partner_id.value && p.status === 'declined';
  });
});
</script>
