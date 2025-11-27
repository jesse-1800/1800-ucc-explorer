<template>
  <AppLayout>
    <template #title>All Equipments</template>
    <template #content>
      <EquipmentsTable :equipments="ucc_equipments"/>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const {ucc_equipments} = storeToRefs(store);
</script>
