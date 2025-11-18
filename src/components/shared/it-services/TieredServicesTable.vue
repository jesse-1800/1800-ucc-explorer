<template>
  <v-expansion-panels :model-value="0">
    <panel class="border" :style="theme_table_style" title="Tiered Services" icon="mdi-format-list-numbered-rtl">
      <!--Service Tiers Table-->
      <v-data-table
        :style="theme_table_style"
        :headers="headers"
        :loading="is_loading"
        :items="filtered_tier_list"
        class="elevation-1">
        <template #top>
          <div class="pa-3 d-flex border-b align-center">
            <TableSearchBox v-model="search_model"/>
            <v-spacer/>
            <v-btn
              class="mr-2"
              text="Refresh"
              @click="Fetch"
              color="primary"
              variant="outlined"
              :loading="is_loading"
              :style="theme_btn_style"
              prepend-icon="mdi-refresh">
            </v-btn>
            <v-btn
              class="mr-2"
              color="primary"
              variant="outlined"
              :style="theme_btn_style"
              text="Add a Tiered Service"
              prepend-icon="mdi-playlist-plus"
              @click="service_tier_modal=true;it_service_tier.id=null">
            </v-btn>
          </div>
        </template>
        <template v-slot:item="{item}">
          <tr>
            <td>{{item.id}}</td>
            <td>{{item.name}}</td>
            <td>
              <v-chip
                color="info"
                size="small"
                variant="elevated"
                :text="FindServiceItems(item).length">
              </v-chip>
            </td>
            <td>
              <v-btn :style="theme_border_radius" variant="outlined" color="primary" @click="EditTier(item)" prepend-icon="mdi-pencil" size="small" class="mr-2">Edit</v-btn>
              <v-btn :style="theme_border_radius" variant="outlined" color="red" @click="DeleteTier(item)" prepend-icon="mdi-close" size="small" :disabled="FindServiceItems(item).length>0">Delete</v-btn>
            </td>
          </tr>
        </template>
      </v-data-table>
    </panel>
  </v-expansion-panels>

  <!--Service Tiers Modal/Form-->
  <MyModal max_width="700" v-model="service_tier_modal" color="transparent" @close="service_tier_modal=false" :title="`${it_service_tier.id?'Edit':'Add'} a Tiered Service`">

    <v-text-field
      required
      class="mt-2"
      label="Tier Name"
      variant="outlined"
      density="comfortable"
      v-model="it_service_tier.name">
    </v-text-field>

    <!--Term-->
    <v-select
      hide-details
      item-value="value"
      item-title="title"
      variant="outlined"
      label="Term"
      :items='it_svc_terms'
      density="comfortable"
      :return-object="false"
      v-model="it_service_tier.charge_type">
    </v-select>

    <text-editor
      variant="outlined"
      label="Description"
      style="height:300px"
      density="comfortable"
      v-model="it_service_tier.description">
    </text-editor>

    <template #footer>
      <v-btn
        color="info"
        text="Cancel"
        variant="outlined"
        :style="theme_border_radius"
        @click="service_tier_modal=false">
      </v-btn>
      <v-spacer/>
      <v-btn
        color="info"
        text="Submit"
        @click="SubmitForm"
        :style="theme_btn_style"
        append-icon="mdi-play-outline">
      </v-btn>
    </template>
  </MyModal>
</template>

<script lang="ts" setup>
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {ProposalServer} from "@/plugins/proposal-server";
import type {ITServiceTierType} from "@/types/StoreTypes";
import {my_user_id, theme_border_radius, theme_btn_style, theme_table_style} from "@/composables/GlobalComposables";
import {it_svc_terms} from "@/composables/ProposalComposable";
import {my_partner_id} from "@/composables/GlobalComposables";
import {timestamp_now} from "@/composables/GlobalComposables";
import {my_it_service_tiers} from "@/composables/GlobalComposables";
import {my_it_service_items} from "@/composables/GlobalComposables";

const store = GlobalStore();
const search_model = ref('');
const is_loading = ref(false);
const headers = ref([
  {title:'ID', value:'id'},
  {title:'Name', value:'name'},
  {title:'Items', value:'items'},
  {title:'Action', value:'action'}
]);
const service_tier_modal = ref(false);
const {getAccessTokenSilently} = useAuth0();
const it_service_tier = ref<ITServiceTierType>({
  id:          null,
  user_id:     '',
  partner_id:  null,
  name:        '',
  description: '',
  charge_type:        'monthly',
  created_at:  '',
  updated_at:  '',
});
const filtered_tier_list = computed(() => {
  if (!search_model.value) return my_it_service_tiers.value;
  return my_it_service_tiers.value.filter((i:any) => {
    return i.name.toLowerCase().includes(search_model.value.toLowerCase());
  });
});

const DeleteTier = async (tier:ITServiceTierType) => {
  const token = await getAccessTokenSilently();
  const confirm = await store.OpenDialog("Confirm Action", `Are you sure you want to delete "${tier.name}"?`);
  if (confirm) {
    ProposalServer(token).post('/itservices/delete-tier/'+tier.id).then(()=>{
      store.ShowSuccess("Successfully Deleted");
      Fetch();
    });
  }
};
const EditTier = (item:ITServiceTierType) => {
  it_service_tier.value = {...item};
  service_tier_modal.value = true;
};
const SubmitForm = async () => {
  const form  = new FormData();
  const token = await getAccessTokenSilently();
  const route = it_service_tier.value.id ? "update-tier":"store-tier";

  is_loading.value = true;
  form.append("tier", JSON.stringify(it_service_tier.value));

  ProposalServer(token).post(`/itservices/${route}`,form).then(res => {
    console.log(res.data);
    store.ShowSuccess(`Tiered Service successfully ${it_service_tier.value.id?'edited!':'added!'}`);
  }).finally(() => {
    Fetch();
    service_tier_modal.value = false;
    is_loading.value = false;
  });
};
const Fetch = async () => {
  const token = await getAccessTokenSilently();
  is_loading.value = true;
  store.FetchItServiceItemsAndTiers(token).finally(() => {
    is_loading.value = false;
  });
};
const FindServiceItems = (item:any) => {
  return my_it_service_items.value.filter((i:any) => +i.tier_id === +item.id);
}

onMounted(async() => {
  const token = await getAccessTokenSilently();
  store.FetchPartnerUsers(token).finally(() => {
    it_service_tier.value.user_id    = my_user_id.value;
    it_service_tier.value.partner_id = my_partner_id.value;
    it_service_tier.value.created_at = timestamp_now.value;
    it_service_tier.value.updated_at = timestamp_now.value;
  });
});
</script>
