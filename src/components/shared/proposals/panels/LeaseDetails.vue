<template>
  <!--Only shows if Lease Proposal-->
  <div v-if="copier_lease_category">
    <v-expansion-panels :style="theme_border_radius" :model-value="panels.lease_details" @update:model-value="store.SetPanel('lease_details',$event)" class="mb-4">
    <panel class="border" :style="theme_border_radius" icon="mdi-briefcase-edit-outline" title="Lease/Purchase Details">
      <template #icon>
        <DragHandle/>
      </template>
      <v-card>
        <v-card-text>

          <!--Feature Toggles-->
          <v-expansion-panels>
            <panel class="border" title="Features" icon="mdi-pencil">
              <v-card>
                <v-card-text>
                  <div class="mb-10" v-if="is_purchase">
                    <v-alert variant="tonal" type="info" title="Proposal type is 'Purchase'">
                      Some inputs are disabled as they do not apply to Purchase proposals.
                    </v-alert>
                  </div>
                  <v-row>
                    <!--Lease Details Input-->
                    <v-col cols="12" lg="6" md="6" sm="12">
                      <v-select
                        variant="outlined"
                        label="Proposal Acquisition Type"
                        v-model.number="proposal.acquisition_type"
                        :items="[{title:'Lease',value:'lease'},{title:'Purchase',value:'purchase'}]">
                      </v-select>
                      <v-select
                        variant="outlined"
                        label="Prints Included Type"
                        v-model.number="proposal.is_global_print_cost"
                        :items="[
                          {title:'Global (shared across all copiers)',value:1},
                          {title:'Per Copier (separate allowance for each machine)', value:0}
                        ]">
                      </v-select>
                      <v-select
                        variant="outlined"
                        label="FREE Prints Included?"
                        v-model.number="proposal.prints_included_free"
                        :items="[{title:'Yes',value:1},{title:'No', value:0}]">
                      </v-select>
                    </v-col>

                    <!--Proposal Details Toggles-->
                    <v-col cols="12" lg="6" md="6" sm="12">
                      <v-card-text class="border pa-5">
                        <v-switch color="info" hide-details label="Term Options" v-model.number="proposal.show_term_options" :true-value="1" :false-value="0" :disabled="is_purchase"/>
                        <v-switch color="info" hide-details label="Prints Included & Rates" v-model.number="proposal.show_prints_cost" :true-value="1" :false-value="0"/>
                        <v-switch color="info" hide-details label="Show All Contract Pages" v-model.number="proposal.show_contract_pages" :true-value="1" :false-value="0">
                          <template #label>
                            Show All Contract Pages
                            <v-icon
                              size="x-small"
                              class="ml-1 mt-1"
                              icon="mdi-information-outline"
                              v-tooltip="`Show Contract Pages regardless if signed or not.`">
                            </v-icon>
                          </template>
                        </v-switch>
                      </v-card-text>
                    </v-col>

                    <!--Regular Pages Toggles-->
                    <v-col cols="12" lg="6" md="6" sm="12" v-if="regular_pages.length">
                      <v-card-text class="border pl-5 pr-5">
                        <h3 class="font-weight-light text-center">Proposal Pages</h3>
                        <template v-for="(regular_page,rp) in regular_pages" :key="rp">
                          <div class="d-flex items-center justify-space-between">
                            <v-switch
                              color="info"
                              hide-details
                              density="compact"
                              v-model="regular_page.display">
                              <template #label>
                                <span :class="`ml-1 ${regular_page.hide_if_signed?'text-disabled':''}`">
                                  {{regular_page.title}}
                                  <v-icon v-if="regular_page.hide_if_signed">mdi-eye-off-outline</v-icon>
                                </span>
                              </template>
                            </v-switch>
                             <v-menu :close-on-content-click="false">
                               <template #activator="{ props }">
                                 <v-btn
                                   class="mt-1"
                                   size="small"
                                   variant="text"
                                   v-bind="props"
                                   icon="mdi-chevron-down">
                                 </v-btn>
                               </template>
                               <v-list>
                                 <v-list-item>
                                   <v-switch
                                     color="info"
                                     hide-details
                                     label="Hide Page if Signed"
                                     v-model="regular_page.hide_if_signed">
                                   </v-switch>
                                 </v-list-item>
                               </v-list>
                             </v-menu>
                          </div>
                        </template>
                      </v-card-text>
                    </v-col>

                    <!--Contract Pages Toggles-->
                    <v-col cols="12" lg="6" md="6" sm="12" v-if="contract_pages.length">
                      <v-card-text class="border pl-5 pr-5">
                        <h3 class="font-weight-light text-center">Contract Pages</h3>
                        <template v-for="(group,g) in contract_page_groups" :key="g">
                          <div class="d-flex items-center justify-space-between">
                            <v-switch
                              color="info"
                              hide-details
                              density="compact"
                              :label="group.group"
                              :model-value="IsGroupDisplayed(group)"
                              :disabled="proposal.show_contract_pages===0"
                              @update:modelValue="UpdateGroup($event,group)">
                              <template #label>
                                <span class="ml-1">{{ group.group }}</span>
                              </template>
                            </v-switch>
                          </div>
                        </template>
                      </v-card-text>
                    </v-col>

                  </v-row>
                </v-card-text>
              </v-card>
            </panel>
          </v-expansion-panels>

          <!--Lease Related Details-->
          <v-expansion-panels class="mt-4">
            <panel class="border" title="Lease Details" icon="mdi-pencil">
              <v-card>
                <v-card-text>
                  <v-row class="align-center">
                    <v-col cols="12" lg="6" md="6" sm="12">
                      <br>
                      <v-select
                        class="mt-5"
                        variant="outlined"
                        label="Lease Type"
                        :items="lease_types"
                        :disabled="is_purchase"
                        v-model="proposal.lease_type">
                      </v-select>
                      <v-select
                        class="mt-5"
                        item-value="id"
                        item-title="name"
                        variant="outlined"
                        :items="my_providers"
                        :disabled="is_purchase"
                        label="Financing Provider"
                        v-model="lease_provider_id">
                        <template #append-inner>
                          <tooltip>
                            <template #text>
                              <!--LEASE FACTOR DEMOGRAPHIC ONLY-->
                              <table style="width:300px">
                                <tbody>
                                <tr>
                                  <th class="text-left" style="width:100px">Lease Term</th>
                                  <th class="text-left" colspan="3">Lease Factors (by type)</th>
                                </tr>
                                <tr>
                                  <td></td>
                                  <td>FMV</td>
                                  <td>Buyout</td>
                                  <td>EFA</td>
                                </tr>
                                <template v-if="proposal.lease_factor_provider">
                                  <tr v-for="term_item in proposal.lease_factor_provider.lease_factors">
                                    <td>{{term_item.term}} Months</td>
                                    <td>{{term_item.fmv}}</td>
                                    <td>{{term_item.buyout}}</td>
                                    <td>{{term_item.efa}}</td>
                                  </tr>
                                </template>
                                <template v-else>
                                  <tr>
                                    <td class="text-center" colspan="4">
                                      <div class="my-10">Select a Financing Provider first!</div>
                                    </td>
                                  </tr>
                                </template>
                                </tbody>
                              </table>
                            </template>
                            <v-icon>mdi-information</v-icon>
                          </tooltip>
                        </template>
                      </v-select>
                      <v-select
                        class="mt-5"
                        variant="outlined"
                        label="Lease Term"
                        :items="lease_terms_items"
                        :disabled="!proposal.lease_factor_provider"
                        v-model.number="proposal.lease_term_offered">
                      </v-select>
                    </v-col>
                    <v-col cols="12" lg="6" md="6" sm="12">
                      <!--LEASE TERM PRICING OPTIONS-->
                      <table class="monthly-pricing-table mt-5">
                        <tbody>
                        <tr>
                          <th class="text-left border py-2 px-4 text-left">Lease Term</th>
                          <th class="text-left border py-2 px-4 text-center" colspan="3">
                            <span v-if="proposal.lease_factor_provider">
                              Monthly Cost Options ({{proposal.lease_factor_provider.name}})
                            </span>
                            <span v-else>
                              Select a Lease Term and Lease Factor first.
                            </span>
                          </th>
                        </tr>
                        <tr>
                          <td class="text-left border py-2 px-4"></td>
                          <td class="text-left border py-2 px-4">FMV</td>
                          <td class="text-left border py-2 px-4">Buyout</td>
                          <td class="text-left border py-2 px-4">EFA</td>
                        </tr>

                        <template v-if="proposal.lease_factor_provider">
                          <tr v-for="term_item in proposal.lease_factor_provider.lease_factors">
                            <td class="text-left border py-2 px-4">{{ term_item.term }} Months</td>
                            <td :class="{'is-active':IsActive('fmv',term_item.term)}" @click="SetTermAndType('fmv',term_item.term)" class="text-left border py-2 px-4">
                              ${{LeaseTermOption(proposal,term_item.term,'fmv')}}/month
                            </td>
                            <td :class="{'is-active':IsActive('buyout',term_item.term)}" @click="SetTermAndType('buyout',term_item.term)" class="text-left border py-2 px-4">
                              ${{LeaseTermOption(proposal,term_item.term,'buyout')}}/month
                            </td>
                            <td :class="{'is-active':IsActive('efa',term_item.term)}" @click="SetTermAndType('efa',term_item.term)" class="text-left border py-2 px-4">
                              ${{LeaseTermOption(proposal,term_item.term,'efa')}}/month
                            </td>
                          </tr>
                        </template>
                        <template v-else>
                          <tr>
                            <td colspan="4" class="border">
                              <h1 class="my-15 text-center font-weight-light">Select a Lease Term and Lease Factor first.</h1>
                            </td>
                          </tr>
                        </template>
                        </tbody>
                      </table>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-card>
            </panel>
          </v-expansion-panels>

          <!--Global Included Prints and Overage Rates-->
          <v-expansion-panels class="mt-4">
            <panel class="border" title="Global Included Prints and Overage Rates" icon="mdi-pencil">
              <v-card>
                <v-card-text>
                  <div class="mb-10" v-if="proposal.is_global_print_cost==0">
                    <v-alert variant="tonal" type="info" title="You're using 'Per Copier' Printing Costs">
                      To modify the prints included and cost, go to each Product > Print Costs section or in Cost Breakdown
                    </v-alert>
                  </div>
                  <v-row>
                    <!--GLOBAL PRINTS INCLUDED-->
                    <v-col cols="12" lg="4" md="4" sm="12">
                      <v-text-field
                        variant="outlined"
                        label="BLACK Prints Included"
                        :disabled="proposal.is_global_print_cost==0"
                        v-model.number="proposal.global_print_cost.black_prints_included">
                        <template #prepend-inner>
                          <v-icon class="black-indicator">mdi-invert-colors</v-icon>
                        </template>
                      </v-text-field>
                      <v-text-field
                        variant="outlined"
                        label="COLOR Prints Included"
                        :disabled="proposal.is_global_print_cost==0"
                        v-model.number="proposal.global_print_cost.color_prints_included">
                        <template #prepend-inner>
                          <v-icon class="color-indicator">mdi-invert-colors</v-icon>
                        </template>
                      </v-text-field>
                    </v-col>

                    <!--GLOBAL PRINT COST-->
                    <v-col cols="12" lg="4" md="4" sm="12">
                      <v-text-field
                        variant="outlined"
                        label="BLACK Cost Per Print (CPP)"
                        prepend-inner-icon="mdi-currency-usd"
                        :disabled="proposal.is_global_print_cost==0"
                        v-model.number="proposal.global_print_cost.black_prints_cost">
                      </v-text-field>
                      <v-text-field
                        variant="outlined"
                        label="COLOR Cost Per Print (CPP)"
                        prepend-inner-icon="mdi-currency-usd"
                        :disabled="proposal.is_global_print_cost==0"
                        v-model.number="proposal.global_print_cost.color_prints_cost">
                      </v-text-field>
                    </v-col>

                    <!--GLOBAL OVERAGE RATES-->
                    <v-col cols="12" lg="4" md="4" sm="12">
                      <v-text-field
                        :disabled="proposal.is_global_print_cost==0"
                        prepend-inner-icon="mdi-currency-usd"
                        variant="outlined"
                        label="BLACK Overage Cost (Global)"
                        v-model.number="proposal.global_print_cost.black_overage_cost">
                      </v-text-field>
                      <v-text-field
                        :disabled="proposal.is_global_print_cost==0"
                        prepend-inner-icon="mdi-currency-usd"
                        variant="outlined"
                        label="COLOR Overage Cost (Global)"
                        v-model.number="proposal.global_print_cost.color_overage_cost">
                      </v-text-field>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-card>
            </panel>
          </v-expansion-panels>
        </v-card-text>
      </v-card>
    </panel>
  </v-expansion-panels>
  </div>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import type {ContractPageType} from "@/types/StoreTypes";
