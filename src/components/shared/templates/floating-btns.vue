<template>
  <div class="floated-btns">
    <v-btn
      v-if="!hide_save"
      size="small"
      icon="mdi-floppy"
      class="rounded-circle"
      :style="theme_btn_style"
      @click="EmitSave"
      :loading="loading"
      v-tooltip="`Save Changes`">
    </v-btn>
    <v-btn
      v-if="!hide_dist_btn"
      size="small"
      icon="mdi-overscan"
      class="rounded-circle"
      :style="theme_btn_style"
      @click="hide_layout=false"
      v-tooltip="`Exit Distraction-Free Mode`">
    </v-btn>
    <v-btn
      size="small"
      icon="mdi-magnify-plus-outline"
      class="rounded-circle"
      :style="theme_btn_style"
      @click="ZoomIn"
      v-tooltip="`Zoom In`">
    </v-btn>
    <v-btn
      size="small"
      icon="mdi-magnify-minus-outline"
      class="rounded-circle"
      :style="theme_btn_style"
      @click="ZoomOut"
      v-tooltip="`Zoom Out`">
    </v-btn>
  </div>
</template>
<script setup lang="ts">
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {theme_btn_style} from "@/composables/GlobalComposables";

const store = GlobalStore();
const zoomLevel = ref(1.25);
const emits = defineEmits(['onsave']);
const {hide_layout} = storeToRefs(store);
const {loading} = defineProps(['loading','hide_save','hide_dist_btn']);

const EmitSave = () => {
  return emits('onsave');
}
const UpdateZoom = () => {
  const frame = (window as any).gjs_instance.Canvas.getFrameEl();
  const frameDoc = frame.contentDocument;
  const width = (100 / zoomLevel.value).toFixed(2);

  frameDoc.body.style.transform = `scale(${zoomLevel.value})`;
  frameDoc.body.style.width = `${width}%`;
};
const ZoomIn = () => {
  zoomLevel.value = Math.min(zoomLevel.value + 0.1, 2);
  UpdateZoom();
};
const ZoomOut = () => {
  zoomLevel.value = Math.max(zoomLevel.value - 0.1, 0.5);
  UpdateZoom();
};
</script>
<style scoped>
.floated-btns {
  position: fixed;
  z-index: 99999999;
  top: 0;
  bottom: 0;
  left: 10px;
  display: flex;
  flex-direction: column;
  height: 100%;
  justify-content: center;
  .v-btn {
    margin-top: 5px;
  }
}
</style>
