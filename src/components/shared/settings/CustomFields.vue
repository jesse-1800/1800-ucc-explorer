<template>
  <v-expansion-panels :model-value="0">
    <panel class="border" :style="theme_table_style" title="Custom Fields" icon="mdi-pencil-box-multiple-outline">
      <v-data-table :style="theme_table_style" :loading="is_loading" :headers="headers" :items="filtered_custom_fields">
        <template #top>
          <div class="pa-3 d-flex border-b align-center">
            <TableSearchBox v-model="search_model"/>
            <v-spacer/>
            <v-btn
              class="ml-3"
              color="info"
              text="Refresh"
              @click="Refresh"
              :loading="is_loading"
              :style="theme_btn_style"
              prepend-icon="mdi-refresh">
            </v-btn>
            <v-btn
              class="ml-3"
              color="info"
              text="New Custom Field"
              :style="theme_btn_style"
              @click="modal=true;CleanForm()"
              prepend-icon="mdi-plus-circle">
            </v-btn>
          </div>
        </template>
        <template #item.actions="{ item }">
          <v-btn
            color="info"
            size="small"
            text="Edit"
            variant="outlined"
            prepend-icon="mdi-pencil"
            :style="theme_border_radius"
            @click="EditField(item)">
          </v-btn>
          <v-btn
            class="ml-2"
            color="red"
            size="small"
            text="Delete"
            variant="outlined"
            prepend-icon="mdi-trash-can"
            :style="theme_border_radius"
            @click="DeleteField(item.id)">
          </v-btn>
        </template>
      </v-data-table>
    </panel>
  </v-expansion-panels>

  <!--Modal-->
  <MyModal v-model="modal" title="Custom Field" color="transparent" max_width="600">
    <v-card-text>
      <v-form>
        <v-text-field v-model="form.field_label" label="Label" variant="outlined" required></v-text-field>
        <v-text-field v-model="form.field_key" label="Unique Key" variant="outlined" required></v-text-field>
        <v-select v-model="form.field_type" label="Input Type" variant="outlined" :items="types" required/>

        <!--These are for "select" only-->
        <template v-if="form.field_type=='select'">
          <v-combobox
            chips
            multiple
            variant="outlined"
            v-model="form.field_options"
            label="Add your options here..">
          </v-combobox>
          <draggable v-model="form.field_options" item-key="id" class="mt-2" handle=".drag-handle">
            <template #item="{ element, index }">
              <v-list-item class="border mb-2">
                <template #prepend>
                  <v-icon class="drag-handle">mdi-drag</v-icon>
                </template>
                <v-list-item-title>{{ element }}</v-list-item-title>
                <template #append>
                  <v-icon
                    @click="form.field_options.splice(index, 1)"
                    class="cursor-pointer">
                    mdi-close
                  </v-icon>
                </template>
              </v-list-item>
            </template>
          </draggable>
        </template>

      </v-form>
    </v-card-text>
    <template #footer>
      <v-spacer/>
      <v-btn :style="theme_btn_style" prepend-icon="mdi-pencil" color="info" @click="SubmitForm">Submit</v-btn>
      <v-spacer/>
    </template>
  </MyModal>
</template>

<script lang="ts" setup>
import moment from "moment";
import draggable from 'vuedraggable';
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import type {CustomFieldType} from "@/types/StoreTypes";
import {UccServer} from "@/plugins/ucc-server.ts";
import {my_user_id, theme_border_radius, theme_btn_style, theme_table_style} from "@/composables/GlobalComposables";
import {my_partner_id} from "@/composables/GlobalComposables";
import {my_custom_fields} from "@/composables/GlobalComposables";

