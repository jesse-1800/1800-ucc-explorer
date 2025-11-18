<template>
  <my-modal
    max_width="700"
    color="transparent"
    title="Send Proposal"
    v-model="modals.send_proposal"
    @close="ToggleModal('send_proposal',false)">
    <template #content>
      <v-row>
        <v-col cols="12" lg="6" md="6" sm="12">
          <v-text-field
            hide-details
            density="comfortable"
            label="To (recipient)"
            v-model="email_form.to"
            variant="outlined">
          </v-text-field>
        </v-col>
        <v-col cols="12" lg="6" md="6" sm="12">
          <v-text-field
            hide-details
            density="comfortable"
            label="From (sender)"
            v-model="email_form.from"
            variant="outlined">
          </v-text-field>
        </v-col>
        <v-col cols="12" lg="6" md="6" sm="12">
          <v-combobox
            solo
            chips
            multiple
            clearable
            hide-details
            density="comfortable"
            label="CC (optional)"
            variant="outlined"
            v-model="email_form.cc"
            placeholder="Press enter to add"
          ></v-combobox>
        </v-col>
        <v-col cols="12" lg="6" md="6" sm="12">
          <v-text-field
            hide-details
            density="comfortable"
            label="Subject"
            v-model="email_form.subject"
            variant="outlined">
            <template #append-inner>
              <v-btn
                size="x-small"
                color="primary"
                text="Generate"
                variant="outlined"
                @click="GenerateSubject"
                :loading="llm_fetching">
              </v-btn>
            </template>
          </v-text-field>
        </v-col>
      </v-row>
      <text-editor v-model="email_form.body" :insert="email_body_shortcode">
        <template #header>
          <v-btn
            size="x-small"
            color="primary"
            text="Generate"
            variant="outlined"
            @click="GenerateBody"
            :loading="llm_fetching">
          </v-btn>
        </template>
        <template #label>
          <div class="w-100 d-flex align-center">
            <span class="ml-3">Email Body</span>
            <v-spacer/>
            <v-select
              hide-details
              max-width="300"
              density="compact"
              variant="outlined"
              label="Shortcodes"
              :items="all_shortcodes"
              v-model="email_body_shortcode">
            </v-select>
          </div>
        </template>
      </text-editor>

      <v-file-input
        multiple
        class="mt-5"
        prepend-icon=""
        variant="outlined"
        v-model="attach_files"
        append-inner-icon="mdi-tray-arrow-up"
        label="Attach files to Email (optional)">
      </v-file-input>
    </template>
    <template #footer>
      <v-checkbox-btn v-model="attach_pdf" label="Attach PDF to Email"/>
      <v-spacer/>
      <v-btn
        width="150"
        text="Send Proposal"
        color="primary"
        :loading="is_sending"
        @click="SendProposal"
        :style="theme_btn_style"
        class="ma-auto d-block">
      </v-btn>
    </template>
  </my-modal>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {ProposalServer} from "@/plugins/proposal-server";
import {GenerateTitle} from "@/composables/LLMComposable";
import {my_signature, theme_btn_style, ToggleModal} from "@/composables/GlobalComposables";
import {GenerateEmailBody} from "@/composables/LLMComposable";
import {all_shortcodes} from "@/composables/ProposalComposable.js";

const store = GlobalStore();
const attach_files = ref([]);
const is_sending = ref(false);
const attach_pdf = ref(false);
const email_body_shortcode = ref("");
const {SetState,ShowSuccess} = store;
const {getAccessTokenSilently} = useAuth0();
const {email_form,proposal} = storeToRefs(store);
const {modals,llm_fetching,profile} = storeToRefs(store);

// Generate the email body using LLM
const GenerateBody = async () => {
  const token = await getAccessTokenSilently();
  SetState({llm_fetching:true});
  GenerateEmailBody(token).then(data => {
    const newForm = {...email_form.value};
    newForm.body = data;
    newForm.body += my_signature.value;
    SetState({email_form:newForm});
  }).finally(() => {
    SetState({llm_fetching:false});
    llm_fetching.value = false;
  });
};

// Generate the email body using LLM
const GenerateSubject = async () => {
  const token = await getAccessTokenSilently();
  SetState({llm_fetching:true});
  GenerateTitle(token).then(data => {
    const newForm = {...email_form.value};
    newForm.subject = data;
    SetState({email_form:newForm});
  }).finally(() => {
    SetState({llm_fetching:false});
    llm_fetching.value = false;
  });
};

// Set initial values from proposal data
const SetInitialValues = () => {
  const newForm = {...email_form.value}
  newForm.to = proposal.value.email;
  newForm.from = profile.value.email;
  newForm.body = my_signature.value;
  SetState({email_form:newForm});

  // Populate only if empty
  /*if (!email_form.value.body.length) {
    GenerateBody();
  }
  if (!email_form.value.subject.length) {
    GenerateSubject();
  }*/
}

// Send the proposal!
const SendProposal = async () => {
  is_sending.value = true;
  const token = await getAccessTokenSilently();
  const form = new FormData;

  form.append('proposal',JSON.stringify(proposal.value));
  form.append('attach_pdf',attach_pdf.value ? 1 : 0);
  form.append('email_form',JSON.stringify(email_form.value));

  // Attaching external files to email
  if (attach_files.value.length) {
    attach_files.value.forEach((file, index) => {
      form.append(`files[${index}]`, file);
    });
  }

  ProposalServer(token).post('/proposals/send',form).then((res)=>{
    console.log("SendProposal: ", res.data);
    ShowSuccess("Proposal has been sent!");
    ToggleModal('send_proposal',false);
  }).finally(() => {
    is_sending.value = false;
  });
};

// Set initial values on component mount
onMounted(() => {
  SetInitialValues();
});
</script>
