<template>
  <AppLayout>
    <template #title>Templates</template>
    <template #content>
      <GrapeJS/>

      <!--Floating Buttons-->
      <FloatingBtns :loading="is_loading" @onsave="SaveTemplate" v-if="hide_layout"/>
    </template>

    <!--Footer-->
    <template v-if="!hide_layout" #footer>
      <v-text-field
        class="mt-1"
        hide-details
        :type="'text'"
        max-width="250"
        density="compact"
        variant="outlined"
        label="Template name"
        v-model="template.name">
      </v-text-field>

      <v-spacer/>

      <v-btn
        :style="theme_btn_style"
        prepend-icon="mdi-overscan"
        text="Distraction-Free Mode"
        @click="hide_layout=!hide_layout">
      </v-btn>

      <v-spacer/>

      <v-switch
        hide-details
        class="mr-3"
        color="info"
        label="Autosave"
        v-if="template.id"
        v-model="autosave">
      </v-switch>
      <v-btn
        color="primary"
        text="Save Template"
        :loading="is_loading"
        @click="SaveTemplate"
        :style="theme_btn_style"
        class="d-flex ml-auto">
      </v-btn>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import html2canvas from 'html2canvas';
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {useRoute, useRouter} from "vue-router";
import type {TemplateType} from "@/types/StoreTypes";
import {ProposalServer} from "@/plugins/proposal-server";
import {my_user_id, theme_btn_style} from "@/composables/GlobalComposables";
import {my_partner_id} from "@/composables/GlobalComposables";
import FloatingBtns from "@/components/shared/templates/floating-btns.vue";

const router = useRouter();
const autosave = ref(false);
const store = GlobalStore();
const route = <any>useRoute();
const is_loading = ref(false);
const autosave_interval = ref<any>(null);
const {getAccessTokenSilently} = useAuth0();
const template = ref(<TemplateType>{
  id: null,
  user_id: null,
  partner_id: null,
  name: 'My Template',
  html_content: '',
  css_content: '',
  created_at: '',
  updated_at: '',
});
const {templates,hide_layout,is_data_loaded} = storeToRefs(store);

const SaveTemplate = async() => {
  const form = new FormData;
  const newTemplate = {...template.value}
  const token = await getAccessTokenSilently();
  const route = template.value.id? '/templates/update':'/templates/store';

  is_loading.value = true;
  newTemplate.html_content = (window as any).gjs_instance.getHtml();
  newTemplate.css_content = (window as any).gjs_instance.getCss();

  if (!template.value.id) {
    newTemplate.name = prompt("Enter a template name",template.value.name);
  }

  newTemplate.user_id = my_user_id.value;
  newTemplate.partner_id = my_partner_id.value;
  newTemplate.thumbnail = await <any>CaptureThumbnail();

  form.append('template',JSON.stringify(newTemplate));
  ProposalServer(token).post(route,form).then((res) => {
    console.log(res.data)
    if (!template.value.id) {
      router.push('/templates');
    }
    store.ShowSuccess('Template saved successfully!');
    console.log("Saved template successfully!");
  }).finally(() => {
    is_loading.value = false;
  });
}
const CaptureThumbnail = async () => {
  const frame = (window as any).gjs_instance.Canvas.getFrameEl();
  const innerDoc = frame.contentDocument || frame.contentWindow.document;
  const firstPage = innerDoc.querySelector('.pdf-page')
  if (!firstPage) return null

  const quality = 0.5;
  const scale = 0.3;
  const canvas = await html2canvas(firstPage, {
    useCORS: true,
    scale: scale
  });

  return canvas.toDataURL('image/png', quality);
}

watch(autosave, (newVal) => {
  if (newVal) {
    autosave_interval.value = setInterval(() => {
      SaveTemplate();
    }, 15000);
  } else if (autosave_interval) {
    clearInterval(autosave_interval.value);
    autosave_interval.value = null;
  }
});
watch(()=>is_data_loaded.value, (newVal) => {
  if (newVal == true) {
    const edit_id = route.params.id;
    if (edit_id) {
      const find_template = <any>templates.value.find((t:any) => t.id == edit_id);
      if (find_template) {
        template.value = find_template;
        setTimeout(() => {
          (window as any).gjs_instance.setComponents(find_template.html_content);
          (window as any).gjs_instance.setStyle(find_template.css_content);
        }, 2000)
      }
    }
  }
});
onUnmounted(() => {
  if (autosave_interval.value) {
    clearInterval(autosave_interval.value);
  }
  hide_layout.value = false;
});
</script>


