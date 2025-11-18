
<template>
  <AppLayout>
    <template #title>Templates</template>
    <template #content>
      <div class="d-flex align-center mb-4">
        <TableSearchBox v-model="search_model"/>
        <v-spacer/>
        <v-btn
          color="info"
          text="Refresh"
          @click="Refresh"
          class="mr-3"
          :style="theme_btn_style"
          prepend-icon="mdi-refresh">
        </v-btn>
        <v-btn
          color="info"
          text="New Template"
          href="/templates/build"
          :style="theme_btn_style"
          prepend-icon="mdi-plus-circle">
        </v-btn>
      </div>

      <v-data-table class="border" :style="theme_table_style" :loading="is_loading" :items="filtered_templates" :headers="headers">
        <template #item="{item:template}">
          <tr>
            <td>{{ template.id }}</td>
            <td class="pa-2">
              <div class="thumbnail" :style="GetStyle(template)"></div>
            </td>
            <td>{{ template.name }}</td>
            <td>{{ template.created_at }}</td>
            <td>
              <v-switch
                @update:model-value="ToggleDefault(template)"
                :base-color="IsDefault(template)?'green':''"
                :model-value="IsDefault(template)"
                :disabled="IsDefault(template)"
                density="compact"
                color="green"
                hide-details
                inset>
              </v-switch>
            </td>
            <td>
              <TableButton :style="theme_border_radius" variant="outlined" icon="mdi-pencil" :href="`/templates/edit/${template.id}`" class="mr-2" label="Edit"/>
              <TableButton :style="theme_border_radius" variant="outlined" icon="mdi-content-copy" @click="DuplicateTemplate(template.id as number)" class="mr-2" label="Duplicate"/>
              <TableButton :style="theme_border_radius" variant="outlined" icon="mdi-trash-can" @click="DeleteTemplate(template.id as number)" label="Delete" color="red" :disabled="IsDefault(template)"/>
            </td>
          </tr>
        </template>
      </v-data-table>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {ProposalServer} from "@/plugins/proposal-server";
import {my_templates, theme_border_radius, theme_btn_style, theme_table_style} from "@/composables/GlobalComposables";
import {my_partner_id} from "@/composables/GlobalComposables";

const store = GlobalStore();
const search_model = ref('');
const is_loading = ref(false);
const headers = ref([
  {sortable: true, title: 'ID',         value: 'id'},
  {sortable: true, title: 'Preview',    value: 'preview'},
  {sortable: true, title: 'Name',       value: 'name'},
  {sortable: true, title: 'Created at', value: 'created_at'},
  {sortable: true, title: 'Is Default', value: 'is_default'},
  {sortable: true, title: 'Manage',     value: 'manage'},
]);
const {getAccessTokenSilently} = useAuth0();

const DeleteTemplate = async (id:number) => {
  const token = await getAccessTokenSilently();
  if (confirm('Are you sure you want to delete this template?')) {
    ProposalServer(token).post(`/templates/destroy/${id}`).then(() => {
      store.ShowSuccess('Template deleted successfully!');
      store.FetchTemplates(token);
    });
  }
}
const DuplicateTemplate = async (id:number) => {
  const token = await getAccessTokenSilently();
  ProposalServer(token).post(`/templates/duplicate/${id}`).then(res => {
    console.log(res.data)
    store.ShowSuccess('Template duplicated successfully!');
    store.FetchTemplates(token);
  });
}
const IsDefault = (template:any) => {
  return template.is_default == 1;
}
const ToggleDefault = async (template:any) => {
  const token = await getAccessTokenSilently();
  const form = new FormData;

  is_loading.value = true;
  form.append('partner_id', my_partner_id.value);
  form.append('template_id', template.id);

  ProposalServer(token).post(`/templates/toggle-default`,form).then(res=>{
    console.log(res.data);
    store.ShowSuccess('Template default updated successfully!');
    store.FetchTemplates(token);
    is_loading.value = false;
  });
}
const GetStyle = (template:any) => {
  const background_image = template.thumbnail ?? 'https://placehold.co/190x250?text=No%20Preview';
  return `background-image: url(${background_image})`;
}
const Refresh = async () => {
  const token = await getAccessTokenSilently();
  await store.FetchTemplates(token);
}
const filtered_templates = computed(() => {
  if (search_model.value === '') {
    return my_templates.value;
  }
  const s = search_model.value.toLowerCase();
  return my_templates.value.filter((template:any) => {
    return template.name.toLowerCase().includes(s) || template.id.toString().includes(s);
  });
});

onMounted(async() => {
  await Refresh();
});
</script>

<style>
.thumbnail {
  width: 100px;
  height: 150px;
  background-size: cover;
  background-position: center;
  border: 1px solid #ccc;
  border-radius: 5px;

  p {
    text-align: center;
    margin-top: 10px;
  }
}
</style>

