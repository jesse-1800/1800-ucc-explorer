<template>
  <AppLayout>
    <template #title>Assignees</template>
    <template #content>

    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const {ucc_equipments} = storeToRefs(store);
</script>
