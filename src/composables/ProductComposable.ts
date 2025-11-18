import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {GetOption} from "@/composables/GlobalComposables";
import type {DatasetType, ProposalType} from "@/types/StoreTypes";
import {copier_lease_category} from "@/composables/GlobalComposables";

// States
const store = GlobalStore();
const {
  datasets,
  proposal,
  products,
  accessories,
  product_pricing,
  accessory_pricing
} = storeToRefs(store);


// Image paths
export const server_base_path = import.meta.env.VITE_CATALOG_BASE_URL;
export const datasets_path = server_base_path+'/storage/uploads/datasets/';
export const products_img_path = server_base_path+'/storage/uploads/products';
export const accessories_path = server_base_path+'/storage/uploads/accessories/featured/';
export const charge_types = ref([
  // item is $100, it'll be $100/month.
  {
    title: 'Monthly (Lease)',
    value: 'monthly',
    tooltip: 'Monthly payment amount based on lease factor.',
    props: { disabled: false }
  },

  // item is $100, it'll be ($100 * lease_factor)/month.
  {
    title: 'Monthly (Flat Rate)',
    value: 'fixed-monthly',
    tooltip: 'Monthly payment amount as it is',
    props: { disabled: false }
  },

  // One-time payment
  {
    title: 'One-Time Charge',
    value: 'one-time',
    tooltip: 'One-time upfront charge.',
    props: { disabled: false }
  },
]);

// Shared Functions
export function GetBaseImage(product: any) {
  if (product?.base_image?.length > 0) {
    return `${products_img_path}/base-img/${product.base_image}`;
  }
  return `${products_img_path}/base-img/_placeholder_.png`;
}
export function GetProductImage(product: any) {
  if (product?.product_image?.length > 0) {
    return `${products_img_path}/product-img/${product.product_image}`;
  }
  return `${products_img_path}/product-img/_placeholder_.png`;
}
export function GetDatasetImage(accessory: any) {
  const dataset = GetDatasetObject(accessory.dataset_id);
  if (dataset.filename == null || !dataset.filename.length) {
    return null;
  }
  return datasets_path + dataset.filename;
}
export function GetAccessoriesReference(product: any) {
  const product_datasets = datasets.value.filter((dataset:any) => dataset.product_id == product.id);
  const product_accessories = <any>[];
  product_datasets.forEach((dataset: any) => {
    const find_accessory = accessories.value.find((a:any) => a.id == dataset.accessory_id);
    if (find_accessory) {
      product_accessories.push(<DatasetType>{
        qty: 1,
        price_margin: 20,
        dataset_id: dataset.id,
        dataset_attached: false,
        accessory_id: find_accessory.id,
        accessory_price: FindAccessoryPrice(find_accessory.id),
      });
    }
  });
  return product_accessories;
}
export function GetAttachedDatasets(cart_item: any) {
  return cart_item.accessories.filter((c:any) => c.dataset_attached == true);
}
export function FindProduct(product_id: number) {
  return products.value.find((p:any) => p.id == product_id);
}
export function RemoveProduct(index: number) {
  const proposal_copy = {...proposal.value}
  proposal_copy.cart_items.splice(index,1);
  store.SetState({proposal: proposal_copy});
  store.ShowSuccess('Product removed from cart');
}
export function DuplicateProduct(item:any) {
  const proposal_copy = JSON.parse(JSON.stringify(proposal.value));
  proposal_copy.cart_items.push({...item});
  store.SetState({proposal: proposal_copy});
  store.ShowSuccess('Product added to cart');
}
export function GetMarginPrice(base_price: number, price_margin: number) {
  if (!price_margin) return base_price;
  const margin_decimal = price_margin / 100;

  // Prevent divide by zero or >100% margin
  if (margin_decimal >= 1) return Infinity;

  const selling_price = base_price / (1 - margin_decimal);
  return parseFloat(selling_price.toFixed(2));
}
export function GetAccessoryObject(accessory_id:number) {
  return accessories.value.find((a:any) => a.id == accessory_id);
}
export function GetDatasetObject(dataset_id:number) {
  return datasets.value.find((a:any) => a.id == dataset_id);
}
export function GetProductsAsString() {
  // Copier Lease
  if (copier_lease_category.value) {
    let product_list = ("");
    proposal.value.cart_items.forEach((item:any) => {
      product_list += `${FindProduct(item.product.id).name},`;
    });
    return product_list.replace(/,$/,'');
  }
  // IT Service
  else {
    let it_services_names = "";
    proposal.value.it_service_items.forEach((svc_cart_item:any) => {
      svc_cart_item.items.forEach((svc_item:any) => {
        it_services_names += svc_item.name + ',';
      });
    });
    return it_services_names.replace(/,$/,'');
  }
}
export function FindProductPrice(product_id:number) {
  try {
    const find_product = <any>product_pricing.value.find((p: any) => p['id'] == product_id);
    return find_product ? SafeParsePrice(find_product['Price']) : 0;
  } catch (error){
    return 0;
  }
}
export function FindCostPerPrint(color:string,product_id:number) {
  let find_product = <any>product_pricing.value.find((p:any) => p['id'] == product_id);
  let find_cpp = 0;
  if (find_product != undefined) {
    const sheet_cpp = parseFloat(find_product[`Cost Per Print ${color}`]);

    // If CPP is zero, we'll use the default Global CPP
    if (sheet_cpp <= 1) {
      find_cpp = <any>GetOption(`cost_per_print_${color.toLowerCase()}`) ?? 0;
    }
  }
  return find_cpp;
}
export function FindAccessoryPrice(accessory_id:number) {
  const price = <any>accessory_pricing.value.find((a: any) => a['id'] == accessory_id);
  if (price != undefined) {
    return SafeParsePrice(price['Price']);
  }
  return 0;
}
export function FindProductSKU(product_id:number) {
  const find_product = <any>product_pricing.value.find((p:any)=>p['id']==product_id);
  return find_product?.['SKU'] || "-";
}
export function FindAccessorySKU(accessory_id:number) {
  const accessory = <any>accessory_pricing.value.find((a:any)=>a['id']==accessory_id);
  return accessory?.['SKU'] || "-";
}
export function SafeParsePrice(val:string) {
  if (!val) return 0;
  const cleaned = val
  .toString()
  .replace(/[^0-9.,-]/g, '')         // remove all but digits, dots, commas, minus
  .replace(/,(?=\d{3}\b)/g, '')      // remove thousand commas like 1,000
  .replace(/(?<=\d),(?=\d{2}\b)/, '.') // replace decimal comma with dot
  const num = parseFloat(cleaned);
  return isNaN(num) ? 0 : num;
}

