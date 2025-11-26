<template>
  <AppLayout>
    <template #title>UCC Filings</template>
    <template #content>
      <v-data-table :style="theme_table_style" :items="[]" :headers="headers">

      </v-data-table>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {theme_table_style} from "@/composables/GlobalComposables.ts";

const headers = <any>[
  {title: "UCC ID", value: "id", sortable: true},
  {title: "Company", value: "company", sortable: false},
]
const store = GlobalStore();
</script>
