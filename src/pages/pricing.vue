<template>
  <AppLayout>
    <template #title>Pricing</template>
    <template #content>

      <v-tabs v-model="tab" class="mb-4">
        <v-tab value="tab-1" prepend-icon="mdi-file-document-multiple-outline">Your files</v-tab>
        <v-tab value="tab-2" prepend-icon="mdi-microsoft-excel">Your Pricing Sheet</v-tab>
      </v-tabs>
      <v-window v-model="tab">
        <v-window-item value="tab-1">
          <v-data-table
            class="border"
            density="comfortable"
            :items="my_files"
            :headers="headers"
            :style="theme_table_style"
            :loading="is_loading">
            <template v-slot:item="{item}">
              <tr>
                <td>{{item.id}}</td>
                <td :title="item.name" style="width:250px">
                  <span class="clip-text-1-lines">{{item.name}}</span>
                </td>
                <td>
                  <span v-if="item.is_synced==1">
                    <v-btn
                      size="small"
                      color="green"
                      variant="text"
                      density="compact"
                      prepend-icon="mdi-check-circle">
                      Synced at {{item.synced_at}}
                    </v-btn>
                  </span>
                  <span v-else>
                    <v-btn
                      size="small"
                      variant="text"
                      color="default"
                      density="compact"
                      text="Not Synced Yet"
                      prepend-icon="mdi-close"
                      :loading="is_syncing==item.id">
                    </v-btn>
                  </span>
                </td>
                <td>{{item.created_at}}</td>
                <td class="align-center">
                  <v-btn
                    size="small"
                    text="Sync"
                    variant="outlined"
                    @click="PerformSync(item)"
                    :loading="is_syncing==item.id"
                    prepend-icon="mdi-sync-circle"
                    :style="theme_border_radius"
                    color="primary">
                  </v-btn>
                  <v-btn
                    size="small"
                    class="ml-2"
                    variant="outlined"
                    v-tooltip="'Delete'"
                    text="Delete"
                    color="red"
                    prepend-icon="mdi-trash-can-outline"
                    :style="theme_border_radius"
                    @click="DeleteFile(item)">
                  </v-btn>
                  <v-btn
                    :href="`/storage/uploads/partner-id-${my_partner_id}/${item.name}`"
                    class="ml-2" size="small"
                    variant="outlined"
                    v-tooltip="'Download'"
                    text="Download"
                    :style="theme_border_radius"
                    prepend-icon="mdi-download">
                  </v-btn>
                </td>
              </tr>
            </template>
            <template #footer.prepend>
              <div class="d-flex align-center ml-2 mt-2">
                <v-btn :style="theme_border_radius" size="small" @click="upload_modal=true" variant="tonal" prepend-icon="mdi-tray-arrow-up">Upload Files</v-btn>
                <template v-if="is_admin">
                  <div class="ma-1"></div>
                  <v-btn :style="theme_border_radius" size="small" @click="prompt_modal=true" variant="tonal" prepend-icon="mdi-robot-excited-outline">Edit AI Prompt</v-btn>
                </template>
              </div>
              <v-spacer/>
            </template>
          </v-data-table>
        </v-window-item>
        <v-window-item value="tab-2">
          <v-card-text>

            <!--
              VERY IMPORTANT!
              In order for the backend to modify this sheet during data scrape, each partner sheet need to be shared access to
              the Google Service Worker "sheet-editor-service-account@proposals-pricing-editor.iam.gserviceaccount.com"
            -->

            <iframe v-if="my_excel_sheet_url" :src="my_excel_sheet_url"></iframe>
            <div v-else class="text-center">
              <p class="text-h5">No pricing information available.</p>
            </div>
          </v-card-text>
        </v-window-item>
      </v-window>

      <!--Upload Files Modal-->
      <MyModal v-model="upload_modal" color="transparent" title="Upload Pricing Sheet" max_width="600">
        <v-card-text>
          <v-file-input
            hide-details
            accept=".xlsx"
            prepend-icon=""
            variant="outlined"
            v-model="file_model"
            density="comfortable"
            label="Excel file (XLSX)"
            class="border-opacity-100"
            @update:model-value="SetFileInput"
            prepend-inner-icon="mdi-microsoft-excel">
          </v-file-input>
        </v-card-text>
        <template #footer>
          <v-spacer></v-spacer>
          <v-btn
            text="Upload"
            color="primary"
            @click="UploadFile"
            :loading="is_loading"
            :disabled="!file_input"
            :style="theme_btn_style"
            prepend-icon="mdi-tray-arrow-up">
          </v-btn>
        </template>
      </MyModal>

      <!--System Prompt Modal-->
      <MyModal v-model="prompt_modal" color="transparent" title="Modify System Prompt" max_width="1024">
        <v-card-text>
          <v-textarea style="height:100%" label="AI System Prompt" v-model="system_prompt"/>
        </v-card-text>
        <template #footer>
          <v-spacer/>
          <v-btn
            variant="tonal"
            text="Done Editing"
            :style="theme_btn_style"
            @click="prompt_modal=false"
            prepend-icon="mdi-check-circle">
          </v-btn>
          <v-spacer/>
        </template>
      </MyModal>

    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {ProposalServer} from "@/plugins/proposal-server";
