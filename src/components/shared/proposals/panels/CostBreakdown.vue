<template>
  <v-expansion-panels :style="theme_border_radius" :model-value="panels.cost_breakdown" @update:model-value="store.SetPanel('cost_breakdown',$event)" class="mb-4">
    <panel class="border" :style="theme_border_radius" title="Cost Breakdown">
      <template #icon>
        <div class="d-flex align-center">
          <DragHandle>
            <img src="/assets/img/proposal/cost-breakdown.png" width="25" height="25">
          </DragHandle>
        </div>
      </template>
      <v-card>
        <v-card-text>
          <!--Copier Leasing Header Inputs-->
          <template v-if="copier_lease_category">
            <v-row>
              <v-col cols="12" lg="6" md="6" sm="12">
                <v-row>
                  <v-col cols="12" lg="4" md="4" sm="12">
                    <v-select
                      hide-details
                      class="mb-2"
                      return-object
                      color="primary"
                      density="compact"
                      item-title="name"
                      variant="outlined"
                      :items="my_addons"
                      v-model="the_addon"
                      label="Insert Addons"
                      item-value="charge_type"
                      prepend-inner-icon="mdi-package-variant-closed-plus"
                      @update:modelValue="InsertAddon"
                      v-if="proposal.lease_term_offered"
                      @click:prepend-inner="RefreshAddons">
                    </v-select>
                  </v-col>
                  <v-col cols="12" lg="4" md="4" sm="12">
                    <v-select
                      v-if="is_leasing"
                      variant="outlined"
                      density="compact"
                      label="Lease Term"
                      :items="lease_terms_items"
                      :disabled="!proposal.lease_factor_provider"
                      v-model.number="proposal.lease_term_offered">
                    </v-select>
                  </v-col>
                  <v-col cols="12" lg="4" md="4" sm="12">
                    <v-text-field
                      v-if="is_leasing"
                      variant="outlined"
                      density="compact"
                      label="Lease Factor (Read-only)"
                      :items="lease_terms_items"
                      :readonly="true"
                      :model-value="GetLeaseFactor(proposal)">
                    </v-text-field>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </template>

          <!--For Copier Leasing TABLE-->
          <v-data-table
            :items="[]"
            hide-no-data
            class="border"
            hide-default-footer
            density="comfortable"
            :style="theme_table_style"
            v-if="copier_lease_category"
            :headers="copier_lease_cost_breakdown_headers">

            <template #body.append>
              <PlaceholderRow/>

              <!--PRODUCTS-->
              <template v-for="(cart_item,ci) in proposal.cart_items">
                <CopierLineItem :cart_item="cart_item" :index="ci"/>
                <AccessoriesRows :cart_item="cart_item"/>
                <PerCopierPrintRows :cart_item="cart_item"/>
              </template>

              <GlobalPrintRows/>
              <CostAddonsRow/>
              <CustomAddonRow/>
              <GrandTotalRow/>
            </template>
            <!--#body.append-->
          </v-data-table>

          <!--For IT Services TABLE-->
          <v-data-table
            :items="[]"
            hide-no-data
            class="border"
            hide-default-footer
            density="comfortable"
            :style="theme_table_style"
            v-if="it_service_category"
            :headers="it_services_cost_breakdown_headers">

            <template #body.append>
              <!--Placeholder-->
              <PlaceholderRow v-if="!proposal.it_service_items.length"/>

              <!--Line Items-->
              <ITServicesLineItem
                :cart_index="i"
                :cart_item="cart_item"
                v-for="(cart_item,i) in proposal.it_service_items">
              </ITServicesLineItem>

              <!--Grand Total-->
              <ITServiceGrandTotal/>
            </template>
            <!--#body.append-->
          </v-data-table>
        </v-card-text>
      </v-card>
    </panel>
  </v-expansion-panels>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import type {AddonType} from "@/types/StoreTypes";
import {is_leasing} from "@/composables/ProposalComposable";
import {GetLeaseFactor} from "@/composables/ProductComposable";
import {lease_terms_items} from "@/composables/ProposalComposable";
import {it_services_cost_breakdown_headers} from "@/composables/ProposalComposable";
import {copier_lease_cost_breakdown_headers} from "@/composables/ProposalComposable";
import {
  my_addons,
  theme_table_style,
  theme_border_radius,
  it_service_category,
  copier_lease_category
} from "@/composables/GlobalComposables";

const store = GlobalStore();
const the_addon = ref<any>(null);
const {getAccessTokenSilently} = useAuth0();
const {proposal,is_reordering,panels} = storeToRefs(store);

// Functions
const InsertAddon = (addon: AddonType) => {
  proposal.value.cost_addons.push({
    name: addon.name,
    type: addon.type,
    price: Number(addon.price),
    qty: Number(addon.qty),
    price_margin: Number(addon.price_margin),
    charge_type: addon.charge_type,
  });
  the_addon.value = null;
}
const RefreshAddons = async() => {
  store.FetchAddons(await getAccessTokenSilently());
}

// Close the Panel if reordering
watch(is_reordering, (newVal) => {
  if (newVal) panels.value.cost_breakdown = 1;
});
</script>