// Addons
export function RemoveAddon(index: number) {
  const newProposal = {...proposal.value};
  newProposal.cost_addons.splice(index,1);
  store.SetState({proposal: newProposal});
}

// Lease Factors
export function GetLeaseFactor(the_proposal:ProposalType, custom_term:any = null, custom_type:any = null) {
  const raw_object = toRaw(the_proposal.lease_factor_provider);
  if (!the_proposal.lease_factor_provider || Object.keys(raw_object).length === 0) return 0;

  const lease_term = custom_term ?? the_proposal.lease_term_offered;
  const lease_type = custom_type ?? the_proposal.lease_type;

  const lease_factor_object = the_proposal.lease_factor_provider.lease_factors.find(
    (factor:any) => factor.term === +lease_term
  );
  if (lease_factor_object !== undefined) {
    return lease_factor_object[lease_type];
  }
  return 0;
}

// Printing Costs (Global)
export function GlobalBlackPrintTotalCost(the_proposal:ProposalType) {
  if (the_proposal.prints_included_free == 1) return 0;
  const {
    charge_type,
    black_prints_cost,
    black_prints_margin,
    black_prints_included
  } = the_proposal.global_print_cost;

  let base_cost = black_prints_included * black_prints_cost;
  if (charge_type == 'monthly') {
    base_cost = base_cost * GetLeaseFactor(the_proposal);
  }

  return GetMarginPrice(base_cost, black_prints_margin).toFixed(2);
}
export function GlobalColorPrintTotalCost(the_proposal:ProposalType) {
  if (the_proposal.prints_included_free == 1) return 0;
  const {
    charge_type,
    color_prints_cost,
    color_prints_margin,
    color_prints_included
  } = the_proposal.global_print_cost;

  let base_cost = color_prints_included * color_prints_cost;
  if (charge_type == 'monthly') {
    base_cost = base_cost * GetLeaseFactor(the_proposal);
  }

  return GetMarginPrice(base_cost, color_prints_margin).toFixed(2);
}

