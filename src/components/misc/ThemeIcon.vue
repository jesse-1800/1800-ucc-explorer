<template>
  <div class="theme-icon">
    <v-img class="the-icon" :src="icon_url"/>
    <span class="menu-name">
      <slot></slot>
    </span>
    <v-chip size="small" class="the-chip" v-if="chip !== undefined">
      {{chip}}
    </v-chip>
  </div>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const {backend_theme} = storeToRefs(store);
const icon_url = computed(() => {
  if (backend_theme.value ==='light') {
    return `/assets/img/sidebar/dark/${props.icon}.png`;
  }
  return `/assets/img/sidebar/light/${props.icon}.png`;
});

const props = defineProps(['icon','chip']);
</script>
<style lang="scss" scoped>
.theme-icon {
  .the-icon {
    width: 30px;
    height: 30px;
    display: inline-block;
    position: relative;
    top: 3px;
  }
  .menu-name {
    padding-left: 15px;
    position: relative;
    top: -6px;
  }
  .the-chip {
    position: absolute;
    right: 20px;
    top: 11px;
  }
}
</style>
