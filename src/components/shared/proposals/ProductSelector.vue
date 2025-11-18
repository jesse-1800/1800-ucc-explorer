<template>
  <v-card elevation="0">
    <v-card-text class="pb-0 pt-0">
      <!--Filter Switcher-->
      <div class="ma-auto d-flex align-center mb-10" style="width:fit-content">
        <span class="mr-2">Ask OSCAR</span>
        <v-switch v-model="proposal_active_filter" :false-value="1" :true-value="2" base-color="info" color="info" hide-details/>
        <span class="ml-2" style="width:90px">Filters</span>
      </div>

      <!--AI Filter-->
      <v-row v-if="proposal_active_filter==1" class="animate-moveFromLeft">
        <v-col cols="12" lg="6" md="6" sm="12">
          <FilterLlmForm/>
        </v-col>
        <v-col cols="12" lg="6" md="6" sm="12">
          <FilterResultsTable :filtered_products="ai_filtered_products"/>
        </v-col>
      </v-row>

      <!--Manual Filters-->
      <v-row v-if="proposal_active_filter==2" class="animate-moveFromLeft">
        <v-col cols="12" lg="6" md="6" sm="12">
          <FilterInputsForm/>
        </v-col>
        <v-col cols="12" lg="6" md="6" sm="12">
          <FilterResultsTable :filtered_products="manual_filtered_products"/>
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {ai_filtered_products} from "@/composables/ProposalComposable";
import {manual_filtered_products} from "@/composables/ProposalComposable";

// Local states
const store = GlobalStore();
const {proposal_active_filter} = storeToRefs(store);

</script>

