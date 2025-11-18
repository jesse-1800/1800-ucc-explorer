<template>
  <v-expansion-panels :model-value="0">
    <panel class="border" :style="theme_table_style" title="Addon Manager" icon="mdi-palette-swatch-outline">
      <v-data-table :style="theme_table_style" :loading="is_loading" :headers="headers" :items="filtered_addons">
        <template #top>
          <div class="pa-3 d-flex border-b align-center">
            <TableSearchBox v-model="search_model"/>
            <v-spacer/>
            <v-btn
              class="ml-3"
              color="info"
              text="Refresh"
              @click="Refresh"
              :style="theme_btn_style"
              :loading="is_loading"
              prepend-icon="mdi-refresh">
            </v-btn>
            <v-btn
              class="ml-3"
              color="info"
              text="New Addon"
              :style="theme_btn_style"
              @click="modals.addon=true;CleanForm()"
              prepend-icon="mdi-plus-circle">
            </v-btn>
          </div>
        </template>
        <template #item.actions="{ item }">
          <v-btn
            color="info"
            size="small"
            text="Edit"
            :style="theme_border_radius"
            variant="outlined"
            prepend-icon="mdi-pencil"
            @click="EditAddon(item)">
          </v-btn>
          <v-btn
            class="ml-2"
            color="red"
            size="small"
            text="Delete"
            :style="theme_border_radius"
            variant="outlined"
            prepend-icon="mdi-trash-can"
            @click="DeleteAddon(item.id)">
          </v-btn>
        </template>
      </v-data-table>
    </panel>
  </v-expansion-panels>

  <!--Addon Modal-->
  <my-modal v-model="modals.addon" color="transparent" title="Addon" max_width="600">
    <v-card-text>
      <v-form>
        <v-text-field variant="outlined" v-model="form.name" label="Name" required></v-text-field>
        <v-text-field variant="outlined" v-model.number="form.price" label="Price" type="number" required></v-text-field>
        <v-text-field variant="outlined" v-model.number="form.qty" label="Quantity" type="number" required></v-text-field>
        <v-text-field variant="outlined" v-model.number="form.price_margin" label="Price Margin (%)" type="number"></v-text-field>
        <v-select
          v-model="form.charge_type"
          :items="charge_types"
          variant="outlined"
          hide-details
          label="Type"
          required>
        </v-select>
      </v-form>
    </v-card-text>
    <template #footer>
      <v-spacer/>
      <v-btn :style="theme_btn_style" prepend-icon="mdi-pencil" color="info" @click="SubmitForm">Submit</v-btn>
      <v-spacer/>
    </template>
  </my-modal>
</template>

<script lang="ts" setup>
import moment from "moment";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import type {AddonType} from "@/types/StoreTypes";
import {ProposalServer} from "@/plugins/proposal-server";
import {charge_types} from "@/composables/ProductComposable";
import {
  my_addons,
  my_user_id,
  my_partner_id,
  theme_btn_style,
  theme_table_style,
  theme_border_radius
} from "@/composables/GlobalComposables";

const store = GlobalStore();
const search_model = ref('');
const is_loading = ref(false);
const headers = [
  {title: 'ID', value: 'id'},
  {title: 'Name', value: 'name'},
  {title: 'Price', value: 'price'},
  {title: 'Qty.', value: 'qty'},
  {title: 'Margin (%)', value: 'price_margin'},
/*  {title: 'Type', value: 'charge_type'},*/
  { title: 'Actions', key: 'actions', sortable: false }
];
const modals = ref({addon:false});
const form = ref<AddonType>({
  id:          null,
  user_id:     '',
  partner_id:  null,
  name:        '',
  type:        'addon',
  price:       0,
  qty:         0,
  price_margin:0,
  charge_type: '',
  created_at:  moment().format("YYYY-MM-DD"),
  updated_at:  moment().format("YYYY-MM-DD"),
})
const {getAccessTokenSilently} = useAuth0();
const filtered_addons = computed(() => {
  if (!search_model.value) return my_addons.value;
  const s = search_model.value.toLowerCase()
  return my_addons.value.filter((addon) => {
    return addon.name.toLowerCase().includes(s) || addon.charge_type.toLowerCase().includes(s);
  });
});

const Refresh = async () => {
  is_loading.value = true;
  const token = await getAccessTokenSilently();
  await store.FetchAddons(token);
  is_loading.value = false;
}
const EditAddon = (addon:AddonType) => {
  form.value = {...addon};
  form.value.updated_at = moment().format("YYYY-MM-DD");
  modals.value.addon = true;
}
const DeleteAddon = async(addon_id: number | null) => {
  const is_confirmed = await store.OpenDialog("Confirm Action", "Are you sure you want to delete this addon?");
  if (is_confirmed) {
    const token = getAccessTokenSilently();
    const endpoint = `/addons/destroy/${addon_id}`;
    ProposalServer(token).delete(endpoint).then(res => {
      if (res.data.result) {
        store.ShowSuccess('Addon deleted successfully!');
        store.FetchAddons(token);
      }
    });
  }
}
const CleanForm = () => {
  form.value = {
    id:          null,
    user_id:     '',
    partner_id:  null,
    name:        '',
    type:        'addon',
    price:       0,
    qty:         0,
    price_margin:0,
    charge_type: '',
    created_at:  '',
    updated_at:  '',
  };
  form.value.user_id = my_user_id.value;
  form.value.partner_id = my_partner_id.value;
}
const SubmitForm = () => {
  const token = getAccessTokenSilently();
  const form_data = new FormData;
  const endpoint = form.value.id? '/addons/update':'/addons/store';
  form_data.append('form', JSON.stringify(form.value));
  ProposalServer(token).post(endpoint,form_data).then(res => {
    if(res.data.result) {
      store.ShowSuccess('Addon saved successfully!');
      modals.value.addon = false;
      store.FetchAddons(token);
      CleanForm();
    }
  });
}

onMounted(async () => {
  const token = await getAccessTokenSilently();
  store.FetchPartnerUsers(token);
  store.FetchAddons(token).then(() => {
    form.value.user_id = my_user_id.value;
    form.value.partner_id = my_partner_id.value;
    form.value.created_at = moment().format("YYYY-MM-DD");
    form.value.updated_at = moment().format("YYYY-MM-DD");
  });
});
</script>
