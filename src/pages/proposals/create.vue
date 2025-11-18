<template>
  <AppLayout>
    <template #title>{{proposal.id?'Edit':'Create'}} a Proposal</template>
    <template #content>
      <draggable v-model="component_order"
        @start="SetState({is_reordering:true})"
        @end="SetState({is_reordering:false})"
        handle=".drag-handle"
        item-key="index">
        <template #item="{ element: componentIndex }">
          <div class="draggable-component">
            <component :is="component_list[componentIndex].component" :key="componentIndex"/>
          </div>
        </template>
      </draggable>

      <!--GrapesJS-->
      <div id="gjs-dummy" class="d-none"></div>

      <!--Modals-->
      <SendProposalModal/>
  <!--<PreviewProposalModal/>-->
      <ProposalCategoryModal v-if="!proposal.id"/>
      <PaperworkEditor :is_proposal="true" v-if="modals.paperwork_editor"/>
      <ProposalTemplateEditor @update="SubmitProposal" v-if="modals.proposal_template"/>
    </template>
    <template #footer>
      <FooterTotal/>
      <v-spacer/>
      <v-btn
        size="small"
        color="info"
        class="mr-4"
        variant="outlined"
        text="Paperwork"
        :disabled="is_loading"
        v-if="proposal.template_id"
        :style="theme_border_radius"
        prepend-icon="mdi-playlist-edit"
        @click="ToggleModal('paperwork_editor',true)">
      </v-btn>
      <v-btn
        size="small"
        color="info"
        class="mr-4"
        variant="outlined"
        text="Template"
        :disabled="is_loading"
        v-if="proposal.template_id"
        :style="theme_border_radius"
        prepend-icon="mdi-cookie-edit-outline"
        @click="ToggleModal('proposal_template',true)">
      </v-btn>
      <v-btn
        size="small"
        text="View Proposal"
        color="info"
        class="mr-4"
        width="150"
        v-if="proposal.id"
        :disabled="is_loading"
        variant="outlined"
        :href="proposal_view_url"
        target="_blank"
        :style="theme_border_radius"
        prepend-icon="mdi-eye">
      </v-btn>
      <v-btn
        size="small"
        text="Send"
        color="info"
        class="mr-4"
        width="120"
        variant="outlined"
        v-if="proposal.id"
        :disabled="is_loading"
        :style="theme_border_radius"
        @click="ToggleModal('send_proposal',true)"
        prepend-icon="mdi-send">
      </v-btn>
      <v-btn
        size="small"
        color="primary"
        :loading="is_loading"
        @click="SubmitProposal"
        prepend-icon="mdi-floppy"
        :style="theme_btn_style"
        :disabled="proposal.status=='accepted'"
        :text="GetProposalSubmitTitle">
      </v-btn>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
import moment from "moment";
import grapesjs from 'grapesjs';
import {storeToRefs} from "pinia";
import draggable from 'vuedraggable';
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {useRoute, useRouter} from "vue-router";
import type {ProposalType} from "@/types/StoreTypes";
import {ProposalServer} from "@/plugins/proposal-server";
import Products from "@/components/shared/proposals/panels/Products.vue";
import LeaseDetails from "@/components/shared/proposals/panels/LeaseDetails.vue";
import CostBreakdown from "@/components/shared/proposals/panels/CostBreakdown.vue";
import ProposalDetails from "@/components/shared/proposals/panels/ProposalDetails.vue";
import {
  GetOption,
  ToggleModal,
  my_providers,
  my_templates,
  my_partner_id,
  timestamp_now,
  my_field_values,
  theme_btn_style,
  my_custom_fields,
  theme_border_radius,
  it_service_category,
  copier_lease_category,
  CleanAndSetLeaseProvider,
} from "@/composables/GlobalComposables";
import {proposal_view_url} from "@/composables/ProposalComposable.ts";

const route = useRoute();
const router = useRouter();
const store = GlobalStore();
const errors = ref(<any>[]);
const is_loading = ref(false);
const is_editing = computed(() => {
  return proposal.value.id !== undefined && proposal.value.id !== null;
});
const {getAccessTokenSilently} = useAuth0();
const component_list = ref([
  { name: 'Products', component: Products },
  { name: 'LeaseDetails', component: LeaseDetails },
  { name: 'ProposalDetails', component: ProposalDetails },
  { name: 'CostBreakdown', component: CostBreakdown }
]);
const {ShowSuccess,ShowError,SetState} = store;
const {
  modals,
  profile,
  proposal,
  proposals,
  is_data_loaded,
  component_order,
  prop_custom_fields
} = storeToRefs(store);


const combined_html_content = computed(() => {
  return proposal.value.template_html + proposal.value.paperwork_html;
});

