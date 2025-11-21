<template>
  <AppLayout>
    <template #title>Financing Provider</template>
    <template #content>
      <v-btn
        class="mb-5"
        color="primary"
        @click="NewProvider"
        text="Add a Provider"
        :style="theme_btn_style"
        prepend-icon="mdi-plus-circle">
      </v-btn>

      <v-data-table
        :items="my_providers"
        :headers="headers"
        :style="theme_table_style"
        class="elevation-1 border">
        <template v-slot:item="{item}">
          <tr>
            <td>{{item.id}}</td>
            <td>{{item.name}}</td>
            <td>
              <v-switch
                @update:model-value="ToggleDefault(item)"
                :base-color="IsDefault(item)?'green':''"
                :model-value="IsDefault(item)"
                :disabled="IsDefault(item)"
                density="compact"
                color="green"
                hide-details
                inset>
              </v-switch>
            </td>
            <td>
              <v-btn
                text="Delete"
                size="small"
                color="red"
                class="mr-2"
                variant="outlined"
                @click="Delete(item)"
                prepend-icon="mdi-close"
                :disabled="IsDefault(item)"
                :style="theme_border_radius">
              </v-btn>
              <v-btn
                text="Edit"
                size="small"
                color="info"
                class="mr-2"
                variant="outlined"
                @click="Edit(item)"
                :style="theme_border_radius"
                prepend-icon="mdi-pencil">
              </v-btn>
              <v-btn
                color="info"
                size="small"
                class="mr-2"
                text="Duplicate"
                variant="outlined"
                @click="Duplicate(item)"
                :style="theme_border_radius"
                prepend-icon="mdi-content-copy">
              </v-btn>
              <v-btn
                color="red"
                size="small"
                variant="outlined"
                :href="`/paperwork/${item.id}`"
                text="Paperwork"
                :style="theme_border_radius"
                prepend-icon="mdi-text-box-edit-outline">
              </v-btn>
            </td>
          </tr>
        </template>
      </v-data-table>

      <MyModal
        v-model="modal"
        max_width="600"
        color="transparent"
        :title="`${provider.id?'Edit':'Add'} a Financing Provider`"
        @close="modal=false">

        <!--SMTP Settings-->
        <v-row>
          <v-col cols="12">
            <!--Company Details-->

            <v-text-field
              hide-details
              prepend-inner-icon="mdi-domain"
              variant="outlined"
              v-model="provider.name"
              label="Finance Company Name"
              required>
            </v-text-field>
          </v-col>
        </v-row>

        <v-expansion-panels model-value="0" elevation="0" class="mt-5">
          <panel class="border" icon="mdi-email-multiple-outline" title="Lease Factors">
            <v-card-text>
              <table class="w-100" style="border-collapse:collapse">
                <tbody>
                <tr>
                  <th class="w-25 pa-1 pt-3 pb-3 border">Term (Months)</th>
                  <th class="w-25 pa-1 pt-3 pb-3 border">FMV</th>
                  <th class="w-25 pa-1 pt-3 pb-3 border">Buyout</th>
                  <th class="w-25 pa-1 pt-3 pb-3 border">EFA</th>
                  <th class="w-25 pa-1 pt-3 pb-3 border">Manage</th>
                </tr>
                <tr v-for="(term,index) in provider.lease_factors">
                  <th class="border"><v-text-field variant="outlined" type="number" density="compact" hide-details class="d-block ma-auto w-100" v-model.number="term.term"/></th>
                  <td class="border"><v-text-field variant="outlined" type="number" density="compact" hide-details class="d-block ma-auto w-100" v-model.number="term.fmv"/></td>
                  <td class="border"><v-text-field variant="outlined" type="number" density="compact" hide-details class="d-block ma-auto w-100" v-model.number="term.buyout"/></td>
                  <td class="border"><v-text-field variant="outlined" type="number" density="compact" hide-details class="d-block ma-auto w-100" v-model.number="term.efa"/></td>
                  <td class="border">
                    <v-btn
                      text="Remove"
                      variant="text"
                      size="x-small"
                      @click="RemoveTerm(index)"
                      prepend-icon="mdi-trash-can">
                    </v-btn>
                  </td>
                </tr>
                </tbody>
              </table>

              <v-btn
                size="small"
                class="mt-4"
                @click="AddTerm"
                variant="outlined"
                text="Add New Term"
                prepend-icon="mdi-plus"
                :style="theme_border_radius">
              </v-btn>
            </v-card-text>
          </panel>
        </v-expansion-panels>

        <template #footer>
          <v-btn
            width="150"
            type="submit"
            text="Submit"
            @click="Submit"
            color="primary"
            :loading="is_loading"
            class="ma-auto d-block"
            :style="theme_btn_style">
          </v-btn>
        </template>
      </MyModal>
      <PaperworkEditor
        v-if="modals.paperwork_editor"
        :is_proposal="false"
        :provider="provider">
      </PaperworkEditor>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
  import {useAuth0} from "@auth0/auth0-vue";
  import {GlobalStore} from "@/stores/globals";
  import {UccServer} from "@/plugins/ucc-server.ts";
  import {
    theme_border_radius, my_partner_id, my_providers, theme_btn_style, theme_table_style, ToggleModal
  } from "@/composables/GlobalComposables";
  import {storeToRefs} from "pinia";
  import {useRouter} from "vue-router";

  const modal = ref(false);
  const router = useRouter();
  const store = GlobalStore();
  const is_loading = ref(false);
  const headers = [
    {title:'ID', value:'id'},
    {title:'Name', value:'name'},
    {title:'Is Default', value:'is_default'},
    {title:'Action', value:'action'}
  ];
  const provider = ref({
    id: null,
    partner_id: null,
    name: '',
    lease_factors: [
      {term: 12, fmv: 0, buyout: 0, efa: 0},
      {term: 24, fmv: 0, buyout: 0, efa: 0},
      {term: 36, fmv: 0, buyout: 0, efa: 0},
      {term: 48, fmv: 0, buyout: 0, efa: 0},
      {term: 60, fmv: 0, buyout: 0, efa: 0},
    ],
    created_at: '',
    updated_at: '',
  });
  const {modals} = storeToRefs(store);
  const {getAccessTokenSilently} = useAuth0();
  const {ShowSuccess,FetchPartnerUsers,FetchProviders} = store;

  // Local Functions
  const IsDefault = (the_provider:any) => {
    return the_provider.is_default == 1;
  }
  const ToggleDefault = async(the_provider:any) => {
    const token = await getAccessTokenSilently();
    const form = new FormData;

    is_loading.value = true;
    form.append('partner_id', my_partner_id.value);
    form.append('provider_id', the_provider.id);

    UccServer(token).post(`/providers/toggle-default`,form).then(res=>{
      console.log(res.data);
      store.ShowSuccess('Provider default updated successfully!');
      store.FetchProviders(token);
      is_loading.value = false;
    });
  }
  const Submit = async () => {
    const form = new FormData();
    const token = await getAccessTokenSilently();
    const route = provider.value.id ? "/providers/update":"/providers/store";
    const server = UccServer(token);

    if (!provider.value.lease_factors.length) {
      return store.ShowError("Please add at least one lease factor!")
    }

    is_loading.value = true;
    form.append("provider", JSON.stringify(provider.value));
    server.post(route, form).then(res => {
      console.log(res.data);
      FetchProviders(token);
      ShowSuccess(`Financing provider successfully ${provider.value.id?'edited!':'added!'}`);
    }).finally(() => {
      modal.value = false;
      is_loading.value = false;
    });
  };
  const Edit = (item:any) => {
    provider.value = {...item};
    modal.value = true;
  };
  const Duplicate = async(provider_obj:any) => {
    const form = new FormData();
    const copy = {...provider_obj};
    const token = await getAccessTokenSilently();

    is_loading.value = true;
    copy.name = copy.name + ' (Copy)';
    copy.is_default = 0;
    delete copy.id;

    form.append("provider", JSON.stringify(copy));
    UccServer(token).post('/providers/store', form).then(res => {
      console.log(res.data);
      FetchProviders(token);
      ShowSuccess(`Item has been duplicated successfully!`);
    }).finally(() => {
      is_loading.value = false;
    });
  };
  const Delete = async(the_provider:any) => {
    const confirm = await store.OpenDialog("Confirm Action", "Are you sure you want to delete this item?");
    if (confirm) {
      is_loading.value = true;
      const form = new FormData;
      const token = await getAccessTokenSilently();
      form.append('provider_id', the_provider.id);

      UccServer(token).post(`/providers/destroy`,form).then(res=>{
        console.log(res.data);
        store.ShowSuccess('Item deleted successfully!');
        store.FetchProviders(token);
        is_loading.value = false;
      });
    }
  };
  const NewProvider = () => {
    provider.value = {
      id: null,
      partner_id: my_partner_id.value,
      lease_factors: [
        {term: 12, fmv: 0, buyout: 0, efa: 0},
        {term: 24, fmv: 0, buyout: 0, efa: 0},
        {term: 36, fmv: 0, buyout: 0, efa: 0},
        {term: 48, fmv: 0, buyout: 0, efa: 0},
        {term: 60, fmv: 0, buyout: 0, efa: 0},
      ],
      name: '',
      created_at: '',
      updated_at: '',
    }
    modal.value = true;
  }
  const AddTerm = () => {
    provider.value.lease_factors.push({
      term: 0,
      fmv: 0,
      buyout: 0,
      efa: 0,
    })
  }
  const RemoveTerm = (index:number) => {
    provider.value.lease_factors.splice(index,1);
  }
  onMounted(async () => {
    const token = await getAccessTokenSilently();
    await FetchPartnerUsers(token).then(()=>{
      provider.value.partner_id = my_partner_id.value;
    });
  });
</script>
