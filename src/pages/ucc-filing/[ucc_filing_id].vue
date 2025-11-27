<template>
  <AppLayout>
    <template #title>UCC Filings</template>
    <template #content>
      <UccFilingsViewer :ucc_filing_id="ucc_filing_id" v-if="is_data_loaded && ucc_filing_id"/>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useRoute} from "vue-router";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const route = useRoute<any>();
const ucc_filing_id = ref(null);
const {is_data_loaded} = storeToRefs(store);

onMounted(() => {
  ucc_filing_id.value = route.params.ucc_filing_id;
});
</script>
