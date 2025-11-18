export interface SnackbarType {
  visible: boolean;
  content: string;
  type: string;
  icon: string;
}
export interface ProfileType {
  name: string;
  email: string;
  sub: string;
  picture: string;
  given_name: string;
  role: string;
  [key: string]: any;
}
export interface DatasetType {
  qty: number,
  price_margin: number;
  dataset_id: number;
  dataset_attached: boolean;
  accessory_id: number;
  accessory_price: number;
}
export interface ProductType {
  id:number;
  qty:number;
  price:number;
  price_margin:number;
}
export interface PrintCostType {
  charge_type: string;
  black_prints_included: number;
  color_prints_included: number;
  black_prints_cost: number;
  black_prints_margin: number;
  color_prints_cost: number;
  color_prints_margin: number;
  black_overage_cost: number;
  color_overage_cost: number;
}
export interface ProposalType {
  // References
  id: number | null;
  user_id: number | string | undefined;
  partner_id: number | string | null;
  template_id: any;
  template_css: string | undefined | null;
  template_html: string | undefined | null;
  hash_code: string;

  // Proposal Category/Type
  category: string;
  acquisition_type: string;

  // Company Details
  company_name: string;
  first_name: string;
  last_name: string;
  email: string;

  // Proposal Details
  title: string;
  cover_letter: string;
  status: string;
  expiry_date: string;
  sent_date: string;

  // Lease Details
  lease_type: string;
  lease_term_offered: number | string;
  lease_term_selected: number | string;

  // Toggles
  prints_included_free: number;
  is_global_print_cost: number;
  show_contract_pages:  number;
  show_term_options:    number;
  show_prints_cost:     number;

  // Signature Details
  accepted_by: string;
  accepted_date: string;
  accepted_ip_addr: string;
  accepted_initials: string;
  accepted_signature: string;

  // These are objects stored as JSON
  lease_factor_provider: any;
  global_print_cost: PrintCostType;
  cart_items: any[];
  cost_addons: any[];
  it_service_items: any[];
  contract_pages: ContractPageType[];

  // Timestamps
  created_at: string;
  updated_at: string;

  // Any other custom fields
  [key: string]: any;
}
export interface PartnerType {
  id: number | null;
  name: string;
  website: string;
  logo: string;
  phone_number: string;
  brand_color: string;
  smtp_host: string;
  smtp_port: string;
  smtp_username: string;
  smtp_password: string;
  products_price_url: string;
  supported_brands: any[];
  is_active: number;
}
export interface EmailFormType {
  to: string;
  cc: string[];
  from: string;
  body: string;
  subject: string;
}
export interface TemplateType {
  id: number | null;
  user_id: string | null;
  partner_id: number | null;
  name: any;
  html_content: string;
  css_content: string;
  is_default: number;
  thumbnail: string;
  created_at: string;
  updated_at: string;
}
export interface AddonType {
  id: number | null;
  user_id: string;
  partner_id: number | null;
  name: string;
  type: string;
  price: number;
  qty: number;
  price_margin: number;
  charge_type: string;
  created_at: string;
  updated_at: string;
}
export interface CustomFieldType {
  id: number | null;
  user_id: string;
  partner_id: number | null;
  field_key: string;
  field_type: string;
  field_label: string;
  field_options: string[];
  created_at: string;
  updated_at: string;
}
export interface FieldValueType {
  id: number | null;
  proposal_id: number | null;
  field_id: number | null;
  field_value: string;
  created_at: string;
  updated_at: string;
}
export interface OptionsType {
  id: number | null;
  user_id: string;
  partner_id: number | null;
  related_to: string;
  key_name: string;
  name: string;
  content: string;
  created_at: string;
  updated_at: string;
}
export interface ITServiceItemType {
  id: number | null;
  user_id: string;
  partner_id: number | null;
  tier_id: number | null;
  name: string;
  unit_price: number;
  unit_type: string;
  quantity: number;
  description: string;
  charge_type: string;
  price_margin: number;
  created_at: string;
  updated_at: string;
}
export interface ITServiceTierType {
  id: number | null;
  user_id: string;
  partner_id: number | null;
  name: string;
  description: string;
  charge_type: string;
  created_at: string;
  updated_at: string;
}
export interface Auth0UserType {
  user_id:   string,
  firstname: string,
  lastname:  string,
  username:  string,
  password:  string,
  role_id:   string,
}
export interface MetricsType {
  id: string | null;
  proposal_id: number;
  total_views: number;
  page_data: string | any[];
  created_at: string;
  updated_at: string;
}
export interface ContractPageType {
  id: string;
  title: string;
  display: boolean;
  category: string;
  hide_if_signed: boolean;
}

/**
 * TYPES FOR GLOBAL STATE
 **/
export interface GlobalStateTypes {
  snackbar: SnackbarType;
  profile: ProfileType | null;
  sidebar: boolean;
  is_mobile: boolean;
  llm_fetching: boolean;
  proposal: ProposalType;
  prop_custom_fields: any[];
  global_loading: boolean;
  is_data_loaded: boolean;
  are_customfields_added: boolean;
  hide_layout: boolean;

  // Filters
  proposal_filter_loading: boolean;
  proposal_active_filter: number;
  proposal_filters: any;
  proposal_ai_filter: any;

  // API DATA
  products: any;
  datasets: any;
  accessories: any;
  lease_terms: any;
  manufacturers: any;
  categories: any;

  // Catalog Filters
  printer_categories: string[];
  paper_sizes: string[];
  connection_types: string[];
  media_types: string[];


  partners: PartnerType[];
  partner_users: any;
  proposals: ProposalType[];
  modals: any;
  email_form: EmailFormType;
  providers: any[];
  files: any[];
  product_pricing: [];
  accessory_pricing: [];
  templates: TemplateType[];
  addons: AddonType[];
  custom_fields: CustomFieldType[];
  field_values: FieldValueType[];
  options: OptionsType[];
  it_service_tiers: ITServiceTierType[];
  it_service_items: ITServiceItemType[];
  metrics: MetricsType[];

  // Auth0
  users: any;

  // View Proposal
  view_proposal: ProposalType | null;
  view_prop_config: any;

  // GrapeJS
  gjs_instance: any | null;
  is_gjs_loaded: boolean;

  // Component Reorder
  component_order: any[];
  is_reordering: boolean;
  panels: any;

  // Theming
  backend_theme: string;
  frontend_theme: string;

  // Dialog
  dialog: any;

  // Partner Form
  partner_loading: boolean,
  partner_modal: boolean,
  partner_form: PartnerType;

  // Auth0 Modals
  new_user_modal: boolean;
  user_added_modal: boolean;
  auth0_roles: any[];
}
