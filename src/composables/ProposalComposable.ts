import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {GetMarginPrice} from "@/composables/ProductComposable.ts";
import {my_custom_fields, my_manufacturers, my_partner_object} from "@/composables/GlobalComposables.ts";
import {Prompt} from "@/composables/LLMComposable.ts";

// States
const store = GlobalStore();
const {
  metrics,
  proposal,
  products,
  backend_theme,
  proposal_filters,
  proposal_ai_filter,
} = storeToRefs(store);

export const td_border_style = computed(() => {
  if (backend_theme.value === 'light') {
    return `
        border-left:1px solid #d1d1d1;
        border-right:1px solid #d1d1d1;
        width: 205px;
      `;
  } else {
    return `
      border-left:1px solid #333;
      border-right:1px solid #333;
      width: 205px;
    `;
  }
});
export const is_leasing = computed(() => {
  return proposal.value.acquisition_type == 'lease';
});
export const is_purchase = computed(() => {
  return proposal.value.acquisition_type == 'purchase';
});
export const lease_terms_items = computed(() => {
  const raw_object = toRaw(proposal.value.lease_factor_provider);
  if (!proposal.value.lease_factor_provider || Object.keys(raw_object).length === 0) return [];
  return proposal.value.lease_factor_provider?.lease_factors.map((factor:any) => ({
    title: `${factor.term} months`,
    value: factor.term,
  })) ?? [];
});
export const proposal_view_url = computed(() => {
  return (
    location.origin+'/proposal/'+proposal.value.hash_code
  )
});
export const proposal_categories = ref([
  {
    title:'Copier Lease',
    value:'copier-lease',
    icon: 'mdi-printer-pos-edit-outline',
  },
  {
    title:'IT Service',
    value:'it-service',
    icon: 'mdi-semantic-web',
  },
]);
export const it_svc_terms = ref([
  { value: "monthly", title: "Monthly",  multiplier: 1 },
  { value: "annually",title: "Annually", multiplier: 12},
  { value: "one-time",title: "One-Time", multiplier: 1 }
]);
export const it_svc_unit_types = ref([
  { key: "user",        title: "User"},
  { key: "device",      title: "Device"},
  { key: "server",      title: "Server"},
  { key: "license",     title: "License"},
  { key: "subscription",title: "Subscription"},
  { key: "hour",        title: "Hour"},
  { key: "site",        title: "Site"},
  { key: "project",     title: "Project"},
  { key: "unit",        title: "Unit"}
]);
export const copier_lease_cost_breakdown_headers = ref([
  {title:'Name',       value:'name'},
  {title:'SKU',        value:'sku'},
  {title:'Category',   value:'category'},
  {title:'Qty.',       value:'qty'},
  {title:'Base Price', value:'base-price'},
  {title:'Margin (%)', value:'margin'},
  {title:'Sell Price', value:'sell-price'},
  {title:'Charge Type',value:'charge_type'},
  {title:'Subtotal',   value:'Subtotal'},
]);
export const it_services_cost_breakdown_headers = ref([
  {title:'Name',       value:'name'},
  {title:'Category',   value:'category'},
  {title:'Qty.',       value:'qty'},
  {title:'Base Price', value:'base-price'},
  {title:'Margin (%)', value:'margin'},
  {title:'Sell Price', value:'sell-price'},
  {title:'Charge Type',value:'charge_type'},
  {title:'Subtotal',   value:'subtotal'},
]);
export const all_shortcodes = computed(() => {
  const shortcodes = [
    { value: "{client_first_name}",    title: "Client First Name" },
    { value: "{client_last_name}",     title: "Client Last Name" },
    { value: "{client_full_name}",     title: "Client Full Name" },
    { value: "{client_email}",         title: "Client Email" },
    { value: "{client_company_name}",  title: "Client Company Name" },
    { value: "{client_signature}",     title: "Client Signature" },
    { value: "{author_company_name}",  title: "Author Company Name" },
    { value: "{author_full_name}",     title: "Author Full Name" },
    { value: "{proposal_title}",       title: "Proposal Title" },
    { value: "{proposal_expiry_date}", title: "Proposal Expiry Date" },
    { value: "{proposal_sent_date}",   title: "Proposal Sent Date" },
    { value: "{proposal_lease_term}",  title: "Proposal Lease Term" },
    { value: "{proposal_lease_type}",  title: "Proposal Lease Type" },
    { value: "{black_prints_included}",title: "Black Prints Included" },
    { value: "{color_prints_included}",title: "Color Prints Included" },
    { value: "{black_overage_cost}",   title: "Black Overage Cost" },
    { value: "{color_overage_cost}",   title: "Color Overage Cost" },
    { value: "{monthly_payment}",      title: "Monthly Payment" },
    { value: "{one_time_payment}",      title: "One-time Payment" },
  ];
  my_custom_fields.value.forEach((field) => {
    shortcodes.push({
      value: `{${field.field_key}}`,
      title: field.field_label
    });
  });
  return shortcodes;
});
export const manual_filtered_products = computed(() => {
  // For Manual Filtering only

  let my_products = <any>[];
  let my_brand_ids = my_partner_object.value?.supported_brands ?? [];

  // Reduce it by partner's supported brands
  my_products = products.value.filter((product: any) => {
    return my_brand_ids?.includes(product.manufacturer_id)
  });

  // Filtered by manufacturer_id
  if (proposal_filters.value.manufacturer_id) {
    my_products = my_products.filter((product: any) => {
      return product.manufacturer_id === proposal_filters.value.manufacturer_id
    });
  }

  // Color Filter
  if (proposal_filters.value.filter_color) {
    my_products = my_products.filter((product: any) => {
      return product.copier_color === proposal_filters.value.filter_color
    });
  }

  // Connectivity Filter
  if (proposal_filters.value.connectivity.length) {
    my_products = my_products.filter((product: any) => {
      return proposal_filters.value.connectivity.some((item:any) => product.connectivity.includes(item));
    });
  }

  // Paper Sizes
  if (proposal_filters.value.paper_sizes.length) {
    my_products = my_products.filter((product: any) => {
      return proposal_filters.value.paper_sizes.some((ps:string) => {
        return product.paper_size.includes(ps);
      });
    })
  }

  // Print Volume Min/Max
  const avg_print_min = proposal_filters.value.print_volume_min;
  const avg_print_max = proposal_filters.value.print_volume_max;
  if (avg_print_min != null && avg_print_max != null) {
    my_products = my_products.filter((product: any) => {
      const printer_volume = Number(product.avg_monthly_prints);
      return printer_volume > avg_print_min && printer_volume < avg_print_max;
    });
  }

  // Print Speed Color
  const print_spd_color = proposal_filters.value.print_speed_color;
  if (print_spd_color != null) {
    my_products = my_products.filter((product: any) => {
      return Number(product.speed_color) >= print_spd_color;
    });
  }

  // Print Speed B&W
  const print_spd_black = proposal_filters.value.print_speed_black;
  if (print_spd_black != null) {
    my_products = my_products.filter((product: any) => {
      return Number(product.speed_black) >= print_spd_black;
    });
  }

  // Search Input Filter
  if (proposal_filters.value.search_filter.length) {
    my_products = my_products.filter((product: any) => {
      return product.name.toLowerCase().includes(proposal_filters.value.search_filter.toLowerCase());
    })
  }

  return my_products;
});
export const ai_filtered_products = computed(() => {
  return proposal_ai_filter.value.product_results;
})