import {
  theme_border_radius, my_excel_sheet_id, my_user_id, theme_btn_style, theme_table_style, is_admin
} from "@/composables/GlobalComposables";
import {my_excel_sheet_url} from "@/composables/GlobalComposables";
import {my_partner_id,my_files} from "@/composables/GlobalComposables";

const tab = ref('tab-1');
const upload_modal = ref(false);
const prompt_modal = ref(false);
const store = GlobalStore();
const file_input = ref(null);
const is_loading = ref(false);
const is_syncing = ref(null);
const file_model = ref(null); // for emptying only
const headers = [
  {title: 'ID',          value: 'id'},
  {title: 'File Name',   value: 'name'},
  {title: 'Synced?',     value: 'is_synced'},
  {title: 'Upload Date', value: 'created_at'},
  {title: 'Manage',      value: 'manage'},
];
const {getAccessTokenSilently} = useAuth0();
const system_prompt = ref(`

  You are a data parser expert. Your task is to extract and return ONLY the numeric value
  closest to an average printer price from the input text.

  Strict Matching Rules:
  1. You will be given a Product Name and/or Model which you will then find in the given Product Data.
  2. Extract only numeric values from the input (e.g., 1199, 899.99).
  3. Your output must be a number only — no currency symbols, text, formatting, or comments.
  4. Return only the highest numeric value that closely resembles a real-world product price in USD.
  5. The output must be raw, valid for direct use with floatval() in PHP.
  6. Always choose the most realistic product price, which is usually the highest valid value.
  7. Ignore unrealistic numbers (e.g., 1753884731) — prices are typically within a reasonable consumer product range.
  8. Only return values that appear to be actual product prices, not just any number.
  10. Price usually have at most two decimal places but sometimes they dont.
  11. If you're asked to find an accessory, they usually cost lower than a product/printer.

  OUTPUT FORMAT:
  - Only the numeric value. Example: 1499.99
  - No quotes, no dollar signs, no explanation.
  - If you don't find anything that resembles a price, return 0.
`);

const SetFileInput = async (event:any) => {
  if (!event) return false;
  const is_valid = event.name.toLowerCase().endsWith('.xlsx') || event.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
  if (is_valid) {
    file_input.value = event;
  } else {
    store.ShowError('Invalid file type. Only Excel (.xlsx) files are supported.')
  }
}
const UploadFile = async () => {
  const token = await getAccessTokenSilently();
  const file = file_input.value;
  is_loading.value = true;
  if (!file) return;

  const form = new FormData();
  form.append('file', file);
  form.append('user_id', my_user_id.value);
  form.append('partner_id', my_partner_id.value);
  ProposalServer(token).post('/files/upload',form,{
    headers: {'Content-Type': 'multipart/form-data'},
  }).then(() => {
    store.ShowSuccess('Upload successful.');
    store.FetchFiles(token);
  }).finally(() => {
    upload_modal.value = false;
    is_loading.value = false;
    file_input.value = null;
  });
};
const DeleteFile = async (item:any) => {
  const is_confirmed = await store.OpenDialog('Confirm action', `Are you sure you want to delete "${item.name}"?`);
  if (is_confirmed) {
    const token = await getAccessTokenSilently();
    ProposalServer(token).post('/files/destroy/'+item.id).then(() => {
      store.ShowSuccess('File deleted.');
      store.FetchFiles(token);
    });
  }
}
const PerformSync = async (file:any) => {
  const form = new FormData();
  const token = await getAccessTokenSilently();

  is_syncing.value = file.id;
  form.append('file_id', file.id);
  form.append('partner_id', my_partner_id.value);
  form.append('spreadsheet_id', my_excel_sheet_id.value);
  form.append('system_prompt', system_prompt.value);

  ProposalServer(token).post('/pricing/perform-sync',form).then((res)=>{
    console.log(res.data)
    store.FetchFiles(token);
    store.ShowSuccess("Price has been synced!")
  }).finally(() => {
    is_syncing.value = null;
  });
}

onMounted(async () => {
  const token = await getAccessTokenSilently();
  store.FetchFiles(token);
});
</script>
<style scoped>
iframe {
  width: 100%;
  height: 100vh;
}
</style>
