<template>
  <!--If cart_item is a TIERED IT Service-->
  <v-expansion-panels :model-value="0" v-if="cart_item.tier" class="mb-4 animate-moveFromRight">
    <panel class="border" icon="mdi-semantic-web">
      <!--Title-->
      <template #title>
        <span class="ml-3 mr-3">{{cart_item.tier.name}}</span>
        <v-spacer/>
        <v-chip size="x-small" class="mr-5">TIERED PACKAGE</v-chip>
      </template>

      <!--Content-->
      <v-card>
        <v-card-text>
          <!--This term controls the items-->
          <v-select
            label="Term"
            max-width="250"
            variant="outlined"
            :items="it_svc_terms"
            v-model="cart_item.tier.charge_type">
          </v-select>

          <!--The Service Item Itself-->
          <ITServiceItem
            v-for="(line_item,i) in cart_item.items"
            :cart_index="cart_index"
            :line_item="line_item"
            :is_standalone="false"
            :line_item_index="i">
          </ITServiceItem>
        </v-card-text>
      </v-card>

      <!--Footer Button-->
      <template #footer>
        <v-spacer/>
        <v-btn
          color="red"
          width="120"
          size="small"
          text="Remove"
          variant="outlined"
          prepend-icon="mdi-close-circle"
          @click="RemoveTierCartItem(index)"
          :style="theme_border_radius"
          class="d-flex ma-auto mt-2 mb-1 mr-1">
        </v-btn>
        <v-btn
          size="small"
          color="primary"
          text="Duplicate"
          variant="outlined"
          prepend-icon="mdi-close-circle"
          @click="DuplicateTierCartItem(cart_item)"
          :style="theme_border_radius"
          class="d-flex ma-auto mt-2 mb-1 ml-1">
        </v-btn>
        <v-spacer/>
      </template>
    </panel>
  </v-expansion-panels>

  <!--If cart_item is a SINGLE IT Service ITEM-->
  <ITServiceItem
    v-for="(line_item,i) in cart_item.items"
    :cart_index="cart_index"
    :line_item="line_item"
    :is_standalone="true"
    :line_item_index="i"
    v-else>
  </ITServiceItem>

</template>
<script setup lang="ts">
import {GlobalStore} from "@/stores/globals";
import {it_svc_terms} from "@/composables/ProposalComposable";
import {RemoveTierCartItem} from "@/composables/ProposalComposable";
import {DuplicateTierCartItem} from "@/composables/ProposalComposable";
import {theme_border_radius} from "@/composables/GlobalComposables.ts";

defineProps(['cart_item','cart_index',]);
</script>