export const RemoveTierCartItem = (index:number) => {
  proposal.value.it_service_items.splice(index,1);
}
export const DuplicateTierCartItem = (svc_cart_item:any) => {
  proposal.value.it_service_items.push({...svc_cart_item});
}
export const FindMetrics = (proposal_id:number) => {
  return metrics.value.find(m => m.proposal_id == proposal_id);
}
export const IsProposalSigned = (the_proposal:any) => {
  return the_proposal.status == 'accepted'
}
export const AreContractPagesDisplayed = (the_proposal:any) => {
  return the_proposal.show_contract_pages == 1
}

// These are just for pre-filtering user prompt
export const FindBrandsFromPrompt = async (token:string) => {
  const brands_list = [...my_manufacturers.value].slice(1);
  const brandCount = brands_list.length;
  const sys_prompt = (`
    Detect if any printer brand is mentioned by the user. Return ONLY the brand ID number or 0.

    VALID BRANDS:
    ${brands_list.map(b => `${b.id}=${b.name}`).join(', ')}

    EXAMPLES:
    ${brandCount > 0 ? `"Customer wants a ${brands_list[0].name} printer" -> ${brands_list[0].id}` : ''}
    ${brandCount > 1 ? `"They're interested in ${brands_list[1].name} copiers" -> ${brands_list[1].id}` : ''}
    ${brandCount > 2 ? `"Client prefers ${brands_list[2].name} for their office" -> ${brands_list[2].id}` : ''}
    "Looking for a reliable printer" -> 0
    "Need a fast printer" -> 0

    RULES:
    - Return ONLY ONE brand ID (first mentioned if multiple)
    - Return 0 if no brand mentioned

    Return ONLY the brand ID number or "0". no explanation, no formatting.
  `);
  const user_prompt = `Here's what the user said: "${proposal_ai_filter.value.user_prompt.toLowerCase().trim()}"`;
  return await Prompt(token,sys_prompt,user_prompt);
}
export const FindConnectivityFromPrompt = async (token:string) => {
  const sys_prompt = `
    Detect Connectivity Features from what the customer said. Return ONLY comma-separated numbers.

    FEATURES:
    1=Bluetooth, 2=Wi-Fi Direct (no router), 3=USB, 4=Wi-Fi (needs router), 5=Ethernet

    KEY RULES:
    - "wifi direct" alone -> 2 only
    - "wifi and wifi direct" -> 2,4
    - "wireless" default -> 4
    - "wireless without router/network" -> 2
    - Nothing found -> 0

    EXAMPLES:
    "bluetooth and USB" -> 1,3
    "wifi direct" -> 2
    "wireless and ethernet" -> 4,5
    "wifi and wifi direct" -> 2,4
    "basic copier" -> 0

    Return CSV only, no explanation, no formatting.;
  `
  const user_prompt = `Here's what the customer said: "${proposal_ai_filter.value.user_prompt.toLowerCase().trim()}"`;
  const connectivity_ids = await Prompt(token,sys_prompt,user_prompt);

  if (connectivity_ids != 0) {
    try {
      const connectivity_list = <any>[];
      String(connectivity_ids).split(',').forEach(cxn_id => {
        connectivity_list.push(
          connect_filter_items.value.find(cf => String(cf.id) == cxn_id)
        );
      });
      return connectivity_list;
    } catch (error) {
      return [];
    }
  }

  // If AI returns zero, meaning it didn't find any indication
  // that the user specified any connectivity requirements
  else {
    return [];
  }
}
export const FindColorFromPrompt = async (token:string) => {
  const sys_prompt = `
    Detect if customer wants color or monochrome printer. Return ONLY one word.

    RETURN VALUES:
    - "Color" if customer wants color printing
    - "B&W" if customer wants black and white/monochrome only
    - "0" if customer didn't specify color preference

    KEY INDICATORS:
    Color: "color printer", "print in color", "colored documents", "full color", "needs color"
    B&W: "black and white", "monochrome", "b&w", "grayscale", "no color needed", "just b&w"

    EXAMPLES:
    "Customer wants a color printer for their marketing materials" -> Color
    "They said black and white printing is fine" -> B&W
    "Looking for office printer with good speed" -> 0
    "Client mentioned monochrome is enough" -> B&W
    "They need to print colored graphics" -> Color
    "Customer: we only print invoices and documents" -> 0
    "Customer said: we need color for our brochures" -> Color

    Return ONLY: Color, B&W, or 0. No explanation, no formatting.
  `
  const user_prompt = `Here's what the customer said: "${proposal_ai_filter.value.user_prompt.toLowerCase().trim()}"`;
  return await Prompt(token,sys_prompt,user_prompt);
}
export const FindAvgPrintFromPrompt = async (token:string) => {
  const sys_prompt = `
    Extract monthly print volume from what the user said. Return ONLY the number.

    EXAMPLES:
    "Customer prints 10000 pages a month" -> 10000
    "They do about 5k pages monthly" -> 5000
    "Around 2500 prints per month" -> 2500
    "Customer said: we print 15,000 pages every month" -> 15000
    "Looking for a fast printer" -> 0

    Return ONLY the number. If not specified, return 0.
  `
  const user_prompt = `Here's what the user said: "${proposal_ai_filter.value.user_prompt.toLowerCase().trim()}"`;
  return await Prompt(token,sys_prompt,user_prompt);
}
export const FindPpmSpeedFromPrompt = async (token:string) => {
  const sys_prompt = `
    Extract minimum print speed requirement from salesperson's description. Return ONLY the number (pages per minute).

    EXAMPLES:
    "Customer needs 30 ppm" -> 30
    "They want at least 25 pages per minute" -> 25
    "Need 50 ppm speed" -> 50
    "Customer wants fast printing, around 35 ppm" -> 35
    "They said 40 ppm for color" -> 40
    "Looking for a reliable printer" -> 0

    Return ONLY the number. If not specified, return 0.
  `
  const user_prompt = `Here's what the user said: "${proposal_ai_filter.value.user_prompt.toLowerCase().trim()}"`;
  return await Prompt(token,sys_prompt,user_prompt);
}
export const FindCopierSizeFromPrompt = async (token:string) => {
  const sys_prompt = (`
    You are a printer size identification assistant for sales quotes. Your task is to analyze user's request and identify which printer paper sizes they need.

    REFERENCE DATA:
    - A5: 5.8 x 8.3 inches (148 x 210 mm) - Small format
    - A4/Letter: 8.3 x 11.7 inches / 8.5 x 11 inches (210 x 297 mm / 216 x 279 mm) - Standard office size
    - Legal: 8.5 x 14 inches (216 x 356 mm) - US legal size
    - A3/Tabloid: 11.7 x 16.5 inches / 11 x 17 inches (297 x 420 mm / 279 x 432 mm) - Large format
    - A2: 16.5 x 23.4 inches (420 x 594 mm) - Extra large format
    - A1: 23.4 x 33.1 inches (594 x 841 mm) - Very large format
    - A0: 33.1 x 46.8 inches (841 x 1189 mm) - Largest standard format
    - 24-inch: 24 inches wide - Small wide format
    - 36-inch: 36 inches wide - Medium wide format
    - 44-inch: 44 inches wide - Large wide format
    - 60-inch+: 60 inches or wider - Extra large wide format

    VALID OUTPUT VALUES (use EXACTLY as written):
    A5
    A4/Letter
    Legal
    A3/Tabloid
    A2
    A1
    A0
    24-inch
    36-inch
    44-inch
    60-inch+

    IMPORTANT EQUIVALENCIES:
    - A4 and Letter (8.5x11) → return "A4/Letter"
    - A3 and Tabloid (11x17) → return "A3/Tabloid"
    - "Desktop printer" or "office printer" typically means A4/Letter or Legal max
    - "Wide format" or "plotter" typically means 24-inch or larger
    - Dimensions in inches or mm should match closest size

    INSTRUCTIONS:
    1. Analyze the user's request for paper size requirements
    2. Identify ALL mentioned sizes or implied sizes
    3. Return ONLY a comma-separated list using the EXACT strings from "VALID OUTPUT VALUES" above
    4. Match the exact casing and formatting (e.g., "A4/Letter" not "a4/letter" or "A4-Letter")
    5. If multiple sizes are mentioned, include all of them separated by commas with no spaces
    6. If no specific size is mentioned, return an empty string (nothing)
    7. Do not include explanations, only return the CSV list or empty string

    EXAMPLES:
    User: "I want an A4 printer" → Output: A4/Letter
    User: "Need a printer that supports 8.5x11" → Output: A4/Letter
    User: "Looking for something that does 11x17" → Output: A3/Tabloid
    User: "Wide format for posters, maybe 36 inch" → Output: 36-inch
    User: "Just a standard office printer" → Output: A4/Letter,Legal
    User: "Large format, A2 or A1" → Output: A2,A1
    User: "Desktop printer for letter and legal" → Output: A4/Letter,Legal
    User: "44 inch wide format plotter" → Output: 44-inch
    User: "Any printer is fine" → Output:
    User: "I need a printer" → Output:

    Now analyze the following user request and return only the CSV list of matching sizes.
  `);
  const user_prompt = (`
    Here's the user's request: "${proposal_ai_filter.value.user_prompt.toLowerCase().trim()}"
  `);
  return await Prompt(token,sys_prompt,user_prompt);
}
export const AIResponseSummary = async (token:string) => {
  const sys_prompt = (`
    Summarize the user's printer search criteria and state that ${proposal_ai_filter.value.product_results.length} matching products were found.
    Format: "You searched for [criteria]. We found [number] products that match your search."
    If product count > 10: Add "For more accurate results, try refining your search criteria."
  `);
  const user_prompt = `Here's what the user said: "${proposal_ai_filter.value.user_prompt.toLowerCase().trim()}"`;
  return await Prompt(token,sys_prompt,user_prompt);
}


