<template>
  <AppLayout>
    <template #title>Homepage</template>
    <template #content>
      <squeeze class="mt-md-15" columns="8" offset="2">

        <!--Show the Field Mapping Window-->
        <template v-if="has_pending_tasks">
          <h1 class="mb-5">Pending Task: Complete your import</h1>

          <!--Collapsible Panels-->
          <v-expansion-panels>
            <panel title="Buyers">
              <!--Buyers Mapping-->
              <v-card-text>
                <v-row>
                  <v-col cols="5">
                    <h3 class="font-weight-light mb-5">Buyer Fields</h3>
                    <template v-for="column in ucc_map_columns.buyers">
                      <v-text-field
                        :readonly="true"
                        density="compact"
                        variant="outlined"
                        v-if="column.display"
                        :label="column.label"
                        :placeholder="column.description">
                      </v-text-field>
                    </template>
                  </v-col>
                  <v-col cols="2">
                    <br><br>
                    <template v-for="buyer in ucc_map_columns.buyers">
                      <div v-if="buyer.display" class="mt-1 mb-5 d-flex align-center justify-center" style="height:42px">
                        <v-icon>mdi-swap-horizontal</v-icon>
                      </div>
                    </template>
                  </v-col>
                  <v-col cols="5" >
                    <h3 class="font-weight-light mb-5">Map to your CSV Data</h3>
                    <template v-for="column in ucc_map_columns.buyers">
                      <v-combobox
                        density="compact"
                        variant="outlined"
                        v-if="column.display"
                        label="Select a header"
                        :items="target_headers"
                        v-model="column.mapped_to">
                      </v-combobox>
                    </template>
                  </v-col>
                </v-row>
              </v-card-text>
            </panel>

            <panel title="Equipments">
              <v-card-text>
                <v-row>
                  <v-col cols="5">
                    <h3 class="font-weight-light mb-5">Equipment Fields</h3>
                    <v-text-field
                      :readonly="true"
                      density="compact"
                      variant="outlined"
                      :placeholder="column.description"
                      v-for="column in ucc_map_columns.equipments"
                      :label="column.label">
                    </v-text-field>
                  </v-col>
                  <v-col cols="2">
                    <br><br>
                    <template v-for="n in ucc_map_columns.equipments.length">
                      <div class="mt-1 mb-5 d-flex align-center justify-center" style="height:42px">
                        <v-icon>mdi-swap-horizontal</v-icon>
                      </div>
                    </template>
                  </v-col>
                  <v-col cols="5" >
                    <h3 class="font-weight-light mb-5">Map to your CSV data</h3>
                    <template v-for="column in ucc_map_columns.equipments">
                      <v-combobox
                        density="compact"
                        variant="outlined"
                        label="Select a header"
                        :items="target_headers"
                        v-model="column.mapped_to">
                      </v-combobox>
                    </template>
                  </v-col>
                </v-row>
              </v-card-text>
            </panel>

            <panel title="Lenders">
              <v-card-text>
                <v-row>
                  <v-col cols="5">
                    <h3 class="font-weight-light mb-5">Lenders Fields</h3>
                    <v-text-field
                      :readonly="true"
                      density="compact"
                      variant="outlined"
                      :placeholder="column.description"
                      v-for="column in ucc_map_columns.lenders"
                      :label="column.label">
                    </v-text-field>
                  </v-col>
                  <v-col cols="2">
                    <br><br>
                    <template v-for="n in ucc_map_columns.lenders.length">
                      <div class="mt-1 mb-5 d-flex align-center justify-center" style="height:42px">
                        <v-icon>mdi-swap-horizontal</v-icon>
                      </div>
                    </template>
                  </v-col>
                  <v-col cols="5" >
                    <h3 class="font-weight-light mb-5">Map to your CSV data</h3>
                    <template v-for="column in ucc_map_columns.lenders">
                      <v-combobox
                        density="compact"
                        variant="outlined"
                        label="Select a header"
                        :items="target_headers"
                        v-model="column.mapped_to">
                      </v-combobox>
                    </template>
                  </v-col>
                </v-row>
              </v-card-text>
            </panel>

            <panel title="Lessors">
              <v-card-text>
                <v-row>
                  <v-col cols="5">
                    <h3 class="font-weight-light mb-5">Lenders Fields</h3>
                    <v-text-field
                      :readonly="true"
                      density="compact"
                      variant="outlined"
                      :placeholder="column.description"
                      v-for="column in ucc_map_columns.lessors"
                      :label="column.label">
                    </v-text-field>
                  </v-col>
                  <v-col cols="2">
                    <br><br>
                    <template v-for="n in ucc_map_columns.lessors.length">
                      <div class="mt-1 mb-5 d-flex align-center justify-center" style="height:42px">
                        <v-icon>mdi-swap-horizontal</v-icon>
                      </div>
                    </template>
                  </v-col>
                  <v-col cols="5" >
                    <h3 class="font-weight-light mb-5">Map to your CSV data</h3>
                    <template v-for="column in ucc_map_columns.lessors">
                      <v-combobox
                        density="compact"
                        variant="outlined"
                        label="Select a header"
                        :items="target_headers"
                        v-model="column.mapped_to">
                      </v-combobox>
                    </template>
                  </v-col>
                </v-row>
              </v-card-text>
            </panel>
          </v-expansion-panels>

          <div class="text-center mt-5">
            <v-btn prepend-icon="mdi-import" :style="theme_btn_style">Ready to Import</v-btn>
          </div>
        </template>

        <!--Show the Uploader Window-->
        <template v-else>
          <h3 class="font-weight-light">Upload your UCC filings in CSV format. After uploading, you will be able to map your CSV columns to the required fields. This helps ensure accurate data import and reduces formatting issues.</h3>
          <v-sheet
            class="d-flex flex-column align-center justify-center pa-8 mt-8"
            elevation="0"
            height="250"
            rounded="lg"
            @dragover.prevent
            @click="TriggerFileInput"
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
              <td>
                <a :href="`files/download/${item.id}`">
                  {{item.name}}
                </a>
              </td>
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
import {my_user_id} from "@/composables/GlobalComposables";
import {SluggifyText} from "@/composables/GlobalComposables";
import {my_partner_id} from "@/composables/GlobalComposables";
import {my_company_name} from "@/composables/GlobalComposables";
import {theme_btn_style,} from "@/composables/GlobalComposables";
import {theme_table_style} from "@/composables/GlobalComposables";