import {is_purchase} from "@/composables/ProposalComposable";
import {CleanAndSetLeaseProvider, my_providers} from "@/composables/GlobalComposables";
import {LeaseTermOption} from "@/composables/ProductComposable";
import {lease_terms_items} from "@/composables/ProposalComposable";
import {theme_border_radius} from "@/composables/GlobalComposables";
import {copier_lease_category} from "@/composables/GlobalComposables";

// Variables
const store = GlobalStore();
const lease_provider_id = ref(null);
const lease_types = ref([
  {title: 'Fair Market Value Lease', value: 'fmv'},
  {title: '$1 Buyout Lease', value: 'buyout'},
  {title: 'Equipment Finance Agreement', value: 'efa'},
]);
const {proposal,is_reordering,panels} = storeToRefs(store);
const regular_pages = computed(() => {
  return proposal.value.contract_pages.filter(r=>r.category!='contract')
});
const contract_pages = computed(() => {
  return proposal.value.contract_pages.filter(c=>c.category == 'contract')
});
const contract_page_groups = computed(() => {
  const all_pages = contract_pages.value;
  const groups = <any>{};

  all_pages.forEach((page:any) => {
    const parent = all_pages.find((other:any) =>
      other.title !== page.title && page.title.startsWith(other.title)
    );
    const groupKey = parent?.title || page.title;
    if (!groups[groupKey]) groups[groupKey] = [];
    groups[groupKey].push({ ...page, isParent: !parent });
  });

  return Object.entries(groups).map(([groupName, all_pages]) => ({
    group: groupName,
    pages: (all_pages as any).sort((a:any,b:any)=>a.isParent?-1:b.isParent?1:0)
  }));
});

