<template>
  <v-row v-if="buyer">
    <v-col cols="12">
      <v-text-field :hide-details="condensed" variant="underlined" label="Company" :model-value="buyer.buyer_company" readonly/>
    </v-col>
    <v-col cols="12">
      <v-text-field :hide-details="condensed" variant="underlined" label="Website" :model-value="buyer.buyer_website" readonly/>
    </v-col>
    <v-col cols="12">
      <v-text-field :hide-details="condensed" variant="underlined" label="Address" :model-value="buyer.buyer_adress1" readonly/>
    </v-col>
    <v-col cols="12" sm="4">
      <v-text-field :hide-details="condensed" variant="underlined" label="City" :model-value="buyer.buyer_city" readonly/>
    </v-col>
    <v-col cols="12" sm="4">
      <v-text-field :hide-details="condensed" variant="underlined" label="State" :model-value="buyer.buyer_state" readonly/>
    </v-col>
    <v-col cols="12" sm="4">
      <v-text-field :hide-details="condensed" variant="underlined" label="ZIP" :model-value="buyer.buyer_zip" readonly/>
    </v-col>
    <v-col cols="12">
      <v-text-field :hide-details="condensed" variant="underlined" label="Phone" :model-value="buyer.buyer_phone" readonly/>
    </v-col>
  </v-row>
</template>

<script setup>
const {buyer} = defineProps(['buyer','condensed'])
</script>
