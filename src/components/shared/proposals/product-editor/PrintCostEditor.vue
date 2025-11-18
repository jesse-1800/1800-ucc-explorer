<template>
  <v-expansion-panels elevation="0" class="mt-4" model-value="1">
    <panel class="border" title="Prints Included & Cost" icon="mdi-printer-pos-cog-outline">
      <v-card-text>
        <v-divider class="my-10">Included Prints</v-divider>

        <!--HELPER ALERT BOX-->
        <div class="mb-10" v-if="proposal.is_global_print_cost==1">
          <v-alert variant="tonal" type="info" title="You're using 'Global' Printing Costs">
            To modify the Global prints included and cost, go to Lease Details section or in Cost Breakdown
          </v-alert>
        </div>

        <v-row>
          <v-col cols="12" lg="6" md="6" sm="12">
            <v-text-field
              variant="outlined"
              density="comfortable"
              label="BLACK Prints Included"
              :disabled="proposal.is_global_print_cost==1"
              v-model.number="item.print_cost.black_prints_included">
              <template #prepend-inner>
                <v-icon class="black-indicator">mdi-invert-colors</v-icon>
              </template>
            </v-text-field>
            <v-text-field
              variant="outlined"
              density="comfortable"
              label="COLOR Prints Included"
              :disabled="proposal.is_global_print_cost==1"
              v-model.number="item.print_cost.color_prints_included">
              <template #prepend-inner>
                <v-icon class="color-indicator">mdi-invert-colors</v-icon>
              </template>
            </v-text-field>
          </v-col>
          <v-col cols="12" lg="6" md="6" sm="12">
            <v-text-field
              variant="outlined"
              density="comfortable"
              label="BLACK Cost Per Print (CPP)"
              prepend-inner-icon="mdi-currency-usd"
              :disabled="proposal.is_global_print_cost==1"
              v-model.number="item.print_cost.black_prints_cost">
            </v-text-field>

            <v-text-field
              variant="outlined"
              density="comfortable"
              label="COLOR Cost Per Print (CPP)"
              prepend-inner-icon="mdi-currency-usd"
              :disabled="proposal.is_global_print_cost==1"
              v-model.number="item.print_cost.color_prints_cost">
            </v-text-field>
          </v-col>
        </v-row>

        <v-divider class="my-10">Overage Prints</v-divider>

        <v-text-field
          variant="outlined"
          density="comfortable"
          label="BLACK Overage Cost"
          prepend-inner-icon="mdi-currency-usd"
          :disabled="proposal.is_global_print_cost==1"
          v-model.number="item.print_cost.black_overage_cost">
        </v-text-field>
        <v-text-field
          variant="outlined"
          density="comfortable"
          label="COLOR Overage Cost"
          prepend-inner-icon="mdi-currency-usd"
          :disabled="proposal.is_global_print_cost==1"
          v-model.number="item.print_cost.color_overage_cost">
        </v-text-field>
      </v-card-text>
    </panel>
  </v-expansion-panels>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const {proposal} = storeToRefs(store);
const {item} = defineProps(['item','index']);

</script>