const IsGroupDisplayed = (group:any) => {
  return group.pages.every((page:any) => page.display);
}
const UpdateGroup = (is_checked:boolean,group:any) => {
  group.pages.forEach((page:any) => {
    const find_page = proposal.value.contract_pages.find(cp => cp.id == page.id);
    if (find_page) find_page.display = is_checked;
  });
}
const IsActive = (type:string, term:number) => {
  const {lease_factor_provider,lease_term_offered,lease_type} = proposal.value;
  return (
    lease_factor_provider &&
    lease_type === type &&
    lease_term_offered === term
  );
}
const SetTermAndType = (type:string, term:number) => {
  proposal.value.lease_type = type;
  proposal.value.lease_term_offered = term;
}

// Watcher
watch(is_reordering, (newVal) => {
  if (newVal) {
    const new_panels = { ...panels.value }
    new_panels.lease_details = 1
    store.SetState({ panels: new_panels })
  }
});

// When acquisition type changes, update all
// Products charge types to 'one-time' if purchase
watch(() => proposal.value.acquisition_type, (newVal) => {
  proposal.value.cart_items.forEach((cart_item:any) => {
    if (newVal === 'purchase') {
      cart_item.product.charge_type = 'one-time';
      cart_item.print_cost.charge_type = 'one-time';
      proposal.value.global_print_cost.charge_type = 'one-time';

      // If purchase, all addons must NOT be monthly
      proposal.value.cost_addons.forEach((addon:any) => {
        if (addon.charge_type == 'monthly') {
          addon.charge_type = 'one-time';
        }
      });
    } else if (newVal === 'lease') {
      cart_item.product.charge_type = 'monthly';
      cart_item.print_cost.charge_type = 'monthly';
      proposal.value.global_print_cost.charge_type = 'monthly';
      proposal.value.cost_addons.forEach((addon:any) => {
        if (addon.charge_type == 'one-time') {
          addon.charge_type = 'monthly';
        }
      });
    }
  });
});