// Printing Costs (Per Copier)
export function CopierBlackPrintTotalCost(the_proposal:ProposalType, item:any) {
  if (the_proposal.prints_included_free == 1) return 0;
  const {charge_type,black_prints_cost,black_prints_margin,black_prints_included} = item.print_cost;
  const base_cost = black_prints_included * black_prints_cost;

  // If it's monthly, we can multiply by lease factor
  if (charge_type == 'monthly') {
    const lease_factor = GetLeaseFactor(the_proposal);
    return (GetMarginPrice(base_cost, black_prints_margin) * lease_factor).toFixed(2)
  }
  // For monthly and one-time, just return the flat rate
  else {
    return GetMarginPrice(base_cost, black_prints_margin).toFixed(2);
  }
}
export function CopierColorPrintTotalCost(the_proposal:ProposalType, item:any) {
  if (the_proposal.prints_included_free == 1) return 0;
  const {charge_type,color_prints_cost,color_prints_margin,color_prints_included} = item.print_cost;
  const base_cost = color_prints_included * color_prints_cost;

  // If it's monthly, we can multiply by lease factor
  if (charge_type == 'monthly') {
    const lease_factor = GetLeaseFactor(the_proposal);
    return (GetMarginPrice(base_cost, color_prints_margin) * lease_factor).toFixed(2)
  }
  // For monthly and one-time, just return the flat rate
  else {
    return GetMarginPrice(base_cost, color_prints_margin).toFixed(2);
  }
}

// Addon Costs
export function TotalAddonOneTimeCost(the_proposal:ProposalType) {
  let addon_total = 0;
  the_proposal.cost_addons.forEach((addon:any) => {
    if (addon.charge_type == 'one-time') {
      addon_total += SingleAddonTotal(the_proposal, addon);
    }
  });
  return addon_total;
}
export function TotalAddonMonthlyCost(the_proposal:ProposalType) {
  let addon_total = 0;
  the_proposal.cost_addons.forEach((addon:any) => {
    if (addon.charge_type == 'monthly' || addon.charge_type == 'fixed-monthly') {
      addon_total += SingleAddonTotal(the_proposal, addon);
    }
  });
  return addon_total;
}

// Frontend Composable
export function SingleEquipmentMonthlyCost(the_proposal:ProposalType, cart_item:any) {
  let cart_item_total = 0;

  // Pluck variables for readability
  const {
    lease_factor_provider,
    lease_term_offered,
    lease_type
  } = the_proposal;

  // The product itself
  const product_margin_price = GetMarginPrice(cart_item.product.price,cart_item.product.price_margin);
  cart_item_total += (product_margin_price * cart_item.product.qty);

  // The product's attached accessories
  cart_item.accessories.forEach((attachment:any) => {
    if (attachment.dataset_attached) {
      const accessory_margin_price = GetMarginPrice(
        attachment.accessory_price * attachment.qty,
        attachment.price_margin
      );
      cart_item_total += accessory_margin_price;
    }
  });

  // Find the lease factor object
  const lease_factor_object = lease_factor_provider.lease_factors.find(
    (factor:any) => factor.term === +lease_term_offered
  );

  // This copier's monthly lease/cost
  const copier_monthly_cost = (cart_item_total * lease_factor_object[lease_type]);

  return copier_monthly_cost.toFixed(2);
}
export function SingleEquipmentOneTimeCost(cart_item:any) {
  let cart_item_total = 0;

  // The product itself
  const product_margin_price = GetMarginPrice(cart_item.product.price,cart_item.product.price_margin);
  cart_item_total += (product_margin_price * cart_item.product.qty);

  // The product's attached accessories
  cart_item.accessories.forEach((attachment:any) => {
    if (attachment.dataset_attached) {
      const accessory_margin_price = GetMarginPrice(attachment.accessory_price,attachment.price_margin);
      cart_item_total += (accessory_margin_price * +cart_item.product.qty);
    }
  });

  return cart_item_total.toFixed(2);
}
export function SingleAddonTotal(the_proposal:ProposalType, addon:any) {
  let addon_total = 0;
  const addon_margin_price = GetMarginPrice(addon.price * addon.qty,addon.price_margin);
  if (addon.charge_type == 'monthly') {
    addon_total += (addon_margin_price * GetLeaseFactor(the_proposal));
  } else {
    addon_total += addon_margin_price;
  }
  return addon_total;
}

