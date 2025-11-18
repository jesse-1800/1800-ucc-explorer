<template>
  <template v-if="proposal_accepted">
    <v-btn rounded="pill" color="primary" readonly variant="tonal" prepend-icon="mdi-check-all">Accepted</v-btn>
    <v-btn rounded="pill" :href="`/proposals/download/${view_proposal.hash_code}`" class="ml-2" color="#c83c45" target="_blank" prepend-icon="mdi-file-pdf-box">Download</v-btn>
  </template>
  <v-btn rounded="pill" v-else-if="proposal_declined" color="red" readonly variant="tonal" prepend-icon="mdi-close-octagon-outline">Proposal Declined</v-btn>
  <v-btn-group v-else class="footer-btns text-white" :style="`background:${get_brand_color}`">
    <v-btn prepend-icon="mdi-file-sign" :loading="global_loading" color="transparent" @click="$emit('accept')">
      Accept
    </v-btn>
    <!-- Dropdown Arrow Button -->
    <v-menu offset-y>
      <template #activator="{ props }">
        <v-btn v-bind="props" icon style="background:none">
          <v-icon class="text-white">mdi-chevron-down</v-icon>
        </v-btn>
      </template>

      <v-list>
        <v-list-item prepend-icon="mdi-close-circle" @click="emit('decline')">
          <v-list-item-title>Decline</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-menu>
  </v-btn-group>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {cm8_gradient, get_brand_color, theme_btn_style} from "@/composables/GlobalComposables.js";

const store = GlobalStore();
const emit = defineEmits(['accept', 'decline']);
const {view_proposal,global_loading} = storeToRefs(store);
const proposal_accepted = computed(() => {
  return view_proposal.value?.status==='accepted';
});
const proposal_declined = computed(() => {
  return view_proposal.value?.status==='declined';
});
</script>
