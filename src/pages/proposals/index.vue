<template>
  <ProposalsTable :selected_proposals="my_proposals"/>
</template>

<script setup>
import {my_proposals} from "@/composables/GlobalComposables";
</script>
