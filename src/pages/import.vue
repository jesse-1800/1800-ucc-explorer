<template>
  <AppLayout>
    <template #title>Homepage</template>
    <template #content>
      <squeeze class="mt-md-15" columns="8" offset="2">
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
      </squeeze>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {useAuth0} from "@auth0/auth0-vue";
import {UccServer} from "@/plugins/ucc-server";

const file_input = ref(null);
const {getAccessTokenSilently} = useAuth0();

const TriggerFileInput = () => {
  file_input.value?.click()
}
const HandleFileSelect = (event) => {
  const selected_file = event.target.files[0]
  if (selected_file) ProcessFile(selected_file)
}
const HandleDrop = (event) => {
  const dropped_file = event.dataTransfer.files[0]
  if (dropped_file) ProcessFile(dropped_file)
}
const ProcessFile = async(file_obj) => {
  const form = new FormData;
  form.append('file', file_obj);
  const token = await getAccessTokenSilently();
  UccServer(token).post("/import/store",form).then(res => {
    console.log(res.data);
  });
}
</script>
