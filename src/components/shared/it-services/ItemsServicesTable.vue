<template>
  <v-expansion-panels :model-value="0">
    <panel class="border" :style="theme_table_style" title="IT Service Items" icon="mdi-sitemap-outline">
      <!--Service Items Table-->
      <v-data-table
        :headers="headers"
        class="elevation-1"
        :loading="is_loading"
        :style="theme_table_style"
        :items="filtered_it_services_items">
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
              text="New IT Service Item"
              prepend-icon="mdi-playlist-plus"
              @click="service_item_modal=true;it_service_item.id=null">
            </v-btn>
          </div>
        </template>
        <template v-slot:item="{item}">
          <tr>
            <td>{{item.id}}</td>
            <td>{{item.name}}</td>
            <td>{{FindTierName(item.tier_id)}}</td>
            <td>
              <v-btn :style="theme_border_radius" variant="outlined" color="primary" @click="EditItem(item)" prepend-icon="mdi-pencil" size="small" class="mr-2">Edit</v-btn>
              <v-btn :style="theme_border_radius" variant="outlined" color="red" @click="DeleteItem(item)" prepend-icon="mdi-close" size="small">Delete</v-btn>
            </td>
          </tr>
        </template>
      </v-data-table>
    </panel>
  </v-expansion-panels>

  <!--Service Item Modal/Form-->
  <MyModal max_width="700" v-model="service_item_modal" color="transparent" @close="service_item_modal=false" :title="`${it_service_item.id?'Edit':'Add'} an IT Service Item`">
    <v-select
      class="mt-2"
      item-value="id"
      item-title="name"
      variant="outlined"
      density="comfortable"
      label="Select Tier (Optional)"
      v-model.number="it_service_item.tier_id"
      :items="[...my_it_service_tiers,{id:null,name:'-- None --'}]">
    </v-select>

    <v-row>
      <!--Item Name-->
      <v-col cols="12" lg="6" md="6" sm="12">
        <v-text-field
          required
          hide-details
          label="Item Name"
          variant="outlined"
          density="comfortable"
          v-model="it_service_item.name">
        </v-text-field>
      </v-col>

      <!--Term-->
      <v-col cols="12" lg="6" md="6" sm="12">
        <v-select
          hide-details
          label="Term"
          item-value="value"
          item-title="title"
          variant="outlined"
          :disabled="HasTier(it_service_item)"
          :items='it_svc_terms'
          density="comfortable"
          :return-object="false"
          v-model="it_service_item.charge_type">
        </v-select>
      </v-col>

      <!--Price Margin-->
      <v-col cols="12" lg="6" md="6" sm="12">
        <v-text-field
          required
          hide-details
          type="number"
          variant="outlined"
          density="comfortable"
          label="Price Margin (%)"
          v-model.number="it_service_item.price_margin">
        </v-text-field>
      </v-col>

      <!--Unit Type-->
      <v-col cols="12" lg="6" md="6" sm="12">
        <v-combobox
          hide-details
          item-value="key"
          item-title="title"
          label="Unit Type"
          variant="outlined"
          density="comfortable"
          :return-object="false"
          :items="it_svc_unit_types"
          v-model="it_service_item.unit_type">
        </v-combobox>
      </v-col>

      <!--Unit Price-->
      <v-col cols="12" lg="6" md="6" sm="12">
        <v-text-field
          required
          hide-details
          type="number"
          label="Unit Price"
          variant="outlined"
          density="comfortable"
          v-model.number="it_service_item.unit_price">
        </v-text-field>
      </v-col>

      <!--Quantity-->
      <v-col cols="12" lg="6" md="6" sm="12">
        <v-text-field
          required
          hide-details
          type="number"
          label="Quantity"
          variant="outlined"
          density="comfortable"
          v-model.number="it_service_item.quantity">
        </v-text-field>
      </v-col>
    </v-row>

    <!--Description-->
    <text-editor
      variant="outlined"
      label="Description"
      style="height:250px"
      density="comfortable"
      v-model="it_service_item.description">
    </text-editor>

    <template #footer>
      <v-btn :style="theme_border_radius" color="info" variant="outlined" @click="service_item_modal=false">Cancel</v-btn>
      <v-spacer/>
      <v-btn :style="theme_btn_style" color="info" append-icon="mdi-play-outline" @click="SubmitForm">Submit</v-btn>
    </template>
  </MyModal>
