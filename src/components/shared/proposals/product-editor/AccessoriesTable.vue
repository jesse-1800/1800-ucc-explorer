<template>
  <v-expansion-panels elevation="0" class="mt-4" model-value="1">
    <panel class="border" title="Accessory Addons" icon="mdi-tune-variant">
      <v-card>
        <v-card-text>
          <v-data-table :style="theme_table_style" :items="accessories" :headers="table_headers">
            <template v-slot:item="{item:accessory}:{item:any}">
              <tr :id="`dataset-id-${accessory.dataset_id}`">
                <td>
                  <div class="d-flex align-center">
                    <div class="mr-2 pa-1">
                      <v-img width="30" height="30" :src="AccessoryFeatImage(accessory)"/>
                    </div>
                    <div>{{ GetAccessoryObject(accessory.accessory_id).name }}</div>
                  </div>
                </td>
                <td>{{ GetAccessoryObject(accessory.accessory_id).part_number }}</td>
                <td>
                  <TableButton :style="theme_border_radius" variant="outlined" width="100" label="Add" icon="mdi-plus-circle" @onclick="AddAndCheckConflicts(accessory)" v-if="!accessory.dataset_attached" color="primary"/>
                  <TableButton :style="theme_border_radius" variant="outlined" width="100" label="Remove" icon="mdi-close-circle" @onclick="RemoveAndCheckConflicts(accessory)" v-else color="red"/>
                </td>
              </tr>
            </template>
          </v-data-table>
        </v-card-text>
      </v-card>
    </panel>
  </v-expansion-panels>
</template>

<script setup lang="ts">
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import type {DatasetType} from "@/types/StoreTypes";
import {GetDatasetObject} from "@/composables/ProductComposable";
import {accessories_path} from "@/composables/ProductComposable";
import {GetAccessoryObject} from "@/composables/ProductComposable";
import {theme_border_radius, theme_table_style} from "@/composables/GlobalComposables.ts";
const store = GlobalStore();
const {proposal} = storeToRefs(store);
const {ShowError,ShowSuccess,SetState} = store;
const table_headers = [
  { title: 'Name',       sortable: true,  value: 'accessory_name' },
  { title: 'Part Number',sortable: true,  value: 'accessory_part_no' },
  { title: 'Manage',     sortable: false, value: 'accessory_manage' },
];
const {accessories,cart_index} = defineProps<{
  accessories: DatasetType[],
  cart_index: number
}>();