// Get Custom Fields for this Proposal
const GetCustomFields = () => {
  store.SetPropCustomFields([]);

  // For new Proposals (Creating)
  if (!proposal.value.id) {
    const new_field_values = <any>[];
    my_custom_fields.value.forEach((field) => {
      if (!prop_custom_fields.value.find(fv=>fv.field_id==field.id)) {
        new_field_values.push({
          id:          null, // just null
          proposal_id: null, // filled in by PHP
          field_id:    field.id,
          field_value: "",
          created_at:  timestamp_now.value,
          updated_at:  timestamp_now.value,
        });
        store.SetPropCustomFields(new_field_values);
      }
    });
  }

  // For Existing Proposals (Editing)
  else {
    // First gather the existing fields
    store.SetPropCustomFields(my_field_values.value)

    // Then if there are any new fields, add them
    const my_field_values_copy = [...prop_custom_fields.value];
    my_custom_fields.value.forEach((field) => {
      if (!my_field_values_copy.find(fv=>fv.field_id==field.id)) {
        my_field_values_copy.push({
          id:          null, // just null
          proposal_id: null, // filled in by PHP
          field_id:    field.id,
          field_value: "",
          created_at:  timestamp_now.value,
          updated_at:  timestamp_now.value,
        });
        store.SetPropCustomFields(my_field_values_copy);
      }
    });
  }
}

// Form Validation
const ValidateForm = () => {
  const error_labels: Partial<Record<keyof ProposalType, string>> = {
    company_name: "Company Name",
    first_name: "First Name",
    last_name: "Last Name",
    email: "Email",
    title: "Title",
    cover_letter: "Cover Letter",
  };
  errors.value = [];

  Object.entries(error_labels).forEach(([key, label]) => {
    const field = key as keyof ProposalType;
    const value = proposal.value[field];
    if (
      value === "" ||
      value === null ||
      value === undefined ||
      (Array.isArray(value) && value.length === 0)
    ) {
      errors.value.push(label);
    }
  });

  if (errors.value.length > 0) {
    const errorMessages = errors.value.map((label:string) => `- ${label} is required.<br>`).join(" ");
    ShowError(`The following fields are required: <br><br>${errorMessages}`);
    return false;
  }

  if (!proposal.value.template_id) {
    ShowError('Please choose a template.');
    return false;
  }

  if (copier_lease_category.value && !proposal.value.cart_items.length) {
    ShowError('Please add at least one product to the proposal.');
    return false;
  }

  if (it_service_category.value && !proposal.value.it_service_items.length) {
    ShowError('Please add at least one IT Service Item to the proposal.');
    return false;
  }

  return true;
};

// Form Insert/Update
const SubmitProposal = async () => {
  if (!ValidateForm()) return;

  const form = new FormData;
  const token = await getAccessTokenSilently();
  const endpoint = is_editing.value? '/proposals/update':'/proposals/store';

  is_loading.value = true;
  form.append('proposal', JSON.stringify({...proposal.value}));
  form.append('field_values', JSON.stringify({...prop_custom_fields.value}));
  ProposalServer(token).post(endpoint,form).then(res => {
    console.log(res.data);
    ShowSuccess(`Proposal ${proposal.value.id?'updated':'created'}!`);

    // Edit mode after saving a new proposal
    if (!is_editing.value) {
      return location.href = (
        `/proposals/edit/${res.data.proposal_id}`
      );
    }

  }).finally(() => {
    store.FetchMetrics(token);
    store.FetchProposals(token);
    store.FetchFieldValues(token);
    is_loading.value = false;
  });
}

// Check if we're editing a Proposal
const IsOnEditMode = () => {
  const params = <any>{...route.params};
  const proposal_id = params.id;
  if (proposal_id) {
    const find_proposal = proposals.value.find((p:ProposalType) => p.id == proposal_id);
    if (!find_proposal) {
      store.ShowError('Proposal not found!');
      return router.push('/proposals');
    }
    find_proposal.lease_term_offered = Number(find_proposal.lease_term_offered);
    find_proposal.template_id = Number(find_proposal.template_id);
    proposal.value = {...find_proposal};

    // For Custom Fields
    GetCustomFields();
  }
  else {
    SetInitialValues();
  }
}

