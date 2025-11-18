<template>
  <div class="d-flex justify-space-between mb-5">
    <div>
      <v-icon>mdi-information-outline</v-icon>
      Use the Filters below to narrow down your search
    </div>
    <div>
      <v-btn @click="ResetFilters" prepend-icon="mdi-restore" density="compact" variant="text">Reset Filters</v-btn>
    </div>
  </div>
  <v-text-field
    prepend-inner-icon="mdi-magnify"
    v-model="proposal_filters.search_filter"
    class="position-relative"
    variant="outlined"
    placeholder="Search for products..."
    density="comfortable">
  </v-text-field>
  <v-select
    v-model.number="proposal_filters.manufacturer_id"
    :items="my_manufacturers"
    label="Select a Brand"
    density="comfortable"
    variant="outlined"
    item-title="name"
    item-value="id">
  </v-select>
  <v-row>
    <v-col cols="12" lg="6" md="6" sm="12">
      <v-select
        label="Color"
        variant="outlined"
        v-model="proposal_filters.filter_color"
        :items="color_filter_items"
        density="comfortable">
      </v-select>
    </v-col>
    <v-col cols="12" lg="6" md="6" sm="12">
      <v-select
        v-model="proposal_filters.paper_sizes"
        :items="paper_sizes"
        item-title="name"
        item-value="name"
        density="comfortable"
        label="Paper Sizes"
        variant="outlined"
        multiple
        chips>
      </v-select>
    </v-col>
  </v-row>
  <v-select
    v-model="proposal_filters.connectivity"
    :items="connect_filter_items"
    item-title="name"
    item-value="name"
    density="comfortable"
    label="Connectivity"
    variant="outlined"
    multiple
    chips>
  </v-select>
  <v-row>
    <v-col cols="12" lg="6" md="6" sm="12">
      <v-text-field
        v-model.number="proposal_filters.print_volume_min"
        label="Recommended Monthly Prints (Min)"
        density="comfortable"
        variant="outlined">
      </v-text-field>
    </v-col>
    <v-col cols="12" lg="6" md="6" sm="12">
      <v-text-field
        v-model.number="proposal_filters.print_volume_max"
        label="Recommended Monthly Prints (Max)"
        density="comfortable"
        variant="outlined">
      </v-text-field>
    </v-col>
  </v-row>
  <v-text-field
    v-model="proposal_filters.print_speed_color"
    label="Print Speed (Color)"
    variant="outlined"
    density="comfortable">
  </v-text-field>
  <v-text-field
    v-model="proposal_filters.print_speed_black"
    label="Print Speed (B&W)"
    variant="outlined"
    density="comfortable">
  </v-text-field>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {my_manufacturers} from "@/composables/GlobalComposables";
import {color_filter_items} from "@/composables/ProposalComposable";
import {connect_filter_items} from "@/composables/ProposalComposable";

const store = GlobalStore();
const {proposal_filters,paper_sizes} = storeToRefs(store);
const ResetFilters = () => {
  proposal_filters.value = {
    manufacturer_id:   null,
    filter_color:      null,
    connectivity:      [  ],
    print_volume_min:  null,
    print_volume_max:  null,
    print_speed_color: null,
    print_speed_black: null,
    paper_sizes:         [],
    search_filter:     (''),
  }
}
</script>
