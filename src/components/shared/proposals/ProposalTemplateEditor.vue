<template>
  <MyModal :card_padding="0" density="compact" title="Template Editor" :fullscreen="fullscreen" :model-value="modals.proposal_template" @close="CloseAndSave">
    <template #append-toolbar-icon>
      <v-btn dark @click="RestoreTemplate">Restore Template</v-btn>
      <v-btn icon='mdi-fullscreen' v-tooltip="'Fullscreen'" dark @click="fullscreen=!fullscreen"/>
      <v-btn icon='mdi-floppy' v-tooltip="'Save & Close'" dark @click="CloseAndSave"/>
    </template>
    <GrapeJS v-if="modals.proposal_template"/>
    <FloatingBtns
      :hide_save="true"
      :hide_dist_btn="true">
    </FloatingBtns>
  </MyModal>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {ToggleModal} from "@/composables/GlobalComposables";
import {my_templates} from "@/composables/GlobalComposables";
import FloatingBtns from "@/components/shared/templates/floating-btns.vue";

const store = GlobalStore();
const fullscreen = ref(false);
const emit = defineEmits(['update']);
const {modals,proposal,is_gjs_loaded} = storeToRefs(store);

const RestoreTemplate = async () => {
  const is_confirmed = await store.OpenDialog("Confirm action", "This will replace your current proposal template with the original template content. Continue?")
  if (is_confirmed) {
    const orig_template = my_templates.value.find(t=>t.id==proposal.value.template_id);
    if (orig_template) {
      console.log("Resetting to original template",orig_template);
      proposal.value.template_css = orig_template.css_content;
      proposal.value.template_html = orig_template.html_content;
      (window as any).gjs_instance.setStyle(orig_template.css_content);
      (window as any).gjs_instance.setComponents(orig_template.html_content);
      store.ShowSuccess("Template reset to default.");
      ToggleModal('proposal_template', false);

      // If it's an existing proposal, save the changes
      if (proposal.value.id) {
        emit('update');
      }
    } else {
      store.ShowError("Original template not found.");
    }
  }
}
const CloseAndSave = () => {
  // Save the html/css first
  proposal.value.template_css = (window as any).gjs_instance.getCss();
  proposal.value.template_html = (window as any).gjs_instance.getHtml();

  // Resets
  ToggleModal('proposal_template', false);
}

watch(() => is_gjs_loaded.value,(loaded)=>{
  if (loaded) {
    (window as any).gjs_instance.setStyle(proposal.value.template_css);
    (window as any).gjs_instance.setComponents(proposal.value.template_html);
  }
});

onUnmounted(() => {
  is_gjs_loaded.value = false;
  (window as any).gjs_instance.destroy();
  console.log("ProposalTemplateEditor.vue: GJS Unloaded.");
});
</script>