// Filter Related Only
export const color_filter_items = computed(() => {
  return [
    {title:'Select Color',value:null},
    {title:'Color',value:'Color'},
    {title:'Monochrome',value:'B&W'}
  ];
});
export const connect_filter_items = computed(() => {
  // The patterns are for string matching against
  // user prompt to be used for AI Filtering
  return [
    {
      id: 1,
      name: 'Bluetooth',
      patterns: [
        /\bbluetooth\b/i,
        /\bblue\s*tooth\b/i,
        /\bblue-tooth\b/i,
        /\bbt\s+connect/i,
        /\bbluetooth\s+connect/i,
        /\bwith\s+bluetooth\b/i,
        /\bhas\s+bluetooth\b/i,
        /\bneed\s+bluetooth\b/i,
        /\bsupport\s+bluetooth\b/i
      ]
    },
    {
      id: 2,
      name: 'Wi-Fi Direct',
      patterns: [
        /\bwi\s*fi\s+direct\b/i,
        /\bwifi\s+direct\b/i,
        /\bwi-fi\s+direct\b/i,
        /\bdirect\s+wi\s*fi\b/i,
        /\bdirect\s+wifi\b/i
      ]
    },
    {
      id: 3,
      name: 'USB',
      patterns: [
        /\busb\b/i,
        /\bu\.s\.b\.\b/i,
        /\busb\s+port/i,
        /\busb\s+connect/i
      ]
    },
    {
      id: 4,
      name: 'Wi-Fi',
      patterns: [
        /\bwi\s*fi\b/i,
        /\bwifi\b/i,
        /\bwi-fi\b/i,
        /\bwireless\b/i,
        /\bw\.i\.f\.i\.\b/i
      ]
    },
    {
      id: 5,
      name: 'Ethernet',
      patterns: [
        /\bethernet\b/i,
        /\bwired\s+network/i,
        /\blan\s+port/i,
        /\bnetwork\s+cable/i,
        /\brj45\b/i,
        /\brj-45\b/i
      ]
    }
  ]
});


