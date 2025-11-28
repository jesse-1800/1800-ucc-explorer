<template>
  <MyModal max_width="700" v-model="modals.contact_form" title="Contact Information">
    <v-form ref="form_ref">
      <v-row>
        <v-col cols="12" md="6">
          <v-text-field
            hide-details
            label="First Name"
            variant="outlined"
            density="comfortable"
            :rules="[rules.required]"
            v-model="contact.firstname">
          </v-text-field>
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            hide-details
            label="Last Name"
            variant="outlined"
            density="comfortable"
            :rules="[rules.required]"
            v-model="contact.lastname">
          </v-text-field>
        </v-col>

        <v-col cols="12" md="6">
          <v-combobox
            hide-details
            label="Company"
            item-value="id"
            variant="outlined"
            :items="ucc_buyers"
            density="comfortable"
            :return-object="false"
            :disabled="edit_contact"
            :rules="[rules.required]"
            item-title="buyer_company"
            v-model="contact.buyer_id">
          </v-combobox>
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            hide-details
            label="Title"
            variant="outlined"
            density="comfortable"
            v-model="contact.title">
          </v-text-field>
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            label="Email"
            hide-details
            type="email"
            variant="outlined"
            density="comfortable"
            v-model="contact.email"
            :rules="[rules.required,rules.email]">
          </v-text-field>
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            hide-details
            label="Phone"
            variant="outlined"
            density="comfortable"
            v-model="contact.phone"
            :rules="[rules.required]">
          </v-text-field>
        </v-col>

        <v-col cols="12">
          <v-text-field
            hide-details
            label="Address"
            variant="outlined"
            density="comfortable"
            v-model="contact.address">
          </v-text-field>
        </v-col>

        <v-col cols="12" md="6">
          <v-text-field
            label="City"
            variant="outlined"
            density="comfortable"
            v-model="contact.city">
          </v-text-field>
        </v-col>

        <v-col cols="12" md="3">
          <v-text-field
            label="State"
            variant="outlined"
            density="comfortable"
            v-model="contact.state">
          </v-text-field>
        </v-col>

        <v-col cols="12" md="3">
          <v-text-field
            label="ZIP Code"
            variant="outlined"
            v-model="contact.zip"
            density="comfortable">
          </v-text-field>
        </v-col>
      </v-row>
    </v-form>
    <template #footer>
      <v-spacer></v-spacer>
      <v-btn
        color="grey"
        text="Cancel"
        variant="text"
        @click="ResetForm">
      </v-btn>
      <v-btn
        text="Save"
        color="primary"
        variant="elevated"
        @click="SubmitForm">
      </v-btn>
    </template>
  </MyModal>
</template>

<script setup>
import { ref } from 'vue';
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";

const form_ref = ref(null);
const store = GlobalStore();
const rules = {
  required: (value) => !!value || 'This field is required',
  email: (value) => {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(value) || 'Invalid email address';
  },
};
const contact = ref({
  'id':         "",
  'partner_id': "",
  'buyer_id':   "",
  'firstname':  "",
  'lastname':   "",
  'title':      "",
  'email':      "",
  'phone':      "",
  'address':    "",
  'city':       "",
  'state':      "",
  'zip':        "",
  'created_at': "",
  'updated_at': "",
});
const props = defineProps(['edit_contact']);
const {modals,ucc_buyers} = storeToRefs(store);

const SubmitForm = async () => {
  const { valid } = await form_ref.value.validate();
  if (valid) {
    console.log('Form submitted:', contact.value);
    // Add your submit logic here
  }
};
const ResetForm = () => {
  form_ref.value.reset();
  if (props.edit_contact) {
    contact.value = props.edit_contact;
  } else {
    contact.value = {
      'id':         "",
      'partner_id': "",
      'buyer_id':   "",
      'firstname':  "",
      'lastname':   "",
      'title':      "",
      'email':      "",
      'phone':      "",
      'address':    "",
      'city':       "",
      'state':      "",
      'zip':        "",
      'created_at': "",
      'updated_at': "",
    };
  }
};

// Watch for prop changes
watch(()=>props.edit_contact,(new_val)=>{
  if (new_val) {
    contact.value = props.edit_contact;
  }
},{immediate:true});

// When modal form closes, reset form.
watch(()=>modals.value.contact_form,(new_val)=>{
  if(new_val === false) {
    ResetForm();
  }
})
</script>