</template>

<script lang="ts" setup>
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {ProposalServer} from "@/plugins/proposal-server";
import type {ITServiceItemType} from "@/types/StoreTypes";
import {my_user_id, theme_border_radius, theme_btn_style, theme_table_style} from "@/composables/GlobalComposables";
import {it_svc_terms} from "@/composables/ProposalComposable";
import {my_partner_id} from "@/composables/GlobalComposables";
import {timestamp_now} from "@/composables/GlobalComposables";
import {it_svc_unit_types} from "@/composables/ProposalComposable";
import {my_it_service_tiers} from "@/composables/GlobalComposables";
import {my_it_service_items} from "@/composables/GlobalComposables";

const store = GlobalStore();
const search_model = ref('');
const is_loading = ref(false);
const headers = ref([
  {sortable:true, title:'ID',        value:'id'},
  {sortable:true, title:'Name',      value:'name'},
  {sortable:true, title:'Tier/Group',value:'tier'},
  {sortable:true, title:'Action',    value:'action'}
]);
const service_item_modal = ref(false);
const {getAccessTokenSilently} = useAuth0();
const it_service_item = ref<ITServiceItemType>({
  id:          null,
  user_id:     "",
  partner_id:  null,
  tier_id:     null,
  name:        "",
  unit_price:  0,
  unit_type:   "",
  quantity:    1,
  description: "",
  charge_type: 'monthly',
  price_margin: 0,
  created_at:  "",
  updated_at:  "",
});
const filtered_it_services_items = computed(() => {
  if (search_model.value === '') return my_it_service_items.value;

  const s = search_model.value.toLowerCase();
  return my_it_service_items.value.filter((item:ITServiceItemType) => {
    return item.name.toLowerCase().includes(s) || (item.tier_id && FindTierName(item.tier_id).toLowerCase().includes(s));
  });
});

const Fetch = async () => {
  const token = await getAccessTokenSilently();
  is_loading.value = true;
  store.FetchItServiceItemsAndTiers(token).finally(() => {
    is_loading.value = false;
  });
};
const SubmitForm = async () => {
  const form  = new FormData();
  const token = await getAccessTokenSilently();
  const route = it_service_item.value.id ? "update-item":"store-item";

  is_loading.value = true;
  form.append("item", JSON.stringify(it_service_item.value));

  ProposalServer(token).post(`/itservices/${route}`,form).then(res => {
    console.log(res.data);
    store.ShowSuccess(`Tiered Service successfully ${it_service_item.value.id?'edited!':'added!'}`);
  }).finally(() => {
    Fetch();
    service_item_modal.value = false;
    is_loading.value = false;
  });
};
const FindTierName = (tier_id:any) => {
  const tier = my_it_service_tiers.value.find((t:any) => +t.id === +tier_id);
  return tier ? tier.name : 'None';
};
const HasTier = (item:ITServiceItemType) => {
  return item.tier_id !== null && item.tier_id !== undefined && item.tier_id !== 0;
};
const EditItem = (item:ITServiceItemType) => {
  it_service_item.value = {...item};
  it_service_item.value.tier_id = Number(it_service_item.value.tier_id);
  service_item_modal.value = true;
};
const DeleteItem = async (item:ITServiceItemType) => {
  const token = await getAccessTokenSilently();
  const confirm = await store.OpenDialog("Confirm Action", "Delete this IT Service Item?");
  if (confirm) {
    ProposalServer(token).post('/itservices/delete-item/'+item.id).then(()=>{
      store.ShowSuccess("Successfully Deleted");
      Fetch();
    });
  }
};

onMounted(async() => {
  const token = await getAccessTokenSilently();
  store.FetchPartnerUsers(token).finally(() => {
    it_service_item.value.user_id    = my_user_id.value;
    it_service_item.value.partner_id = my_partner_id.value;
    it_service_item.value.created_at = timestamp_now.value;
    it_service_item.value.updated_at = timestamp_now.value;
  });
});
</script>
