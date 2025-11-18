<template>
  <!--Metics Modal-->
  <MyModal max_width="700" color="transparent" v-model="modals.metrics_modal" :title="get_title">
    <div class="text-center" v-if="!metric?.page_data.length">
      <div>
        <v-icon size="150">mdi-emoticon-sad-outline</v-icon>
        <div>There's not enough data so far.</div>
      </div>
    </div>
    <v-data-table :style="theme_table_style" :items="metric?.page_data" v-else :headers="headers">
      <template #item.views="{ item }">
        <v-chip>{{item.views}}</v-chip>
      </template>
      <template #item.seconds="{ item }">
        {{ FormatSeconds(item.seconds) }}
      </template>
    </v-data-table>
    <template #footer>
      <v-spacer/>
      <v-btn :style="theme_btn_style" @click="modals.metrics_modal=false">Done</v-btn>
    </template>
  </MyModal>
</template>
<script setup lang="ts">
import moment from "moment";
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {FindMetrics} from "@/composables/ProposalComposable";
import {theme_btn_style, theme_table_style} from "@/composables/GlobalComposables";

const store = GlobalStore();
const props = defineProps(['proposal_id']);
const {modals,proposals} = storeToRefs(store);
const headers = [
  { sortable: true, title: 'Page Title', value: 'title' },
  { sortable: true, title: 'Views', value: 'views' },
  { sortable: true, title: 'Time Spent', value: 'seconds' },
]
const get_title = computed(() => {
  return the_proposal.value ? `Metrics for Proposal ID #${the_proposal.value.id}` : 'Metrics';
});
const metric = computed(() => {
  return FindMetrics(props.proposal_id);
});
const the_proposal = computed(() => {
  return proposals.value.find((p:any) => p.id === props.proposal_id);
});

const  FormatSeconds = (seconds: number) => {
  const duration = moment.duration(seconds, 'seconds');
  if (seconds < 60) {
    return `${seconds} seconds`;
  } else if (seconds < 3600) {
    return `${duration.minutes()} minute${duration.minutes() !== 1 ? 's' : ''} ${duration.seconds()} second${duration.seconds() !== 1 ? 's' : ''}`;
  } else {
    return `${duration.hours()} hour${duration.hours() !== 1 ? 's' : ''} ${duration.minutes()} minute${duration.minutes() !== 1 ? 's' : ''}`;
  }
}
</script>
