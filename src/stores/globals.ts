import {defineStore} from 'pinia';
import type {ProfileType} from '@/types/StoreTypes';
import type {PrintCostType} from '@/types/StoreTypes';
import type {GlobalStateTypes} from '@/types/StoreTypes';
import {ProposalServer} from "@/plugins/proposal-server";

export const GlobalStore = defineStore('globals', {
  state: (): GlobalStateTypes => ({
    snackbar: {
      visible: false,
      content: '',
      type: '',
      icon: ''
    },
    profile: null,
    sidebar: false,
    is_mobile: false,
    hide_layout: false,
    global_loading: false,
    is_data_loaded: false,
    are_customfields_added: false,

    // Proposal Filtering
    proposal_active_filter: 2,
    proposal_filter_loading: false,
    proposal_filters: {
      manufacturer_id:   null,
      filter_color:      null,
      connectivity:      [  ],
      print_volume_min:  null,
      print_volume_max:  null,
      print_speed_color: null,
      print_speed_black: null,
      paper_sizes:         [],
      search_filter:     (''),
    },
    proposal_ai_filter: {
      user_prompt:   (''),
      product_results: [],
      response_summary: "",
    },

    // API data
    products: [],
    datasets: [],
    accessories: [],
    lease_terms: [],
    manufacturers: [],
    categories: [],

    // Catalog Filters
    printer_categories: [],
    paper_sizes: [],
    connection_types: [],
    media_types: [],

    partners: [],
    partner_users: [],
    proposals: [],
    providers: [],
    files: [],
    product_pricing: [],
    accessory_pricing: [],
    templates: [],
    addons: [],
    custom_fields: [],
    field_values: [],
    options: [],
    it_service_tiers: [],
    it_service_items: [],
    metrics: [],

    // Auth0
    users: [],

    // AI Settings
    llm_fetching: false,

    // Proposal Related
    proposal: {
      // References
      id:            null,
      user_id:       "",
      partner_id:    "",
      template_id:   null,
      template_css:  "",
      template_html: "",
      hash_code:     "",

      // Proposal Category/Type
      category: "copier-lease", // copier-lease, it-services
      acquisition_type: "lease",

      // Company Details
      company_name: "",
      first_name:   "",
      last_name:    "",
      email:        "",

      // Proposal Details
      title:          "",
      cover_letter:   "",
      status:         "draft",
      expiry_date:    "",
      sent_date:      "",

      // Lease Details
      lease_type:            "", // fmv,buyout,amv
      lease_term_offered:    "", // staff selection
      lease_term_selected:   "", // customer selection


      // Toggables
      prints_included_free: 0,
      is_global_print_cost: 0,
      show_contract_pages: 1,
      show_term_options: 1,
      show_prints_cost: 1,

      // Signature Details
      accepted_by: "",
      accepted_date: "",
      accepted_ip_addr: "",
      accepted_initials: "",
      accepted_signature: "",

      // These are objects stored as JSON
      lease_factor_provider: "",
      global_print_cost: <PrintCostType>{
        charge_type:   'fixed-monthly',
        black_prints_included: 0,
        color_prints_included: 0,

        black_prints_cost:     0,
        black_prints_margin:   0,
        color_prints_cost:     0,
        color_prints_margin:   0,

        black_overage_cost:    0, // always global
        color_overage_cost:    0, // always global
      },
      cart_items: [],
      cost_addons: [],
      it_service_items: [],
      contract_pages: [],

      // Timestamps
      created_at: "",
      updated_at: "",
    },

    // Proposal custom fields
    prop_custom_fields: [],

    // Send Proposal Form
    email_form: {
      to: "",
      cc: [],
      from: "",
      body: "",
      subject: "",
    },

    // Modals
    modals: {
      send_proposal: false,
      preview_proposal: false,
      proposal_template: false,
      proposal_category: true,
      metrics_modal: false,
      paperwork_editor: false,
    },

    // View Proposal Related
    view_proposal: null,
    view_prop_config: {
      is_loading: false,
      accept_modal: false,
    },

    // GrapeJS related
    gjs_instance: null,
    is_gjs_loaded: false,

    // Component Reorder
    component_order: [0, 1, 2, 3],
    is_reordering: false,

    // Collapsibles 0 = open
    panels: {
      products: 0,
      lease_details: 0,
      proposal_details: 0,
      cost_breakdown: 0,
    },

    // Theming
    backend_theme: 'dark',
    frontend_theme: 'dark',

    // Dialog
    dialog: {
      show: false,
      title: "",
      content: "",
      ok_txt: 'OK',
      cancel_txt: 'Cancel',
      resolver: null as null | ((v: boolean) => void),
    },

    // Partner Form
    partner_loading: false,
    partner_modal: false,
    partner_form: {
      id: null,
      name: '',
      website: '',
      logo: '',
      phone_number: '',
      brand_color: '',
      smtp_host: '',
      smtp_port: '',
      smtp_username: '',
      smtp_password: '',
      products_price_url: '',
      supported_brands: [],
      is_active: 1,
    },

    // Auth0 Form
    new_user_modal: false,
    user_added_modal: false,
    auth0_roles: [],
  }),
  persist: {
    pick: [
      'profile',
      'sidebar',
      'component_order',
      'panels',
      'backend_theme',
      'frontend_theme',

      // Catalog Data
      'products',
      'datasets',
      'accessories',
      'lease_terms',
      'manufacturers',
      'categories',
      'copier_types',

      // Auth0
      'auth0_roles'
    ],
  },
  actions: {
    // UI Actions
    LogError(message: string) {
      console.log('%c'+message, 'color: #FF2F50; font-weight: bold;');
    },
    ShowError(message: string) {
      this.snackbar = {
        visible: true,
        content: message,
        type: 'error',
        icon: 'mdi-alert-circle'
      };
    },
    LogSuccess(message: string) {
      console.log('%c'+message, 'color: #4CAF50; font-weight: bold;');
    },
    ShowSuccess(message: string) {
      this.snackbar = {
        visible: true,
        content: message,
        type: 'success',
        icon: 'mdi-check-circle'
      };
    },
    CloseSnackbar() {
      this.snackbar = {
        visible: false,
        content: "",
        type: '',
        icon: ''
      };
    },
    SetProfile(profile: ProfileType | null) {
      this.profile = profile;
    },
    SetState(payload: Partial<GlobalStateTypes>) {
      Object.assign(this, payload)
    },
    SetPanel(target:string, value:any) {
      this.panels[target] = (value==undefined) ? 1 : 0;
    },
    SetPropCustomFields(field_values: any[]) {
      this.prop_custom_fields = field_values;
    },

    // API Calls
    async FetchCatalogData() {
      return await ProposalServer('').post('/catalog/fetch').then(response => {
        this.products           = response.data.products;
        this.datasets           = response.data.datasets;
        this.accessories        = response.data.accessories;
        this.lease_terms        = response.data.lease_terms;
        this.manufacturers      = response.data.manufacturers;
        this.categories         = response.data.categories;
        this.printer_categories = response.data.printer_categories;
        this.paper_sizes        = response.data.paper_sizes;
        this.connection_types   = response.data.connection_types;
        this.media_types        = response.data.media_types;
        this.LogSuccess("Products fetched successfully.");
      });
    },
    async FetchUsers(token:any) {
      return await ProposalServer(token).get('/users/fetch').then(response => {
        this.users = response.data;
      });
    },
    async FetchPartners(token:any) {
      return await ProposalServer(token).get('/partners/fetch').then(response => {
        this.partners = response.data;
      });
    },
    async FetchPartnerUsers(token:any) {
      return await ProposalServer(token).get('/partnerusers/fetch').then(response => {
        this.partner_users = response.data;
      });
    },
    async FetchProposals(token:any) {
      this.global_loading = true;
      return await ProposalServer(token).get(`/proposals/fetch/${this.FindPartnerId()}`).then(response => {
        this.proposals = response.data;
      }).finally(() => {
        this.global_loading = false;
      });
    },
    async FetchProviders(token:any) {
      this.global_loading = true;
      return await ProposalServer(token).get(`/providers/fetch/${this.FindPartnerId()}`).then(response => {
        this.providers = response.data;
      }).finally(() => {
        this.global_loading = false;
      });
    },
    async FetchFiles(token:any) {
      return await ProposalServer(token).get(`/files/fetch/${this.FindPartnerId()}`).then(response => {
        this.files = response.data;
      });
    },
    async FetchProductPricing({token,partner_id}:any) {
      const endpoint = `/pricing/products-pricing/${partner_id}`;
      return await ProposalServer(token).get(endpoint).then(res=>{
        this.product_pricing = res.data;
      });
    },
    async FetchAccessoryPricing({token,partner_id}:any) {
      const endpoint = `/pricing/accessories-pricing/${partner_id}`;
      return await ProposalServer(token).get(endpoint).then(res=>{
        this.accessory_pricing = res.data;
      });
    },
    async FetchTemplates(token:any) {
      this.global_loading = true;
      return await ProposalServer(token).get(`/templates/fetch/${this.FindPartnerId()}`).then(response => {
        this.templates = response.data;
      }).finally(() => {
        this.global_loading = false;
      });
    },
    async FetchAddons(token:any) {
      this.global_loading = false;
      return await ProposalServer(token).get(`/addons/fetch/${this.FindPartnerId()}`).then(response => {
        this.addons = response.data;
      }).finally(() => {
        this.global_loading = false;
      });;
    },
    async FetchCustomFields(token:any) {
      return await ProposalServer(token).get(`/customfields/fetch/${this.FindPartnerId()}`).then(response => {
        this.custom_fields = response.data;
      });
    },
    async FetchFieldValues(token:any) {
      return await ProposalServer(token).get('/fieldvalues/fetch').then(response => {
        this.field_values = response.data;
      });
    },
    async FetchOptions(token:any) {
      return await ProposalServer(token).get(`/options/fetch/${this.FindPartnerId()}`).then(response => {
        this.options = response.data;
      });
    },
    async FetchMetrics(token:any) {
      return await ProposalServer(token).get(`/metrics/fetch/${this.FindPartnerId()}`).then(response => {
        this.metrics = response.data;
      });
    },
    async FetchItServiceItemsAndTiers(token:any) {
      return await ProposalServer(token).get(`/itservices/fetch/${this.FindPartnerId()}`).then(res => {
        this.it_service_items = res.data.items;
        this.it_service_tiers = res.data.tiers;
      });
    },
    async SubmitPartnerForm(token:any,my_user_id:string) {
      const form = new FormData();
      const route = this.partner_form.id ? "/partners/update" : "/partners/store";
      this.partner_loading = true;
      form.append("user_id", my_user_id);
      form.append("partner", JSON.stringify(this.partner_form));
      ProposalServer(token).post(route, form).then((res: any) => {
        console.log(res.data);
        this.ShowSuccess(`Form successfully ${this.partner_form.id ? 'updated!' : 'added!'}`);
      }).finally(() => {
        this.partner_modal = false;
        this.FetchPartners(token).finally(() => {
          this.partner_loading = false;
        });
      });
    },
    async FetchAuth0Roles(token:any) {
      if (this.auth0_roles.length) {
        return;
      }
      return await ProposalServer(token).get('/users/fetch-roles').then(response => {
        this.auth0_roles = response.data;
      });
    },

    // Fetches all data reduce overhead
    async FetchAllData(token:string) {
      return await ProposalServer(token).get(`/data/fetch/${this.FindPartnerId()}`).then(res=>{
        this.users            = res.data.users;
        this.files            = res.data.files;
        this.auth0_roles      = res.data.auth0_roles;
        this.options          = res.data.options;
        this.products         = res.data.products;
        this.datasets         = res.data.datasets;
        this.accessories      = res.data.accessories;
        this.lease_terms      = res.data.lease_terms;
        this.manufacturers    = res.data.manufacturers;
        this.categories       = res.data.categories;
        this.partners         = res.data.partners;
        this.proposals        = res.data.proposals;
        this.providers        = res.data.providers;
        this.partner_users    = res.data.partner_users;
        this.templates        = res.data.templates;
        this.addons           = res.data.addons;
        this.custom_fields    = res.data.custom_fields;
        this.field_values     = res.data.field_values;
        this.it_service_items = res.data.it_service_items;
        this.it_service_tiers = res.data.it_service_tiers;
        this.metrics          = res.data.metrics;

        // Catalog Filters
        this.printer_categories = res.data.printer_categories;
        this.paper_sizes        = res.data.paper_sizes;
        this.connection_types   = res.data.connection_types;
        this.media_types        = res.data.media_types;

        this.is_data_loaded  = true;
      });
    },

    // Dialogs
    OpenDialog(title: string, content: string, ok_txt: string = 'OK', cancel_txt: string = 'CANCEL'): Promise<boolean> {
      this.dialog.title = title;
      this.dialog.content = content;
      this.dialog.show = true;
      this.dialog.cancel_txt = cancel_txt;
      this.dialog.ok_txt = ok_txt;
      return new Promise(resolve => {
        this.dialog.resolver = resolve;
      })
    },
    ConfirmDialog() {
      this.dialog.show = false
      this.dialog.resolver?.(true)
      this.dialog.resolver = null
    },
    CancelDialog() {
      this.dialog.show = false
      this.dialog.resolver?.(false)
      this.dialog.resolver = null
    },

    // For recovering Partner ID
    FindPartnerId() {
      const find_pu = this.partner_users.find((p:any) => p.user_id == this.profile?.sub);
      return find_pu ? find_pu.partner_id : null;
    }
  }
});
