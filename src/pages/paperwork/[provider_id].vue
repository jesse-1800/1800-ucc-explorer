<template>
  <AppLayout>
    <template #title>
      Edit Paperwork {{ provider ? `for ${provider.name}` : '' }}
    </template>
    <template #content>
      <GrapeJS/>

      <!--Floating Buttons-->
      <FloatingBtns :loading="is_loading" @onsave="SaveChanges" v-if="hide_layout"/>
    </template>

    <!--Footer-->
    <template v-if="!hide_layout" #footer>
      <v-btn
        :style="theme_btn_style"
        prepend-icon="mdi-overscan"
        text="Distraction-Free Mode"
        @click="hide_layout=!hide_layout">
      </v-btn>

      <v-spacer/>

      <v-btn
        color="primary"
        text="Save Changes"
        @click="SaveChanges"
        :loading="is_loading"
        class="d-flex ml-auto"
        :style="theme_btn_style"
        prepend-icon="mdi-floppy">
      </v-btn>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useRoute, useRouter} from "vue-router";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {ProposalServer} from "@/plugins/proposal-server";
import {my_providers} from "@/composables/GlobalComposables";
import {theme_btn_style} from "@/composables/GlobalComposables";

const skip_reinstate = ref(false);
const route = <any>useRoute();
const provider = <any>ref(null);
const router = <any>useRouter();
const store = GlobalStore();
const is_loading = ref(false);
const emit = defineEmits(['update']);
const {getAccessTokenSilently} = useAuth0();
const {is_data_loaded, is_gjs_loaded, hide_layout} = storeToRefs(store);

const SaveChanges = async () => {
  is_loading.value = true;
  const form = new FormData;
  const token = await getAccessTokenSilently();
  form.append('provider_id', provider.value.id);
  form.append('paperwork_css', (window as any).gjs_instance.getCss());
  form.append('paperwork_html', (window as any).gjs_instance.getHtml());
  ProposalServer(token).post('/providers/update-paperwork', form).then(res => {
    console.log(res.data);
    store.ShowSuccess("Your Paperwork has been updated.");
  }).finally(() => {
    store.FetchProviders(token);
    is_loading.value = false;
  });
}

// Watch when GJS is instantiated
watchEffect(() => {
  if (is_data_loaded.value && is_gjs_loaded.value) {

    // We will only call setComponents once.
    if (!skip_reinstate.value) {
      provider.value = my_providers.value.find((p: any) => {
        return p.id == route.params.provider_id
      });
      if (provider.value) {
        (window as any).gjs_instance.setStyle(provider.value.paperwork_css,{});
        (window as any).gjs_instance.setComponents(provider.value.paperwork_html);
        skip_reinstate.value = true;
      } else {
        store.ShowError("This Paperwork doesn't exist. Redirecting to Providers page...")
        setTimeout(() => {
          router.push('/providers');
        }, 3000);
      }
    }
  }
})

onUnmounted(() => {
  (window as any).gjs_instance.destroy();
  hide_layout.value = false;
});
</script>
