<template>
  <AppLayout>
    <template #title>Homepage</template>
    <template #content>
      <BuyersInteractiveMap/>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>

import BuyersInteractiveMap from "@/components/shared/buyers/BuyersInteractiveMap.vue";
</script>