// Set the initial form values
const SetInitialValues = async() => {
  proposal.value.id = null;
  proposal.value.user_id = profile.value?.sub || "";
  proposal.value.partner_id = my_partner_id.value;
  proposal.value.expiry_date = moment().add('15','days').format('YYYY-MM-DD');
  proposal.value.created_at = timestamp_now.value;
  proposal.value.updated_at = timestamp_now.value;

  // Set Template ID, HTML, CSS
  const default_template = my_templates.value.find((t:any)=>t.is_default==1);
  if (default_template != undefined) {
    proposal.value.template_id = my_templates.value.find((t:any)=>t.is_default==1)!.id;
    proposal.value.template_css = my_templates.value.find((t:any)=>t.is_default==1)!.css_content;
    proposal.value.template_html = my_templates.value.find((t:any)=>t.is_default==1)!.html_content;
  }

  // Lease Factor Provider and Lease Term Offered
  if (my_providers.value.length <= 0) {
    const confirm_redirect = await store.OpenDialog('Confirm action', 'You have not added any Finance Provider. Do you want to create one now?')
    if (confirm_redirect) {
      return router.push('/providers');
    }
  }

  let default_provider = my_providers.value.find((p:any)=>p.is_default==1) || my_providers.value[0];
  if (default_provider) {
    proposal.value.lease_factor_provider = CleanAndSetLeaseProvider(default_provider);
    proposal.value.lease_term_offered = proposal.value.lease_factor_provider.lease_factors[0].term;
    proposal.value.lease_type = "fmv";
  } else {
    store.ShowError("Finance Provider not found. You must add one to continue. Redirecting in 3 seconds...");
    setTimeout(() => {
      return router.push('/providers');
    },3000);
  }

  // Set Global Prints default values from 'Options'
  const global_ovg_rate_black = <any>GetOption('global_overage_cost_black');
  const global_ovg_rate_color = <any>GetOption('global_overage_cost_color');
  const cost_per_print_black = <any>GetOption('cost_per_print_black');
  const cost_per_print_color = <any>GetOption('cost_per_print_color');

  if (global_ovg_rate_black) {
    proposal.value.global_print_cost.black_overage_cost = Number(global_ovg_rate_black);
  }
  if (global_ovg_rate_color) {
    proposal.value.global_print_cost.color_overage_cost = Number(global_ovg_rate_color);
  }
  if (cost_per_print_black) {
    proposal.value.global_print_cost.black_prints_cost = Number(cost_per_print_black);
  }
  if (cost_per_print_color) {
    proposal.value.global_print_cost.color_prints_cost = Number(cost_per_print_color);
  }

  // For Custom Fields
  GetCustomFields();
}

const GetProposalSubmitTitle = computed(() => {
  if (proposal.value.status == 'accepted') return 'Accepted (Readonly)';
  return is_editing.value ? 'Update Proposal' : 'Save Proposal';
});

// Watch when proposals is filled then fetch
watch(() => is_data_loaded.value,async(new_value)=>{
  if (new_value) IsOnEditMode();
},{immediate:true});

// When partner_id is available, fetch products/accessories pricing
watch(() => my_partner_id.value,async(new_partner_id)=>{
  if (new_partner_id) {
    const token = await getAccessTokenSilently();
    store.FetchProductPricing({token,partner_id:new_partner_id});
    store.FetchAccessoryPricing({token,partner_id:new_partner_id});
  }
},{immediate:true});

// Load the temporary grapesjs
onMounted(() => {
  (window as any).gjs_temporary = grapesjs.init({
    container: '#gjs-dummy',
    storageManager: false,
  });
})

// We need to watch everytime the paperwork/template html changes
// so we can get all the updated pages for enable/disable feature
watch(()=>combined_html_content.value,(new_content) => {
  // If already has content and GJS is loaded, reread updated pages.
  if (new_content.length && (window as any).gjs_temporary) {

    // Set components and Updated pages
    (window as any).gjs_temporary.setComponents(combined_html_content.value);
    const pages_found = (window as any).gjs_temporary.DomComponents.getWrapper().find('section.pdf-page');

    // Build map from GrapesJS
    const gjsMap = new Map<string, any>();
    pages_found.forEach((page: any) => {
      const is_contract = page.getClasses().includes('contract-page');

      // Skip if it's a shortcode e.g. {product_title}
      if (!/^\{.*\}$/.test(page.getAttributes().title)) {
        gjsMap.set(page.getId(), {
          id: page.getId(),
          title: page.getAttributes().title,
          display: true, // default, may get overridden
          category: is_contract ? 'contract':'regular',
          hide_if_signed: false,
        });
      }
    });

    // Sync existing items: remove missing, update title, keep display
    proposal.value.contract_pages = proposal.value.contract_pages.filter(p => gjsMap.has(p.id)).map(p => {
      const gjsPage = gjsMap.get(p.id);
      return {
        ...gjsPage,
        display: p.display,      // preserve existing display
        title: gjsPage.title,    // update title from GrapesJS
      };
    });

    // Add new items not yet in contract_pages
    gjsMap.forEach((page, id) => {
      if (!proposal.value.contract_pages.find(p => p.id === id)) {
        proposal.value.contract_pages.push(page);
      }
    });
  }
}, {immediate:true})

</script>
