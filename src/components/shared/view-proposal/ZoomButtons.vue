<template>
  <div class="zoom-buttons">
    <v-btn
      size="small"
      icon="mdi-magnify-plus-outline"
      class="mb-3"
      @click="zoomIn"
      :disabled="zoomLevel >= maxZoom"
    ></v-btn>
    <v-btn
      size="small"
      icon="mdi-magnify-minus-outline"
      @click="zoomOut"
      :disabled="zoomLevel <= minZoom"
    ></v-btn>
    <div class="zoom-level-text mt-2">{{ Math.round(zoomLevel * 100) }}%</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import {GlobalStore} from "@/stores/globals.js";
import {storeToRefs} from "pinia";

const zoomLevel = ref(1.25);
const minZoom = 0.5;
const maxZoom = 1.75;
const zoomStep = 0.25;

const store = GlobalStore();
const {global_loading} = storeToRefs(store);

const applyZoom = () => {
  const pdfPages = document.querySelectorAll('.pdf-page');

  pdfPages.forEach(page => {
    // Reset any previous wrapper
    if (page.parentElement?.classList.contains('pdf-page-zoom-wrapper')) {
      const wrapper = page.parentElement;
      wrapper.parentElement.insertBefore(page, wrapper);
      wrapper.remove();
    }

    // Create wrapper
    const wrapper = document.createElement('div');
    wrapper.className = 'pdf-page-zoom-wrapper';

    // Insert wrapper before the page and move page into wrapper
    page.parentElement.insertBefore(wrapper, page);
    wrapper.appendChild(page);

    // Apply zoom to page
    page.style.transform = `scale(${zoomLevel.value})`;
    page.style.transformOrigin = 'top center';
    page.style.transition = 'transform 0.2s ease';

    // Get the actual dimensions
    const rect = page.getBoundingClientRect();
    const originalHeight = page.offsetHeight;

    // Set wrapper height to match scaled content
    wrapper.style.height = `${originalHeight * zoomLevel.value}px`;
    wrapper.style.transition = 'height 0.2s ease';
  });
};
const zoomIn = () => {
  if (zoomLevel.value < maxZoom) {
    zoomLevel.value = Math.min(zoomLevel.value + zoomStep, maxZoom);
    applyZoom();
  }
};
const zoomOut = () => {
  if (zoomLevel.value > minZoom) {
    zoomLevel.value = Math.max(zoomLevel.value - zoomStep, minZoom);
    applyZoom();
  }
};


onMounted(() => {
  applyZoom();
});

/* Zoom to 1.25x */
watch(() => global_loading.value, () => {
  applyZoom();
},{immediate:true});
</script>

<style scoped>
.zoom-buttons {
  position: fixed;
  bottom: 75px;
  right: 25px;
  z-index: 100;
  display: flex;
  flex-direction: column;
}

.zoom-level-text {
  font-size: 12px;
  text-align: center;
  font-weight: 500;
  user-select: none;
}
</style>

<style>
/* Global styles for zoom wrapper */
.pdf-page-zoom-wrapper {
  overflow: visible;
  display: flex;
  justify-content: center;
  margin-bottom: 50px;
}
</style>