const store = GlobalStore();
const {getAccessTokenSilently} = useAuth0();
const form = ref<CustomFieldType>({
  id:         null,
  user_id:     '',
  partner_id: null,
  field_key:   '',
  field_type:  '',
  field_label: '',
  field_options: [], // for 'select' type
  created_at:  '',
  updated_at:  '',
})
const modal = ref(false);
const headers = [
  {title: 'ID', value: 'id'},
  {title: 'Label', value: 'field_label'},
  {title: 'Key', value: 'field_key'},
  {title: 'Type', value: 'field_type'},
  {title: 'Created at', value: 'created_at'},
  {title: 'Updated at', value: 'updated_at'},
  { title: 'Actions', key: 'actions', sortable: false }
];
const errors = ref<string[]>([]);
const types = ['text','number','date','textarea','select'];
const search_model = ref('');
const is_loading = ref(false);
const filtered_custom_fields = computed(() => {
  if (!search_model.value) return my_custom_fields.value;
  const s = search_model.value.toLowerCase();
  return my_custom_fields.value.filter(cf =>
    cf.field_label.toLowerCase().includes(s) ||
    cf.field_key.toLowerCase().includes(s) ||
    cf.field_type.toLowerCase().includes(s)
  );
})

const EditField = (field:CustomFieldType) => {
  form.value = {...field};
  form.value.updated_at = moment().format("YYYY-MM-DD");
  modal.value = true;
}
const DeleteField = (field_id: number | null) => {
  if (confirm("Are you sure you want to delete this field?")) {
    const token = getAccessTokenSilently();
    const endpoint = `/customfields/destroy/${field_id}`;
    UccServer(token).delete(endpoint).then(res => {
      if (res.data.result) {
        store.ShowSuccess('Custom Field deleted successfully!');
        store.FetchCustomFields(token);
      }
    });
  }
}
const CleanForm = () => {
  form.value = <CustomFieldType>{
    id:          null,
    user_id:       '',
    partner_id:  null,
    field_key:     '',
    field_type:    '',
    field_label:   '',
    field_options: [],
    created_at:    '',
    updated_at:    '',
  };
  SetInitialValues();
}
const Validated = () => {
  errors.value = [];
  const find_key = my_custom_fields.value.find(cf => cf.field_key==form.value.field_key);

  if (!form.value.field_label) {
    errors.value.push('- Field Label is required');
  }
  if (!form.value.field_key) {
    errors.value.push('- Field Key is required');
  }
  else if (find_key!=undefined && find_key.id != form.value.id) {
    errors.value.push('- Field Key already exists');
  }
  if (!form.value.field_type) {
    errors.value.push('- Field Type is required');
  }
  if (errors.value.length) {
    store.ShowError("There were errors: <br>"+errors.value.join('<br>'));
    return false;
  }
  return true;
}
const SubmitForm = () => {
  if (!Validated()) { return false; }

  const token = getAccessTokenSilently();
  const form_data = new FormData;
  const endpoint = form.value.id? '/customfields/update':'/customfields/store';
  form_data.append('form', JSON.stringify(form.value));
  UccServer(token).post(endpoint,form_data).then(res => {
    console.log(res.data);
    store.ShowSuccess(res.data.message);
    modal.value = false;
    CleanForm();
    Refresh();
  });
}
const SetInitialValues = () => {
  if (!form.value.id) {
    form.value.user_id = my_user_id.value;
    form.value.partner_id = my_partner_id.value;
    form.value.created_at = moment().format("YYYY-MM-DD");
    form.value.updated_at = moment().format("YYYY-MM-DD");
  }
}
const Refresh = async () => {
  const token = await getAccessTokenSilently();
  is_loading.value = true;
  store.FetchCustomFields(token).finally(() => {
    is_loading.value = false;
  });
}

watch(()=>form.value.field_label,(newVal)=>{
  if (!form.value.id) {
    form.value.field_key = newVal.trim().toLowerCase().replace(/\s+/g,"_");
  }
});

onMounted(async () => {
  const token = await getAccessTokenSilently();
  await store.FetchPartnerUsers(token);
  await store.FetchCustomFields(token).then(() => {
    SetInitialValues();
  });
});
</script>
