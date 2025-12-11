<template>
</template>

<script lang="ts" setup>
import {useAuth0} from "@auth0/auth0-vue";
import {UccServer} from "@/plugins/ucc-server";

const buyer = ref(null);
const contacts = ref([]);
const equipments = ref([]);
const ucc_filings = ref([]);

const {getAccessTokenSilently} = useAuth0();
const {buyer_id} = defineProps(['buyer_id']);

const FindBuyer = async () => {
  const token = await getAccessTokenSilently();
  UccServer(token).get(`/buyers/find-buyer/${buyer_id}`).then(res => {
    buyer.value = res.data;
    console.log(buyer.value);
  });
}

watch(()=>buyer_id,(new_buyer_id) => {
  if (new_buyer_id) FindBuyer()
});
</script>