export function TotalEquipmentCost(the_proposal:ProposalType) {
  let overall_total = 0;
  the_proposal.cart_items.forEach((item:any) => {

    // Only add to monthly if charge type is 'monthly'
    if (item.product.charge_type == 'monthly') {
      overall_total += GetMarginPrice(
        item.product.price * item.product.qty,
        item.product.price_margin
      );

      // The product's attached accessories
      GetAttachedDatasets(item).forEach((accessory:any) => {
        overall_total += GetMarginPrice(
          accessory.accessory_price * item.product.qty,
          accessory.price_margin
        );
      });
    }
  });
  return overall_total;
}

// One-time and Monthly Print Costs
export function TotalMonthlyPrintCost(the_proposal:ProposalType) {
  if (!the_proposal) return 0;

  if (the_proposal.prints_included_free == 1) return 0;

  // For GLOBAL/SHARED print cost calculation
  if (the_proposal.is_global_print_cost == 1) {
    // This can either be flat or multiplied by lease factor
    let global_print_base_cost = 0;

    // If it's a "one-time" cost, do not include to monthly
    if (the_proposal.global_print_cost.charge_type == 'one-time') {
      return 0;
    }
    global_print_base_cost += Number(GlobalBlackPrintTotalCost(the_proposal));
    global_print_base_cost += Number(GlobalColorPrintTotalCost(the_proposal));

    return global_print_base_cost;
  }

  // For PER-COPIER print cost calculation
  else {
    let per_copier_base_cost = 0;
    the_proposal.cart_items.forEach((cart_item:any) => {
      if (cart_item.print_cost.charge_type != 'one-time') {
        per_copier_base_cost += Number(CopierBlackPrintTotalCost(the_proposal, cart_item));
        per_copier_base_cost += Number(CopierColorPrintTotalCost(the_proposal, cart_item));
      }
    });
    return per_copier_base_cost;
  }
}

// This function reverses the monthly total so we can use it
// to display different lease terms inside LeaseDetails.vue
export function LeaseTermOption(the_proposal:ProposalType, lease_term:number, lease_type:string) {
  // I like how this feels like a hack, but it's actually mathematically correct
  // I don't need to break all the existing functions that are already working correctly
  const base_total = OfferedMonthlyCost(the_proposal) / GetLeaseFactor(the_proposal);
  return (base_total * GetLeaseFactor(the_proposal, lease_term, lease_type)).toFixed(2);
}

// Grand Totals
export function OfferedOneTimeCost(the_proposal:ProposalType) {
  if (!the_proposal) return 0;
  let one_time_grand_total = 0;

  // Add the one-time addons
  one_time_grand_total += TotalAddonOneTimeCost(the_proposal);

  // Add one-time equipments
  the_proposal.cart_items.forEach((cart_item:any) => {
    if (cart_item.product.charge_type == 'one-time') {
      one_time_grand_total += Number(SingleEquipmentOneTimeCost(cart_item));
    }
  });

  // Add the print costs if it's one-time
  if (the_proposal.is_global_print_cost == 1) {
    if (the_proposal.global_print_cost.charge_type == 'one-time') {
      one_time_grand_total += Number(GlobalBlackPrintTotalCost(the_proposal));
      one_time_grand_total += Number(GlobalColorPrintTotalCost(the_proposal));
    }
  } else {
    the_proposal.cart_items.forEach((cart_item:any) => {
      if (cart_item.print_cost.charge_type == 'one-time') {
        one_time_grand_total += Number(CopierBlackPrintTotalCost(the_proposal, cart_item));
        one_time_grand_total += Number(CopierColorPrintTotalCost(the_proposal, cart_item));
      }
    });
  }

  return one_time_grand_total;
}
export function OfferedMonthlyCost(the_proposal:ProposalType) {
  if (!the_proposal) return 0;
  const lease_factor = GetLeaseFactor(the_proposal);
  const maintenance_cost = TotalMonthlyPrintCost(the_proposal);
  const addon_monthly_cost = TotalAddonMonthlyCost(the_proposal);
  const equipment_monthly_cost = TotalEquipmentCost(the_proposal) * lease_factor;

  return (equipment_monthly_cost + maintenance_cost + addon_monthly_cost);
}
