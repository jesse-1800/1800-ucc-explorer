<template>
  <AppLayout>
    <template #title>UCC Filings</template>
    <template #content>

      <InnerLayout>
        <template #sidebar>
          <v-card-text>
            <UccFilingsFilters/>
          </v-card-text>
        </template>
        <template #content>
          <v-data-table density="comfortable" :style="theme_table_style" :items="filtered_ucc_filings" :headers="headers">
            <template #item="{item}">
              <tr>
                <td>{{item.id}}</td>
                <td>{{ item.buyer_company }}</td>
                <td><v-chip color="primary">{{FindUccEquipments(item.id).length}}</v-chip></td>
                <td>{{item.ucc_date}}</td>
                <td>{{item.ucc_status}}</td>
                <td>
                  <v-btn variant="outlined" size="small" prepend-icon="mdi-file-find" color="primary">View</v-btn>
                  <v-btn variant="outlined" size="small" prepend-icon="mdi-pencil" color="primary" class="ml-1">Edit</v-btn>
                </td>
              </tr>
            </template>
          </v-data-table>
        </template>
      </InnerLayout>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import moment from "moment";
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {FindUccBuyer} from "@/composables/GlobalComposables";
import {FindUccEquipments} from "@/composables/GlobalComposables";
import {theme_table_style} from "@/composables/GlobalComposables";


const store = GlobalStore();
const headers = <any>[
  {title: "UCC ID",     value: "id",           sortable: true},
  {title: "Company",    value: "buyer_company",sortable: true},
  {title: "Equipments", value: "equipments",   sortable: true},
  {title: "UCC Date",   value: "ucc_date",     sortable: true},
  {title: "UCC Status", value: "ucc_status",   sortable: true},
  {title: "Manage",     value: "manage",       sortable: false},
]
const {ucc_filings} = storeToRefs(store);
const filtered_ucc_filings = computed(() => {
  // Add extra properties for table sort to work.
  let filtered_ucc_list = ucc_filings.value.map(filing => ({
    ...filing,
    buyer_company: FindUccBuyer(filing.buyer_id)?.buyer_company || '(None)',
    equipments: FindUccEquipments(filing.id).length
  }));

  // Search filter
  const search = filters.value.search?.toLowerCase().trim() || ''
  if (search) {
    filtered_ucc_list = filtered_ucc_list.filter(row => {
      return (
        row.id.toString().toLowerCase().includes(search) ||
        row.buyer_company.toLowerCase().includes(search) ||
        row.equipments.toString().includes(search) ||
        row.ucc_date?.toLowerCase().includes(search) ||
        row.ucc_status?.toLowerCase().includes(search)
      )
    })
  }

  // Star/End date filter
  if (filters.value.start_date || filters.value.end_date) {
    filtered_ucc_list = filtered_ucc_list.filter(row => {
      if (!row.ucc_date) return false

      const ucc_date = moment(row.ucc_date, 'MM/DD/YYYY')
      const start_date = filters.value.start_date ? moment(filters.value.start_date) : null;
      const end_date = filters.value.end_date ? moment(filters.value.end_date) : null;

      if (start_date && end_date) {
        return ucc_date.isSameOrAfter(start_date, 'day') && ucc_date.isSameOrBefore(end_date, 'day')
      }
      if (start_date) {
        return ucc_date.isSameOrAfter(start_date, 'day')
      }
      if (end_date) {
        return ucc_date.isSameOrBefore(end_date, 'day')
      }
      return true
    });
  }

  // Provider ID filter
  if (filters.value.provider_id) {
    filtered_ucc_list = filtered_ucc_list.filter(row => {
      return row.provider_id == filters.value.provider_id;
    });
  }

  // Assignee ID filter
  if (filters.value.assignee_id) {
    filtered_ucc_list = filtered_ucc_list.filter(row => {
      return row.assignee_id == filters.value.assignee_id;
    });
  }

  // UCC Status filter
  if (filters.value.ucc_status) {
    filtered_ucc_list = filtered_ucc_list.filter(row => {
      return row.ucc_status == filters.value.ucc_status;
    });
  }

  return filtered_ucc_list;
});
const {ucc_filing_filters:filters} = storeToRefs(store);
</script>
