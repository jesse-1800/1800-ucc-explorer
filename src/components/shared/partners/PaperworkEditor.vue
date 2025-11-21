<template>
  <MyModal :card_padding="0" density="compact" v-model="modals.paperwork_editor" title="Paperwork Editor" :fullscreen="fullscreen" @close="PromptSave">
    <template #append-toolbar-icon>
      <v-btn v-if="is_proposal" dark @click="RestorePaperwork">Restore Paperwork</v-btn>
      <v-btn icon='mdi-fullscreen' v-tooltip="'Fullscreen'" dark @click="fullscreen=!fullscreen"/>
      <v-btn icon='mdi-floppy' v-tooltip="'Save & Close'" :loading="is_loading" dark @click="CloseAndSave"/>
    </template>
    <GrapeJS/>

    <FloatingBtns
      :hide_save="true"
      :hide_dist_btn="true">
    </FloatingBtns>
  </MyModal>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {UccServer} from "@/plugins/ucc-server.ts";
import {ToggleModal} from "@/composables/GlobalComposables";
import {my_providers} from "@/composables/GlobalComposables";
import FloatingBtns from "@/components/shared/templates/floating-btns.vue";

const store = GlobalStore();
const is_loading = ref(false);
const fullscreen = ref(false);
const are_values_set = ref(false);
const emit = defineEmits(['update']);
const {getAccessTokenSilently} = useAuth0();
const {modals,proposal,is_gjs_loaded} = storeToRefs(store);
const {provider,is_proposal} = defineProps(['provider','is_proposal']);

const PromptSave = async () => {
  if (is_proposal) {
    CloseAndSave();
  } else {
    const prompt = await store.OpenDialog("Confirm Action", "Do you want to save your changes to this Paperwork?","Yes", "No");
    if (prompt) {
      CloseAndSave();
    } else {
      ToggleModal('paperwork_editor', false);
    }
  }
}
const CloseAndSave = async () => {
  // Means we're editing a proposal's copy
  if (is_proposal) {
    proposal.value.paperwork_css = (window as any).gjs_instance.getCss();
    proposal.value.paperwork_html = (window as any).gjs_instance.getHtml();
    ToggleModal('paperwork_editor', false);
  }
  else {
    // Means it's a provider direct edit
    is_loading.value = true;
    const form = new FormData;
    const token = await getAccessTokenSilently();
    form.append('provider_id', provider.id);
    form.append('paperwork_css', (window as any).gjs_instance.getCss());
    form.append('paperwork_html', (window as any).gjs_instance.getHtml());
    UccServer(token).post('/providers/update-paperwork',form).then(res => {
      console.log(res.data);
      store.ShowSuccess("Your Paperwork has been updated.");
    }).finally(() => {
      store.FetchProviders(token);
      is_loading.value = false;
      are_values_set.value = false;
      ToggleModal('paperwork_editor', false);
    });

  }
}
const RestorePaperwork = async () => {
  const is_confirmed = await store.OpenDialog("Confirm action", "This will replace your Proposal's paperwork with the original Paperwork. Continue?")
  const provider_id = proposal.value.lease_factor_provider;
  if (is_confirmed) {
    const orig_provider = my_providers.value.find(p=>p.id== provider_id.id);
    if (orig_provider) {
      // Set the proposals vars
      proposal.value.paperwork_css = orig_provider.paperwork_css;
      proposal.value.paperwork_html = orig_provider.paperwork_html;
      // Update the Editor components
      (window as any).gjs_instance.setStyle(orig_provider.paperwork_css);
      (window as any).gjs_instance.setComponents(orig_provider.paperwork_html);
      store.ShowSuccess("Paperwork reset to default.");
      ToggleModal('paperwork_editor', false);
      (window as any).gjs_instance.destroy();
    } else {
      store.ShowError("Original Paperwork not found.");
    }
  }
}

// Watch when GJS is instantiated
watch(() => is_gjs_loaded.value,(loaded) => {
  if (loaded) {
    if (is_proposal) {
      (window as any).gjs_instance.setStyle(proposal.value.paperwork_css);
      (window as any).gjs_instance.setComponents(proposal.value.paperwork_html);
    } else {
      (window as any).gjs_instance.setStyle(provider.paperwork_css);
      (window as any).gjs_instance.setComponents(provider.paperwork_html);
    }
  }
});

onUnmounted(() => {
  is_gjs_loaded.value = false;
  console.log("Grapesjs Instance Destroyed");
  setTimeout(() => {
    (window as any).gjs_instance.destroy();
  },1000)
})
</script>
