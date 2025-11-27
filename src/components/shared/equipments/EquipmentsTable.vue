<template>
  <v-data-table
    :headers="headers"
    :items="equipments"
    density="comfortable"
    :style="theme_table_style">
  </v-data-table>
</template>

<script lang="ts" setup>
import {theme_table_style} from "@/composables/GlobalComposables";

const props = defineProps(['equipments']);
const headers = [
  { title: 'UCC ID',       value: 'ucc_filing_id',      sortable: true },
  { title: 'Unit',         value: 'equipment_unit',     sortable: true },
  { title: 'UCC Year',     value: 'equipment_ucc_year', sortable: true },
  { title: 'Number',       value: 'equipment_number',   sortable: true },
  { title: 'Brand',        value: 'equipment_brand',    sortable: true },
  { title: 'Model',        value: 'equipment_model',    sortable: true },
  { title: 'Description',  value: 'equipment_desc',     sortable: true },
  { title: 'Code',         value: 'equipment_code',     sortable: true },
  { title: 'Serial Number',value: 'equipment_serial_no',sortable: true },
  { title: 'Size',         value: 'equipment_size',     sortable: true },
  { title: 'End Year',     value: 'equipment_end_year', sortable: true },
  { title: 'Value',        value: 'equipment_value',    sortable: true },
]
</script>
