<template>
  <v-icon class="drag-handle">mdi-drag-vertical</v-icon>
  <slot></slot>
</template>
<script setup lang="ts">
</script>

<style scoped>
.drag-handle {
  position: relative;
  left: -10px;
}
</style>
