<template>
  <v-expansion-panels elevation="0" model-value="0">
    <panel class="border" :style="theme_table_style" title="Product Details" icon="mdi-pencil-circle">
      <v-card-text>
        <!--Copier info-->
        <v-data-table :style="theme_table_style" hide-default-footer hide-no-data :items="[]" :headers="product_headers">
          <template #body.append>
            <tr>
              <td>{{FindProduct(item.product.id).name}}</td>
              <td>{{FindProduct(item.product.id).model_number}}</td>
              <td class="d-flex align-center">
                <TableButton :style="theme_border_radius" variant="outlined" text="Remove" @onclick="RemoveProduct(index)" icon="mdi-close" color="red" class="mr-2"/>
                <TableButton :style="theme_border_radius" variant="outlined" text="Duplicate" @onclick="DuplicateProduct(item)" icon="mdi-content-copy"/>
              </td>
            </tr>
          </template>
        </v-data-table>
      </v-card-text>
    </panel>
  </v-expansion-panels>
</template>

<script setup lang="ts">
import {FindProduct} from "@/composables/ProductComposable";
import {RemoveProduct} from "@/composables/ProductComposable";
import {DuplicateProduct} from "@/composables/ProductComposable";
import {theme_border_radius, theme_table_style} from "@/composables/GlobalComposables.ts";

const product_headers = [
  {title:'Model Name',  value:'model'},
  {title:'Model Number',value:'mdl'},
//{title:'Price',       value:'price'},
  {title:'Manage',      value:'action'}
]
const {item} = defineProps(['item','index'])
</script>
