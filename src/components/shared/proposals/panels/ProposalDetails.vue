<template>
  <v-expansion-panels :style="theme_border_radius" :model-value="panels.proposal_details" @update:model-value="store.SetPanel('proposal_details',$event)" class="mb-4">
    <panel class="border" :style="theme_border_radius" icon="mdi-briefcase-edit-outline" title="Proposal Details">
      <template #icon>
        <DragHandle/>
      </template>

      <v-card>
        <v-card-text>
          <v-row>
            <v-col cols="12" lg="3" md="3" sm="12">
              <v-text-field
                hide-details
                variant="outlined"
                density="comfortable"
                label="Proposal Title"
                v-model="proposal.title"
                :loading="llm_fetching">
                <template #append-inner>
                  <v-btn
                    size="x-small"
                    color="primary"
                    text="Generate"
                    variant="outlined"
                    :loading="llm_fetching"
                    @click="NewProposalTitle">
                  </v-btn>
                </template>
              </v-text-field>
            </v-col>
            <v-col cols="12" lg="3" md="3" sm="12">
              <v-select
                hide-details
                label="Status"
                variant="outlined"
                :items="statuses"
                v-model="proposal.status"
                density="comfortable">
              </v-select>
            </v-col>
            <v-col cols="12" lg="3" md="3" sm="12">
              <v-menu
                offset-y
                min-width="auto"
                v-model="datepicker"
                transition="scale-transition"
                :close-on-content-click="false">
                <template #activator="{props}">
                  <v-text-field
                    readonly
                    hide-details
                    v-bind="props"
                    variant="outlined"
                    density="comfortable"
                    label="Expiration Date"
                    prepend-inner-icon="mdi-calendar"
                    :model-value="SimplerDate(proposal.expiry_date)">
                  </v-text-field>
                </template>
                <v-date-picker
                  hide-header
                  color="primary"
                  v-model="proposal.expiry_date"
                  @update:model-value="SetExpiryDate">
                </v-date-picker>
              </v-menu>
            </v-col>
            <v-col cols="12" lg="3" md="3" sm="12">
<!--              <v-text-field
                prepend-inner-icon="mdi-account-edit"
                :model-value="profile?.name"
                density="comfortable"
                label="Assigned To"
                variant="outlined"
                hide-details
                disabled>
              </v-text-field>-->
              <v-select
                hide-details
                item-value="id"
                label="Template"
                item-title="name"
                variant="outlined"
                :items="my_templates"
                density="comfortable"
                :return-object="false"
                v-model.number="proposal.template_id">
              </v-select>
            </v-col>
          </v-row>

          <!--LEAD INFO-->
          <v-row>
            <v-col cols="12" lg="3" md="3" sm="12">
              <v-text-field
                v-model="proposal.company_name"
                density="comfortable"
                label="Company Name"
                variant="outlined"
                hide-details>
              </v-text-field>
            </v-col>
            <v-col cols="12" lg="3" md="3" sm="12">
              <v-text-field
                v-model="proposal.first_name"
                density="comfortable"
                label="First Name"
                variant="outlined"
                hide-details>
              </v-text-field>
            </v-col>
            <v-col cols="12" lg="3" md="3" sm="12">
              <v-text-field
                v-model="proposal.last_name"
                density="comfortable"
                label="Last Name"
                variant="outlined"
                hide-details>
              </v-text-field>
            </v-col>
            <v-col cols="12" lg="3" md="3" sm="12">
              <v-text-field
                v-model="proposal.email"
                density="comfortable"
                label="Email"
                variant="outlined"
                hide-details>
              </v-text-field>
            </v-col>
          </v-row>

          <!--Cover Letter-->
          <TextEditor v-model="proposal.cover_letter" :insert="discovery_shortcode">
            <template #header>
              <v-btn
                size="small"
                class="ml-5"
                color="primary"
                text="Generate"
                variant="outlined"
                :loading="llm_fetching"
                @click="NewCoverLetter">
              </v-btn>
            </template>
            <template #label>
              <div class="w-100 d-flex align-center">
                <span class="ml-3">Cover Letter</span>
                <v-spacer/>
                <v-select
                  hide-details
                  max-width="300"
                  density="compact"
                  variant="outlined"
                  label="Shortcodes"
                  :items="all_shortcodes"
                  v-model="discovery_shortcode">
                </v-select>
              </div>
            </template>
          </TextEditor>

          <!--Custom Fields-->
          <v-divider class="my-10">Custom Fields</v-divider>
          <v-row class="mb-4">
            <v-col v-for="(field, index) in prop_custom_fields" :key="index" cols="12" lg="4" md="4" sm="12">
              <!--Select Field-->
              <template v-if="CustomField(field?.field_id).field_type=='select'">
                <v-select
                  hide-details
                  variant="outlined"
                  density="comfortable"
                  v-model="field.field_value"
                  :label="CustomField(field?.field_id).field_label"
                  :items="CustomField(field?.field_id).field_options">
                </v-select>
              </template>

              <!--Number Field-->
              <template v-else-if="CustomField(field?.field_id).field_type=='number'">
                <v-text-field
                  hide-details
                  type="number"
                  variant="outlined"
                  density="comfortable"
                  v-model="field.field_value"
                  :label="CustomField(field?.field_id).field_label">
                </v-text-field>
              </template>

              <!--Datepicker Field-->
              <template v-else-if="CustomField(field?.field_id).field_type=='date'">
                <v-text-field
                  hide-details
                  variant="outlined"
                  density="comfortable"
                  v-model="field.field_value"
                  @click="activeDatepickerIndex=index"
                  :label="CustomField(field?.field_id).field_label">
                </v-text-field>
                <v-date-picker
                  hide-details
                  type="number"
                  variant="outlined"
                  density="comfortable"
                  v-click-outside="CloseDatepicker"
                  @update:modelValue="SetDatepickerValue($event,field)"
                  v-if="activeDatepickerIndex==index">
                </v-date-picker>
              </template>

              <!--Textarea-->
              <template v-else-if="CustomField(field?.field_id).field_type=='textarea'">
                <v-textarea
                  hide-details
                  variant="outlined"
                  density="comfortable"
                  v-model="field.field_value"
                  :label="CustomField(field?.field_id).field_label">
                </v-textarea>
              </template>

              <!--Default Text Field-->
              <template v-else>
                <v-text-field
                  hide-details
                  variant="outlined"
                  density="comfortable"
                  v-model="field.field_value"
                  :label="CustomField(field?.field_id).field_label">
                </v-text-field>
              </template>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </panel>
  </v-expansion-panels>
