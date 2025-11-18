<template>
  <my-modal
    max_width="100%"
    :bg_color="theme"
    title="Preview Proposal"
    :no_scroll="true"
    :fullscreen="is_fullscreen"
    :show_toolbar="false"
    v-model="modals.preview_proposal"
    @close="ToggleModal('preview_proposal',false)">
    <template #content>
      <div id="preview-proposal">
        <ViewProposal @fullscreen="SetFullscreen" :preview_hash="proposal.hash_code"/>
      </div>
    </template>
  </my-modal>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import ViewProposal from '@/pages/proposal/[hash].vue';
import {ToggleModal} from "@/composables/GlobalComposables";

const store = GlobalStore();
const is_fullscreen = ref(true);
const {proposal} = storeToRefs(store);
const {modals,frontend_theme} = storeToRefs(store);
const theme = computed(() => {
  return frontend_theme==='light'?'#c3ccdc':'#121212'
});
const SetFullscreen = () => {
  is_fullscreen.value = !is_fullscreen.value;
}
</script>
<style lang="scss">
#preview-proposal {
  .pdf-page * {
    zoom: 0.99 !important
  }
}
</style>
