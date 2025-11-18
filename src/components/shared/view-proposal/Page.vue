<template>
  <v-card :style="cover_css" class="bg-white border proposal-page ma-auto mb-5" elevation="0">
    <v-card-title :style="header_css" v-if="!blank" class="text-left text-white">
      <div>{{title}}</div>
    </v-card-title>
    <v-card-text :class="{'pa-0':blank}" class="content text-body-1">
      <slot></slot>
    </v-card-text>
    <v-card-actions v-if="!blank">
      <slot name="footer"/>
    </v-card-actions>
  </v-card>
</template>
<script setup lang="ts">
defineProps({
  title: {
    type: String,
    default: ''
  },
  blank: {
    type: Boolean,
    default: false
  },
  cover_css: null,
});

const header_css = computed(() => {
  return {
    fontSize: '18px !important',
    background: `url(/assets/img/header.png)`,
    backgroundRepeat: 'no-repeat',
    backgroundSize: 'cover',
    backgroundPosition: 'left',
  }
});
</script>

<style lang="scss" scoped>
.proposal-page {
  border-radius: 20px;
  max-width: 900px;
  min-height: 1200px;
  .content {
    padding: 50px;
  }
  @media(max-width: 600px) {
    & {
      min-height: fit-content;
      max-width: 100%;
    }
    .content {
      padding: 20px;
    }
  }
}
</style>