// Local functions
const AccessoryFeatImage = (accessory:any) => {
  return accessories_path+GetAccessoryObject(accessory.accessory_id).featured_image
}
const AddAndCheckConflicts = async (this_accessory: DatasetType) => {
  let should_we_attach = false;
  let rules = JSON.parse(GetDatasetObject(this_accessory.dataset_id).rules);

  if (rules.length < 1) {
    ToggleAttachment(this_accessory.dataset_id,true);
    return ShowSuccess('Accessory added');
  }
  for (const rule of rules) {

    // Incompatible accessories
    if (rule.type == 'incompatible_with') {
      console.log("incompatible_with")
      // Check for incompatible accessories
      let conflicts = <any>[];
      rule.items.forEach((dataset_id:number) => {
        if (FindAttachment(dataset_id)?.dataset_attached) {
          conflicts.push({...FindAttachment(dataset_id)});
        }
      });

      // Check for conflicts and ask removal
      if (conflicts.length > 0) {
        let conflict_items_html = ("Please remove the ff. incompatible Accessories. <br>");
        conflicts.forEach((item:any) => {
          conflict_items_html += `\n- ${GetAccessoryObject(item.accessory_id).name}`
          let dataset_elem = document.querySelector(`#dataset-id-${item.dataset_id}`);
          if (dataset_elem) {
            dataset_elem.classList.add('blink-yellow');
            setTimeout(()=>{dataset_elem.classList.remove('blink-yellow')},3000);
          }
        });

        store.ShowError(conflict_items_html);

        // Break the loop cause there's a conflict
        should_we_attach = false;
        ToggleAttachment(this_accessory.dataset_id,false);
        break;
      }

      // Set attach if there's are no conflicts
      else {
        should_we_attach = true;
      }
    }

    // Checks if there's at least one accessory selected
    else if (rule.type == 'require_one') {
      console.log("require_one")
      let passed = false;
      let items_txt = "<br><br>";
      rule.items.forEach((dataset_id:number) => {
        const find_dataset = GetDatasetObject(dataset_id);
        const find_accessory = GetAccessoryObject(find_dataset.accessory_id);

        items_txt += '- '+find_accessory.name + '<br>';
        if (FindAttachment(dataset_id)?.dataset_attached) {
          passed = true;
        }
      });

      // Means at least one is selected
      if (passed) {
        should_we_attach = true;
      }

      // Means none selected, we highlight the choices
      else {
        ToggleAttachment(this_accessory.dataset_id,false);
        rule.items.forEach((dataset_id:number) => {
          let dataset_elem = document.querySelector(`#dataset-id-${dataset_id}`);
          if (dataset_elem) {
            dataset_elem.classList.add('blink-yellow');
            setTimeout(()=>{dataset_elem.classList.remove('blink-yellow')},3000);
          }
        });

        // We should abort again and ask the user to select all of them.
        // We don't want to display another 'prompt' here. It's not good
        should_we_attach = false;
        ToggleAttachment(this_accessory.dataset_id,false);
        ShowError(`This Accessory requires one of the ff.\n${items_txt}`);
        break;
      }
    }

    // Checks if all required accessories are added
    else if (rule.type == 'require_all') {
      console.log("require_all")

      // If one of the required accessories isn't attached,
      // this will result to false.
      let are_all_required_accessories_attached = true;
      rule.items.forEach((dataset_id:number) => {
        if (FindAttachment(dataset_id)?.dataset_attached == false) {
          are_all_required_accessories_attached = false;
        }
      });

      // Everything's attached, we should set to TRUE
      if (are_all_required_accessories_attached) {
        should_we_attach = true;
      }

      // One or more isn't attached, we should abort here
      else {
        // Highlight all required accessories (the missing ones)
        let items_txt = "<br><br>";
        rule.items.forEach((dataset_id:number) => {
          const find_dataset = GetDatasetObject(dataset_id);
          const find_accessory = GetAccessoryObject(find_dataset.accessory_id);

          items_txt += '- '+find_accessory.name + '<br>';
          let dataset_elem = document.querySelector(`#dataset-id-${dataset_id}`);
          if (dataset_elem) {
            dataset_elem.classList.add('blink-yellow');
            setTimeout(()=>{dataset_elem.classList.remove('blink-yellow')},3000);
          }
        });

        // Abort for now. User should add all required accessories
        // Again, weren't showing another prompt here
        should_we_attach = false;
        ToggleAttachment(this_accessory.dataset_id,false);
        ShowError(`This Accessory requires all of the ff.\n${items_txt}`);
        break;
      }
    }

    // This is dedicated only to CSS rules
    // the rules can be found inside ImageStacks.vue
    else if (rule.type == 'custom_css') {
      console.log("custom_css")
      should_we_attach = true;
    }

    // Each rules will dictate if this accessory can be attached
    ToggleAttachment(this_accessory.dataset_id,should_we_attach);
  }
}
const RemoveAndCheckConflicts = async (this_accessory:DatasetType) => {
  // Check 2 things:
  // 1. If the category requires at least 1 accessory
  // 2. If removing this accessory will cause a conflict
  // 3. If this accessory is being required by another accessory

  // This only contains the reference IDs. Not the actual raw accessories list.
  const conflicts = DatasetsThatRequiresThis(this_accessory);

  // If there are no conflicts, just unattach
  if (conflicts.length <= 0) {
    ToggleAttachment(this_accessory.dataset_id,false);
  }

  // Prompt for removal
  else {
    let conflict_items_html = "Accessories dependent on this will be removed. Continue? <br>" +
    conflicts.map((item: any) => `- ${GetAccessoryObject(item.accessory_id).name}`).join("<br>");
    const confirm_remove = await store.OpenDialog("Confirm Action",conflict_items_html)
    if (confirm_remove) {
      conflicts.forEach((item: any) => ToggleAttachment(item.dataset_id, false));
      ToggleAttachment(this_accessory.dataset_id, false);
    }
  }
}
const FindAttachment = (dataset_id:number) => {
  return accessories.find((accessory:DatasetType) => {
    return +accessory.dataset_id === +dataset_id
  });
}
const ToggleAttachment = (dataset_id:number,value:boolean) => {
  const accessory_index = accessories.findIndex((acc:any) => acc.dataset_id === dataset_id);
  if (accessory_index > -1) {
    let proposal_copy = {...proposal.value};
    proposal_copy.cart_items[cart_index].accessories[accessory_index].dataset_attached = value;
    SetState({proposal:proposal_copy});
  }
}
const DatasetsThatRequiresThis = (this_accessory:DatasetType) => {
  const list = [] as DatasetType[];

  // The 'accessories' is the one attached to the product.
  // Not the actual raw accessories, pls don't get confused.
  accessories.forEach((accessory:DatasetType) => {
    const rules = JSON.parse(GetDatasetObject(accessory.dataset_id).rules);
    rules.forEach((rule:any) => {
      if (rule.type == 'require_one' || rule.type == 'require_all') {
        rule.items.forEach((dataset_id:number) => {
          if (dataset_id == this_accessory.dataset_id && accessory.dataset_attached) {
            list.push(accessory);
          }
        });
      }
    });
  });
  return list;
}
</script>
<style>
.blink-yellow {
  animation: blinkYellow 0.8s linear 3;
}
@keyframes blinkYellow {
  0%, 100% {
    background-color: transparent;
  }
  50% {
    background-color: yellow;
  }
}
</style>
