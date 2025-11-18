<template>
  <v-expansion-panels :model-value="0">
    <panel class="border" :style="theme_table_style" title="Proposal Defaults" icon="mdi-file-document-edit-outline">
      <squeeze columns="6" offset="3">
        <div class="py-10">
          <template v-for="item in my_options">
            <v-text-field :label="item.name" variant="outlined" v-model="item.content"/>
          </template>
        </div>
      </squeeze>
      <template #footer>
        <v-btn
          color="info"
          class="ma-auto"
          text="Save Changes"
          @click="SaveChanges"
          :style="theme_btn_style"
          :loading="is_options_loading">
        </v-btn>
      </template>
    </panel>
  </v-expansion-panels>
</template>

<script lang="ts" setup>
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {ProposalServer} from "@/plugins/proposal-server";
import {my_options} from "@/composables/GlobalComposables";
import {theme_btn_style} from "@/composables/GlobalComposables";
import {theme_table_style} from "@/composables/GlobalComposables";

const store = GlobalStore();
const is_options_loading = ref(false);
const {getAccessTokenSilently} = useAuth0();

const SaveChanges = async () => {
  const token = await getAccessTokenSilently();
  is_options_loading.value = true;
  const form = new FormData;
  form.append('options', JSON.stringify(my_options.value));
  ProposalServer(token).post("/options/update",form).then(res=>{
    console.log(res.data);
    store.ShowSuccess("Options updated successfully");
  }).finally(()=>{
    is_options_loading.value = false;
  });
}

onMounted(async () => {
  const token = await getAccessTokenSilently();
  store.FetchOptions(token);
});
</script>
