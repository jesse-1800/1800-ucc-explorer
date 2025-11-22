<template>
  <AppLayout>
    <template #title>Homepage</template>
    <template #content>
      <squeeze class="mt-md-15" columns="8" offset="2">

        <!--Show the Field Mapping Window-->
        <template v-if="has_pending_tasks">
          <h1 class="mb-5">Pending Task: Complete your import</h1>

          <!--Buyers table-->
          <v-row>
            <v-col cols="6">
              <v-text-field
                density="compact"
                variant="outlined"
                :placeholder="column.description"
                v-for="column in ucc_map_columns.buyers"
                :label="column.label">
              </v-text-field>
            </v-col>
            <v-col cols="6" >
              <template v-for="column in ucc_map_columns.buyers">
                <v-select
                  density="compact"
                  variant="outlined"
                  :items="['item 1', 'item 2', 'item 3']"
                  v-model="column.mapped_to">
                </v-select>
              </template>
            </v-col>
          </v-row>
        </template>

        <!--Show the Uploader Window-->
        <template v-else>
          <h3 class="font-weight-light">Upload your UCC filings in CSV format. After uploading, you will be able to map your CSV columns to the required fields. This helps ensure accurate data import and reduces formatting issues.</h3>
          <v-sheet
            class="d-flex flex-column align-center justify-center pa-8 mt-8"
            elevation="0"
            height="250"
            rounded="lg"
            @click="TriggerFileInput"
            @dragover.prevent
            @drop.prevent="HandleDrop"
            style="background:transparent;border: 2px dashed #9e9e9e; cursor: pointer;">
            <v-icon size="56" color="grey">mdi-upload</v-icon>
            <div class="text-grey mt-3">Drag & drop CSV file here or click to upload</div>
            <input
              type="file"
              ref="file_input"
              class="d-none"
              accept=".csv"
              @change="HandleFileSelect"
            />
          </v-sheet>
        </template>

        <v-data-table class="mt-5" :style="theme_table_style" :items="ucc_files" :headers="headers">
          <template #item="{item}">
            <tr>
              <td>{{item.id}}</td>
              <td>{{item.name}}</td>
              <td>
                <span v-if="item.is_imported" class="text-green">
                  <v-icon size="small">mdi-check-circle-outline</v-icon>
                  <span class="ml-2">Completed</span>
                </span>
                <span v-else class="text-orange">
                  <v-icon size="small">mdi-progress-clock</v-icon>
                  <span class="ml-2">In-progress</span>
                </span>
              </td>
            </tr>
          </template>
        </v-data-table>

      </squeeze>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {UccServer} from "@/plugins/ucc-server";
import {my_user_id, } from "@/composables/GlobalComposables";
import {SluggifyText} from "@/composables/GlobalComposables";
import {my_partner_id} from "@/composables/GlobalComposables";
import {my_company_name} from "@/composables/GlobalComposables";
import {theme_table_style} from "@/composables/GlobalComposables";

const store = GlobalStore();
const target_columns = ref([]);
const headers = [
  {title:"ID",       value: "id",  sortable:true},
  {title:"File name",value: "name",sortable:true},
  {title:"Status",   value: "is_imported", sortable:true},
];
const file_input = ref<any>(null);
const {getAccessTokenSilently} = useAuth0();
const {ucc_files,ucc_map_columns} = storeToRefs(store);
const has_pending_tasks = computed(() => {
  return ucc_files.value.some(f => f.is_imported == 0);
});

const TriggerFileInput = () => {
  file_input.value?.click()
}
const HandleFileSelect = (event:any) => {
  const selected_file = event.target.files[0]
  if (selected_file) ProcessFile(selected_file)
}
const HandleDrop = (event:any) => {
  const dropped_file = event.dataTransfer.files[0]
  if (dropped_file) ProcessFile(dropped_file)
}
const ProcessFile = async(file_obj:any) => {
  const form = new FormData;
  const token = await getAccessTokenSilently();

  form.append('file', file_obj);
  form.append('user_id', my_user_id.value);
  form.append('partner_id', my_partner_id.value);
  form.append('folder_name', SluggifyText(my_company_name.value));

  UccServer(token).post("/import/store",form).then(res => {
    store.FetchFiles(token);
  });
}
</script>
