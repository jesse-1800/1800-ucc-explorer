<template>
  <my-modal
    max_width="500px"
    color="transparent"
    :hide_close_btn="true"
    title="Select Proposal Category"
    v-model="modals.proposal_category"
    @close="ToggleModal('preview_proposal',false)">
    <template #content>
      <v-row>
        <v-col v-for="category in proposal_categories" cols="12" lg="6" md="6" sm="12">
          <v-card :style="theme_border_radius" variant="tonal" @click="proposal.category=category.value" :color="IsActive(category.value) ? 'info':''" class="d-flex flex-column align-center">
            <v-icon size="120">{{category.icon}}</v-icon>
            <p class="my-5">{{category.title}}</p>
          </v-card>
        </v-col>
      </v-row>
    </template>
    <template #footer>
      <v-spacer/>
      <v-btn
        color="primary"
        text="Continue"
        append-icon="mdi-play"
        :style="theme_btn_style"
        @click="ToggleModal('proposal_category',false)">
      </v-btn>
      <v-spacer/>
    </template>
  </my-modal>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {proposal_categories} from "@/composables/ProposalComposable";
import {theme_border_radius, theme_btn_style, ToggleModal} from "@/composables/GlobalComposables";

const store = GlobalStore();
const {SetState,ShowSuccess} = store;
const {proposal} = storeToRefs(store);
const {modals} = storeToRefs(store);

const IsActive = (category_value)  => {
  return proposal.value.category === category_value;
}
</script>
