<template>
  <p class="mb-5 text-center">
    <v-icon style="position:relative;bottom:2px">mdi-robot-outline</v-icon>
    <span class="ml-2">Let OSCAR help you find the right copier for you.</span>
  </p>

  <!--User Prompt-->
  <v-card elevation="0" class="border">
    <v-card-text class="pt-0">
      <v-textarea
        variant="plain"
        style="max-height:250px"
        :disabled="proposal_filter_loading"
        v-model="proposal_ai_filter.user_prompt"
        placeholder="Describe what you're looking for. e.g. 11x17, color, 60ppm, HP, supports wifi">
      </v-textarea>
    </v-card-text>
    <v-label style="font-size:12px" class="pa-2 border-t d-flex justify-center align-center">
      <v-icon class="mr-2">mdi-information-outline</v-icon>
      OSCAR is powered by OpenAl's artificial intelligence model, ChatGPT.
    </v-label>
  </v-card>

  <!--Submit Button-->
  <div class="text-center mt-5">
    <v-btn
      text="AI Search"
      :style="theme_btn_style"
      @click="DoAISearchFilter"
      prepend-icon="mdi-magnify"
      :loading="proposal_filter_loading"
      :disabled="!proposal_ai_filter.user_prompt">
    </v-btn>
  </div>

  <p class="mt-5 animate-moveFromTop">{{proposal_ai_filter.response_summary}}</p>
</template>
<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {my_manufacturers, theme_btn_style} from "@/composables/GlobalComposables";
import {my_partner_object} from "@/composables/GlobalComposables";
import {
  FindColorFromPrompt,
  FindBrandsFromPrompt,
  FindAvgPrintFromPrompt,
  FindPpmSpeedFromPrompt,
  FindConnectivityFromPrompt, AIResponseSummary, FindCopierSizeFromPrompt
} from "@/composables/ProposalComposable";
const store = GlobalStore();
const {getAccessTokenSilently} = useAuth0();
const {proposal_filter_loading,paper_sizes} = storeToRefs(store);
const {proposal_ai_filter,products} = storeToRefs(store);

const DoAISearchFilter = async() => {
  // They only support these brands
  let my_brand_ids = my_partner_object.value?.supported_brands ?? [];
  let ai_products_list = products.value.filter((p: any) => {
    return my_brand_ids?.includes(p.manufacturer_id)
  });
  let token = await getAccessTokenSilently();
  proposal_ai_filter.value.response_summary = "";


  // Filtering Paper Sizes
  const found_paper_sizes = <any>[];
  const user_copier_size = await FindCopierSizeFromPrompt(token);
  if (user_copier_size.length) {
    String(user_copier_size).split(',').forEach(ai_result_size => {
      // Check if AI result exists in paper size list
      if (paper_sizes.value.includes(ai_result_size)) {
        found_paper_sizes.push(ai_result_size);
      }
    });

    // If paper sizes were found in prompt, do filter.
    if (found_paper_sizes.length) {
      ai_products_list = ai_products_list.filter((product: any) => {
        return product.paper_size.some((size: string) => {
          return found_paper_sizes.includes(size);
        });
      });
    }
  }

  // Loading and Resets
  proposal_filter_loading.value = true;
  proposal_ai_filter.value.product_results = [];
  proposal_ai_filter.value.response_summary = "";

  // Filtering Brand
  const user_brand_id = await FindBrandsFromPrompt(token);
  const brand_exists = my_manufacturers.value.some(m=>m.id==user_brand_id);
  if (Number(user_brand_id)!=0 && brand_exists) {
    ai_products_list = ai_products_list.filter((product:any) => {
      return +product.manufacturer_id == +Number(user_brand_id);
    });
    console.log("User specified a Brand ID: ", user_brand_id)
  }

  // Filtering Connectivity Features
  const connectivity_list = await FindConnectivityFromPrompt(token);
  if (connectivity_list.length) {
    ai_products_list = ai_products_list.filter((product: any) => {
      return connectivity_list.some((item:any) => product.connectivity.includes(item.name));
    });
    console.log("User specified Connectivity Features:", connectivity_list)
  }

  // Filtering Color/Monochrome printers
  const user_color_requirement = await FindColorFromPrompt(token);
  if (['Color','B&W'].includes(user_color_requirement)) {
    ai_products_list = ai_products_list.filter((product: any) => {
      return product.copier_color.toLowerCase() == user_color_requirement.toLowerCase()
    });
    console.log("User specified Color Preference: ", user_color_requirement)
  }

  // Filter by printer's Avg monthly print
  const user_monthly_prints = await FindAvgPrintFromPrompt(token);
  if (user_monthly_prints > 0) {
    ai_products_list = ai_products_list.filter((product: any) => {
      return Number(product.avg_monthly_prints) >= user_monthly_prints
    });
    console.log(`User needs ${user_monthly_prints} pages/month`);
  }

  // Finally, let's detect PPM Speed
  const user_ppm_speed = await FindPpmSpeedFromPrompt(token);
  if (user_ppm_speed > 0) {
    ai_products_list = ai_products_list.filter((product: any) => {
      // For B&W only printers, just check B&W speed
      if (product.copier_color.toLowerCase() === 'b&w') {
        return Number(product.speed_black) >= user_ppm_speed;
      }
      // For color printers, check both speeds
      return Number(product.speed_color) >= user_ppm_speed && Number(product.ppm_bw) >= user_ppm_speed;
    });
    console.log(`User needs ${user_ppm_speed} ppm`);
  }

  // If nothing found, show error
  if (!ai_products_list.length) store.ShowError("AI could not find any product based on your search.");
  proposal_ai_filter.value.product_results = ai_products_list;

  // Let's provide a summary for the user.
  proposal_ai_filter.value.response_summary = await AIResponseSummary(token);

  proposal_filter_loading.value = false;
}
</script>
