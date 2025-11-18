<template>
  <v-card style="border:2px solid green" class="mt-5">
    <v-card-text class="pa-2">
      <p class="text-h6 mt-0 mb-5 text-center">Accepted by:</p>
      <div class="d-flex justify-space-between">
        <p>
          <u>{{view_proposal.accepted_by}}</u><br>
          For {{view_proposal.company_name}}
        </p>
        <p>
          <u>{{view_proposal.accepted_date}}</u><br>
          Date
        </p>
      </div>
    </v-card-text>
  </v-card>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals.js";

const store = GlobalStore();
const {view_proposal} = storeToRefs(store);
</script>