// Watcher for when 'proposal.show_contract_pages' changes,
// It should also affect the individual contract pages
watch(() => proposal.value.show_contract_pages, (newVal) => {
  proposal.value.contract_pages.forEach((page:ContractPageType) => {
    if (page.category == 'contract') {
      page.display = newVal == 1;
    }
  });
});

// Watch the Temporary Lease Provider model to copy
// the Paperwork html/css in proposal
watch(() => lease_provider_id.value,(new_provider_id:any)=>{
  if (new_provider_id) {
    const find_provider = my_providers.value.find(cp => cp.id == new_provider_id);
    proposal.value.lease_factor_provider = CleanAndSetLeaseProvider(find_provider);
  }
});

// We're adding a watcher for lease_factor_provider so if
// lease_factor_provider is already set, we then select the right item in the selector above.
watch(() => proposal.value.lease_factor_provider,(new_provider:any)=>{
  if (new_provider) {
    lease_provider_id.value = new_provider.id;
  }
}, {immediate:true});
</script>

<style lang="scss" scoped>
table.monthly-pricing-table {
  width:100%;
  border-collapse: collapse;

  tr td {
    user-select: none;
    &.is-active {
      background: #0059ff;
      color: #fff
    }
  }
}
.black-indicator {
  color: #6e6e6e
}
.color-indicator {
  background: linear-gradient(to bottom, rgb(255,0,0), rgb(206, 0, 255), rgb(0, 150, 255));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
</style>
