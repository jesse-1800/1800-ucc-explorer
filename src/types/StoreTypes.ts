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
export interface Auth0UserType {
  user_id:   string,
  firstname: string,
  lastname:  string,
  username:  string,
  password:  string,
  role_id:   string,
}

/**
 * TYPES FOR GLOBAL STATE
 **/
export interface GlobalStateTypes {
  snackbar: SnackbarType;
  profile: ProfileType | null;
  sidebar: boolean;
  is_mobile: boolean;
  global_loading: boolean;
  is_data_loaded: boolean;

  // Modals
  modals: any;

  // Theming
  backend_theme: string;
  frontend_theme: string;

  // Dialog
  dialog: any;

  // Partner Form
  partner_loading: boolean;
  partner_modal: boolean;
  partner_form: PartnerType;

  // Auth0 Form
  new_user_modal: boolean;
  user_added_modal: boolean;

  // Data Arrays
  users: any[];
  files: any[];
  partners: PartnerType[];
  auth0_roles: any[];
  partner_users: any[];
}