// PROPOSAL CALCULATIONS ONLY
export const ITServiceLineItemSubtotal = (cart_item:any) => {
  let line_item_total = 0;

  // Iterate through each item in the tier and sum up the total
  cart_item.items.forEach((line_item:any) => {
    const line_item_cost = GetMarginPrice(
      line_item.unit_price * line_item.quantity,
      line_item.price_margin,
    );
    line_item_total += line_item_cost * ITServiceTermMultiplier(line_item.charge_type);
  });
  return line_item_total;
}
export const ITServiceTermMultiplier = (term:string) => {
  const term_obj = it_svc_terms.value.find(t => t.value === term);
  return term_obj ? +term_obj.multiplier : 1;
}
export const ITServiceLineItemOneTimeGrandTotal = () => {
  let one_time_total = 0;
  proposal.value.it_service_items.forEach((cart_item:any) => {
    cart_item.items.forEach((line_item:any) => {
      if (line_item.charge_type === 'one-time') {
        const line_item_cost = GetMarginPrice(
          line_item.unit_price * line_item.quantity,
          line_item.price_margin,
        );
        one_time_total += line_item_cost * ITServiceTermMultiplier(line_item.charge_type);
      }
    })
  });
  return one_time_total;
}
export const ITServiceLineItemRecurringGrandTotal = () => {
  let one_time_total = 0;
  proposal.value.it_service_items.forEach((cart_item:any) => {
    cart_item.items.forEach((line_item:any) => {
      if (line_item.charge_type !== 'one-time') {
        const line_item_cost = GetMarginPrice(
          line_item.unit_price * line_item.quantity,
          line_item.price_margin,
        );
        one_time_total += line_item_cost * ITServiceTermMultiplier(line_item.charge_type);
      }
    })
  });
  return one_time_total;
}