</template>

<script setup lang="ts">
import moment from "moment";
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import type {TemplateType} from "@/types/StoreTypes";
import {GenerateTitle} from "@/composables/LLMComposable";
import {my_partner_id} from "@/composables/GlobalComposables";
import {GenerateCoverLetter} from "@/composables/LLMComposable";
import {my_custom_fields} from "@/composables/GlobalComposables";
import {all_shortcodes} from "@/composables/ProposalComposable";
import {theme_border_radius} from "@/composables/GlobalComposables";

const store = GlobalStore();
const datepicker = ref(false);
const statuses = [
  {title:'----',  value: '', props:{disabled:true}},
  {title:'Draft', value:'draft'},
  {title:'Open',  value:'open'},
  {title:'Sent',  value:'sent',  props:{disabled:true}},
  {title:'Signed',value:'signed',props:{disabled:true}}
];
const discovery_shortcode = ref("");
const my_templates = computed(() => {
  return templates.value.filter((t:any) => t.partner_id == my_partner_id.value);
});
const {getAccessTokenSilently} = useAuth0();
const activeDatepickerIndex = ref<number|null>(null);
const {
  panels,
  proposal,
  templates,
  llm_fetching,
  is_reordering,
  prop_custom_fields,

} = storeToRefs(store);

// Watcher for Expansion Panel Reordering
watch(is_reordering, (newVal) => {
  if (newVal) {
    panels.value.proposal_details = 1;
  }
});

// Watcher for Template ID changes
watch(() => proposal.value.template_id, (newId) => {
  const template = <TemplateType|undefined> my_templates.value.find((t:TemplateType)=>t.id==newId);
  proposal.value.template_html = template?.html_content;
  proposal.value.template_css = template?.css_content;
});

// local functions
const SetDatepickerValue = ($event:any,field:any) => {
  field.field_value = moment($event).format('YYYY-MM-DD');
}
const CloseDatepicker = () => {
  activeDatepickerIndex.value = null;
}
const NewCoverLetter = async () => {
  const token = await getAccessTokenSilently();
  GenerateCoverLetter(token).then(response => {
    proposal.value.cover_letter = response;
  });
}
const NewProposalTitle = async() => {
  const token = await getAccessTokenSilently();
  GenerateTitle(token).then(response => {
    proposal.value.title = response;
  });
}
const SetExpiryDate = (raw_date:any) => {
  proposal.value.expiry_date = moment(raw_date).format('YYYY-MM-DD');
}
const SimplerDate = (the_date: string) => {
  if (!the_date) {
    return `${moment().add('1','day').format('MMMM D, YYYY')} (in 1 day)`
  }
  const date = moment(the_date);
  const diff = date.diff(moment(), 'days');
  return `${date.format('MMMM D, YYYY')} (in ${diff+1} days)`;
}
const CustomField = (field_id: number | null) => {
  return <any>my_custom_fields.value.find(f => f.id === field_id) ?? {};
}
</script>
