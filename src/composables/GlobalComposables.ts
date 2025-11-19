import {storeToRefs} from "pinia";
import moment from "moment/moment";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const {
  users,
  files,
  modals,
  profile,
  partners,
  partner_users,
  backend_theme,
} = storeToRefs(store);

// Computed properties
export const my_profile = computed(() => {
  return profile.value;
});
export const my_user_id = computed(() => {
  if (!profile.value) return ("");
  return profile.value?.sub
});
export const my_partner_id = computed(() => {
  if (!profile.value) return ("");
  const partner = partner_users.value?.find((pu:any) => {
    return pu.user_id == profile.value?.sub;
  });
  if (partner != undefined) {
    return partner.partner_id;
  } else {
    return ("");
  }
});
export const my_partner_object = computed(() => {
  if (!profile.value) return null;
  const partner = partner_users.value.find((pu:any) => {
    return pu.user_id == profile.value?.sub;
  });
  return partners.value.find((p:any) => p.id == partner?.partner_id);
});
export const my_company_name = computed(() => {
  const partner = partners.value?.find((p:any) => {
    return p.id == my_partner_id.value
  });

  if (partner != undefined) {
    return partner.name;
  } else {
    return ("");
  }
});
export const my_files = computed(() => {
  return files.value.filter(f => f.partner_id == my_partner_id.value);
});
export const my_users = computed(() => {
  return users.value.filter((u:any) => {
    return IsUserInMyTeam(u.user_id) && u.role != "Admin";
  });
});
export const my_partner_user_profile = computed(() => {
  return partner_users.value.find((pu:any) => {
    return pu.user_id == my_user_id.value;
  });
});

// Methods
export const ToggleModal = (name: string,value:boolean) => {
  modals.value[name] = value;
};
export const IsUserInMyTeam = (user_id:string) => {
  const matches = partner_users.value.filter((pu:any) => {
    return pu.user_id == user_id && pu.partner_id == my_partner_id.value;
  });
  return matches.length > 0;
}
export const ParseValidName = (name:string) => {
  const parts = name.trim().split(/\s+/);
  if (!parts?.length) {
    return { firstname: '', lastname: '' }
  }
  const lastname = parts.pop()!;
  const firstname = parts.join(' ');
  return { firstname, lastname };
}
export const FindPartnerUser = (user_id:string) => {
  return partner_users.value.find((pu:any) => pu.user_id == user_id);
}
export const IsMyself = (user_id:string) => {
  return user_id == my_user_id.value;
}

// Access Levels
export const is_admin = computed(() => {
  return profile.value?.role?.toLowerCase().includes('admin');
});
export const is_manager = computed(() => {
  return profile.value?.role.toLowerCase().includes('manager');
});

// Theming

export const cm8_gradient = ref('linear-gradient(44deg,rgba(4,0,110,1) 0%,rgba(0,0,171,1) 51%,rgba(0,100,200,1) 100%)');
export const style_defaults = ref({
  border_radius: '5px',

  // For buttons
  elems_light_theme_gradient: 'linear-gradient(45deg,#5198D8,#7C95ED,#BBA9FC)',
  elems_dark_theme_gradient: 'linear-gradient(44deg,rgba(4, 0, 110, 1) 0%, rgba(0, 0, 171, 1) 51%, rgba(0, 100, 200, 1) 100%)',

  // For body background
  body_light_theme_gradient: 'radial-gradient(circle, rgb(255, 242, 247) 0%, rgb(230, 241, 255) 100%)',
  body_dark_theme_gradient: 'radial-gradient(circle,rgba(13, 11, 79, 1) 0%, rgba(11, 11, 54, 1) 48%, rgba(0, 7, 23, 1) 100%)',
})
export const theme_main_background = computed(() => {
  const gradients = <any>{
    light: style_defaults.value.body_light_theme_gradient,
    dark: style_defaults.value.body_dark_theme_gradient,
  }
  return `background: ${gradients[backend_theme.value]}`;
});
export const theme_border_radius = ref(`border-radius:${style_defaults.value.border_radius}`);
export const theme_switch_background = computed(() => {
  const gradients = <any>{
    light: cm8_gradient.value,
    dark: cm8_gradient.value,
  }
  return `background: ${gradients[backend_theme.value]}`;
});
export const theme_btn_style = computed(() => {
  const gradients = <any>{
    light: {
      background: cm8_gradient.value,
      color: 'white !important',
      border: 'none',
      borderRadius: style_defaults.value.border_radius,
      boxShadow: 'none',
    },
    dark: {
      background: cm8_gradient.value,
      color: 'white !important',
      border: 'none',
      borderRadius: style_defaults.value.border_radius,
      boxShadow: 'none',
    }
  }

  return gradients[backend_theme.value];
});
export const theme_table_style = computed(() => {
  if (backend_theme.value == 'light') {
    return (`
      ${theme_border_radius.value}
    `)
  } else {
    return (`
      background:linear-gradient(57deg,rgba(5, 4, 36, 1) 0%, rgba(6, 6, 59, 1) 100%);
      ${theme_border_radius.value}
    `)
  }
});
export const theme_card_style = computed(() => {
  if (backend_theme.value == 'light') {
    return (`
      background: white;
      ${theme_border_radius.value}
    `)
  } else {
    return (`
      background:linear-gradient(57deg,rgba(5, 4, 36, 1) 0%, rgba(6, 6, 59, 1) 100%);
      ${theme_border_radius.value}
    `)
  }
});
export const theme_card_title_style = computed(() => {
  if (backend_theme.value == 'light') {
    return (`
      background:${cm8_gradient.value};
      color: #ffffff;
    `)
  } else {
    return `background:rgba(28, 33, 65, 0.8)`
  }
});
export const theme_drawer_style = computed(() => {
  if (backend_theme.value == 'light') {
    return ``
  } else {
    return 'background:#060927'
  }
});
export const theme_navbar_style = computed(() => {
  if (backend_theme.value == 'light') {
    return `background:linear-gradient(44deg,rgba(4,0,110,1) 0%,rgba(0,0,171,1) 51%,rgba(0,100,200,1) 100%)`
  } else {
    return `background:rgba(7,10,42,0.8)`
  }
});
export const theme_modal_style = computed(() => {
  if (backend_theme.value == 'light') {
    return 'background: #ffffff'
  } else {
    return 'background: linear-gradient(57deg,rgba(5,4,36,1) 0%, rgba(6,6,59,1) 100%)'
  }
});
export const theme_panel_color = computed(() => {
  if (backend_theme.value == 'light') {
    return (`
      background:${cm8_gradient.value};
      color: #ffffff;
    `)
  } else {
    return `background:rgba(28, 33, 65, 0.8)`
  }
});

// Timestamps
export const timestamp_now = ref(
  moment().format("YYYY-MM-DD hh:mm A")
);