const store = GlobalStore();
const sample_values = ref([]);
const target_headers = ref([]);
const headers = [
  {title:"ID",       value: "id",  sortable:true},
  {title:"File name",value: "name",sortable:true},
  {title:"Status",   value: "is_imported", sortable:true},
];
const file_input = ref<any>(null);
const {getAccessTokenSilently} = useAuth0();
const has_pending_tasks = computed(() => {
  return ucc_files.value.find(f => f.is_imported == 0);
});
const {ucc_files,ucc_map_columns,is_data_loaded} = storeToRefs(store);

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

  UccServer(token).post("/import/store",form).then(()=>{
    store.ShowSuccess("File has been uploaded!")
    store.FetchFiles(token);
  });
}
const ParseContents = async() => {
  const file_id = has_pending_tasks.value.id;
  const token = await getAccessTokenSilently();
  UccServer(token).post(`/files/parse-file/${file_id}`).then(res => {
    console.log(res.data);
    sample_values.value = res.data.sample_data;
    target_headers.value = res.data.headers;
    ucc_map_columns.value.buyers.forEach((buyer: any) => {
      const preselect = res.data.headers.find((h: string) => {
        // Remove BOM and trim whitespace
        const cleanHeader = h.replace(/^\uFEFF/, '').trim();
        return buyer.preselect.includes(cleanHeader);
      });
      if (preselect) {
        buyer.mapped_to = preselect;
      }
      if (buyer.column == "buyid") {
        console.log("preselect: ", preselect);
      }
    });
  });
}

// Watcher to check if there's pending task.
watch(()=>has_pending_tasks.value,(new_val:boolean) => {
  if (new_val) {
    console.log("Fetch ran..")
    ParseContents();
  }
},{immediate:true});
</script>
