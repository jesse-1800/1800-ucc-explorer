<template>
  <v-card class="border" :style="theme_border_radius">
    <v-card-text>
      <div class="d-block ma-auto" style="width:fit-content">
        <div class="stacked-images">
          <!--Base Image-->
          <img class="base-image" :src="GetBaseImage(product)" v-if="IsBuilding && HasBaseImage" alt=""/>

          <!--Featured Image-->
          <img class="featured-image" :src="GetProductImage(product)" v-else alt=""/>

          <!--Accessories-->
          <template v-for="(accessory, key) in attachments" :key="key">
            <img
              :style="CustomAccessoryCss(accessory,key)"
              v-if="GetDatasetObject(accessory.dataset_id).filename.length"
              :src="GetDatasetImage(accessory) as string" alt=""
              class="img-stack animate-fade-in">
          </template>
        </div>
      </div>
    </v-card-text>
  </v-card>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {GetBaseImage} from "@/composables/ProductComposable";
import {GetDatasetImage} from "@/composables/ProductComposable";
import {GetProductImage} from "@/composables/ProductComposable";
import {GetDatasetObject} from "@/composables/ProductComposable";
import {theme_border_radius} from "@/composables/GlobalComposables.ts";

// Prop Types
type ImgStackPropTypes = {
  product: object,
  attachments: any[]
}

// Pinia states
const store = GlobalStore();
const {is_mobile} = storeToRefs(store);
const {product,attachments} = defineProps<ImgStackPropTypes>();

// Computed properties
const Dimensions = computed(() => is_mobile.value ? 250 : 500);
const IsBuilding = computed(() => attachments.length > 0);
const HasBaseImage = computed(() => {
  return GetBaseImage(product).length > 0 && !GetBaseImage(product).includes('_placeholder_.png')
})

const CustomAccessoryCss = (accessory:any,z_index:number) => {
  let csstxt = '';
  const rules = JSON.parse(GetDatasetObject(accessory.dataset_id).rules);
  rules.forEach((rule:any) => {
    if (rule.type === 'custom_css') {
      let is_condition_met = 0;
      rule.items.forEach((dataset_id:number) => {
        const find_item = attachments.find(a => a.dataset_id === dataset_id && a.dataset_attached);
        if (typeof find_item !== 'undefined') {
          is_condition_met++;
        }
      });
      if (is_condition_met === rule.items.length && rule.items.length > 0) {
        csstxt = rule.css;
      }
    }
  });
  return `z-index:${z_index};`+csstxt;
}
</script>

<style lang="scss" scoped>
.stacked-images {
  transform-origin: center;
  transition: transform 0.3s ease;
  width: fit-content;
  height: fit-content;
  margin: auto;
  position: relative;
  user-select: none;
  -webkit-user-drag: none;
  user-drag: none;
  img {
    width: 100%;
    height: 100%;
  }
  img.img-stack {
    position: absolute;
    object-fit: contain;
    top: 0;
    left: 0;
  }
}
</style>
