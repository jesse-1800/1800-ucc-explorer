<template>
  <AppLayout>
    <template #title>Proposals</template>
    <template #content>
      <div class="d-flex align-center mb-4">
        <TableSearchBox v-model="search_model"/>
        <v-spacer/>
        <v-btn
          class="mr-2"
          text="Refresh"
          color="primary"
          @click="Refresh"
          :loading="is_loading"
          :style="theme_btn_style"
          prepend-icon="mdi-refresh">
        </v-btn>
        <v-btn
          prepend-icon="mdi-plus-circle"
          text="Create a Proposal"
          href="/proposals/create"
          :style="theme_btn_style"
          color="primary">
        </v-btn>
      </div>

      <!--The Table-->
      <v-data-table
        :headers="headers"
        class="elevation-1 border"
        :loading="is_loading"
        :style="`
          ${theme_table_style};
          ${theme_border_radius}
        `"
        :items="filter_proposals">
        <template v-slot:item="{item:proposal}:any">
          <tr>
            <td>{{proposal.id}}</td>
            <td>{{proposal.category == 'copier-lease'? 'Copier Lease':'IT Service'}}</td>
            <td>{{proposal.title}}</td>
            <td>{{proposal.first_name}} {{proposal.last_name}}</td>
            <td>{{proposal.company_name}}</td>
            <td>
              <v-chip
                class="text-capitalize"
                :text="proposal.status"
                :color="StatusColor(proposal)">
              </v-chip>
            </td>
            <td>
              <v-chip
                @click="ViewMetrics(proposal.id)"
                v-tooltip="'Click to see all Metrics'"
                :text="FindMetrics(proposal.id).total_views">
              </v-chip>
            </td>
            <td>
              <v-btn
                text="Edit"
                size="small"
                class="mr-1"
                variant="outlined"
                color="primary"
                @click="Edit(proposal)"
                :style="theme_border_radius"
                prepend-icon="mdi-pencil">
              </v-btn>
              <v-btn
                text="View"
                size="small"
                class="mr-1"
                variant="outlined"
                color="primary"
                target="_blank"
                :style="theme_border_radius"
                :href="ProposalUrl(proposal)"
                prepend-icon="mdi-eye">
              </v-btn>
              <v-btn
                text="PDF"
                size="small"
                class="mr-1"
                color="orange"
                target="_blank"
                variant="outlined"
                :style="theme_border_radius"
                prepend-icon="mdi-file-sign"
                :href="`/proposals/download/${proposal.hash_code}`">
              </v-btn>
              <v-btn
                prepend-icon="mdi-delete"
                :loading="is_loading"
                :style="theme_border_radius"
                variant="outlined"
                text="Delete"
                class="mr-1"
                color="red"
                size="small"
                @click="Delete(proposal)">
              </v-btn>
            </td>
          </tr>
        </template>
      </v-data-table>

      <!--Metics Modal-->
      <MetricsModal :proposal_id="proposal_id"/>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import {useRouter} from "vue-router";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import type {ProposalType} from "@/types/StoreTypes";
import {ProposalServer} from "@/plugins/proposal-server";
import {
  ProposalUrl,
  theme_btn_style,
  theme_table_style,
  theme_border_radius,
} from "@/composables/GlobalComposables";
import {FindMetrics} from "@/composables/ProposalComposable";

const router = useRouter();
const store = GlobalStore();
const search_model = ref('');
const is_loading = ref(false);
const headers = [
  {title: 'ID',      sortable:true, value: 'id'},
  {title: 'Category',sortable:true, value: 'category'},
  {title: 'Title',   sortable:true, value: 'title'},
  {title: 'Customer',sortable:true, value: 'customer'},
  {title: 'Company', sortable:true, value: 'company'},
  {title: 'Status',  sortable:true, value: 'status'},
  {title: 'Views',   sortable:true, value: 'status'},
  {title: 'Actions', sortable:true, value: 'actions'},
];
const proposal_id = ref<number|null>(null);
const {getAccessTokenSilently} = useAuth0();
const filter_proposals = computed(() => {
  // If no search term, return all proposals
  if (!search_model.value) {
    return props.selected_proposals;
  }

  // If searching, filter the proposals
  const s = search_model.value.toLowerCase();
  return props.selected_proposals.filter((proposal:ProposalType) => {
    return proposal.title.toLowerCase().includes(s)   ||
      proposal.first_name.toLowerCase().includes(s)   ||
      proposal.last_name.toLowerCase().includes(s)    ||
      proposal.company_name.toLowerCase().includes(s) ||
      proposal.id?.toString().includes(s);
  });
});
const props = defineProps(['selected_proposals']);

// Local functions
const Delete = async (proposal:any) =>{
  const is_confirmed = await store.OpenDialog("Confirm Action","Are you sure you want to delete this proposal?");
  if (is_confirmed) {
    is_loading.value = true;
    const token = await getAccessTokenSilently();
    const server = ProposalServer(token);
    const form = new FormData;
    form.append('proposal_id', proposal?.id);
    form.append('hash_code', proposal.hash_code);
    server.post('/proposals/destroy',form).then(res => {
      console.log(res.data);
    }).finally(() => {
      is_loading.value = false;
      store.FetchProposals(token);
    })
  }
}
const Edit = (proposal:ProposalType) => {
  router.push('/proposals/edit/'+proposal.id);
}
const StatusColor = (proposal:ProposalType) => {
  const {status} = proposal;
  if (status === 'draft') {
    return 'grey';
  }
  else if (status === 'open') {
    return 'blue';
  }
  else if (status === 'sent') {
    return '#8BC34A';
  }
  else if (status === 'accepted') {
    return 'green';
  }
  else if (status === 'rejected') {
    return 'red';
  }
}
const Refresh = async () => {
  is_loading.value = true;
  const token = await getAccessTokenSilently();
  store.FetchMetrics(token);
  store.FetchProposals(token);
  is_loading.value = false;
}
const ViewMetrics = (prop_id: number) => {
  proposal_id.value = prop_id;
  store.modals.metrics_modal = true;
}
</script>
