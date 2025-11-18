<template>
  <my-modal-frontend
    @close="CloseModal"
    :color="get_brand_color"
    v-model="config.accept_modal"
    max_width="600">
    <template #title>
      <v-icon>mdi-file-sign</v-icon>
      <span class="ml-2">Accept Proposal</span>
    </template>

    <!--Name of whoever accepted-->
    <v-text-field
      v-model="view_proposal.accepted_by"
      label="Your Full Name" variant="outlined">
    </v-text-field>

    <!--Initials-->
    <v-row>
      <v-col cols="12" lg="6" md="6" sm="12">
        <v-text-field
          v-model="initials_model"
          :disabled="!view_proposal.accepted_by"
          label="Your Initials" variant="outlined">
        </v-text-field>
      </v-col>
      <v-col cols="12" lg="6" md="6" sm="12">
        <canvas class="border border-opacity-25 w-100" id="initials" height="60"></canvas>
      </v-col>
    </v-row>

    <!--Signature Canvas-->
    <div class="d-flex justify-space-between align-center">
      <v-label>Draw your signature below:</v-label>
      <v-btn :color="get_brand_color" @click="ResetCanvas" class="mb-1" prepend-icon="mdi-restart" size="small">Reset</v-btn>
    </div>
    <div class="canvas-wrapper w-100">
      <canvas class="bg-grey-lighten-4 border rounded-lg" id="canvas"></canvas>
    </div>

    <!--Confirm tick box-->
    <v-checkbox-btn
      class="mt-5 mb-5"
      v-model="has_agreed"
      label="
        I confirm that I have reviewed and accept this proposal,
        and would like to proceed to final paperwork,
        subject to credit approval.
      ">
    </v-checkbox-btn>

    <template #footer>
      <v-spacer/>
      <v-btn
        :color="get_brand_color"
        rounded="pill"
        :loading="is_loading"
        text="Accept Proposal"
        :disabled="!has_agreed"
        @click="AcceptProposal"
        prepend-icon="mdi-checkbox-marked-outline">
      </v-btn>
      <v-spacer/>
    </template>
  </my-modal-frontend>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import moment from "moment/moment";
import SignaturePad from "signature_pad";
import {GlobalStore} from "@/stores/globals";
import {ProposalServer} from "@/plugins/proposal-server";
import {get_brand_color} from "@/composables/GlobalComposables";

const store = GlobalStore();
const initials_model = ref("");
const signature_pad = ref<any>(null);
const has_agreed = ref<boolean>(false);
const is_loading = ref<boolean>(false);
const {SetState,ShowError,ShowSuccess} = store;
const {view_proposal} = <any>storeToRefs(store);
const {view_prop_config:config} = storeToRefs(store);

const CloseModal = () => {
  SetState({
    view_prop_config: {
      ...config.value,
      accept_modal: false
    }
  });
}
const AcceptProposal = () => {
  if (!has_agreed.value) return ShowError("Please tick the checkbox to proceed.");
  if (signature_pad.value.isEmpty()) return ShowError("Please draw your signature");
  if (!view_proposal.value?.accepted_by) return ShowError("Please provide your name.");
  if (!initials_model.value) return ShowError("Please provide your initials.");

  is_loading.value = true;

  // Grab initials data:image
  view_proposal.value.accepted_initials = ExportInitials();

  const form = new FormData;
  form.append('proposal_id', view_proposal.value?.id);
  form.append('accepted_by', view_proposal.value?.accepted_by);
  form.append('accepted_date', view_proposal.value?.accepted_date);
  form.append('accepted_initials', view_proposal.value?.accepted_initials);
  form.append('accepted_signature', signature_pad.value.toDataURL());

  ProposalServer('').post('/proposals/accept',form).then((res)=>{
    console.log(res.data);
    ShowSuccess("Thank you for accepting the proposal! <br> We'll get back to you shortly.");
    CloseModal();
  }).finally(()=>{
    is_loading.value = false;
    setTimeout(()=>{location.reload()},1000);
  });
}
const ResetCanvas = () => {
  signature_pad.value.clear();
}
const SyncCanvasWidth = () => {
  const updateCanvasWidth = () => {
    const wrapper = <any>document.querySelector('.canvas-wrapper');
    const canvas = <any>document.getElementById('canvas');
    if (wrapper && canvas) {
      canvas.width = wrapper.clientWidth
    }
  }

  onMounted(() => {
    updateCanvasWidth()
    window.addEventListener('resize', updateCanvasWidth)
  });
  onUnmounted(() => {
    window.removeEventListener('resize', updateCanvasWidth)
  });
}
const GetInitials = (name:string) => {
  if (!name) return "";
  return name
  .trim()
  .split(/\s+/)
  .map(word => word[0].toUpperCase())
  .join("");
}
const DrawInitials = async (initials:string) => {
  const canvas = <any>document.getElementById('initials');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  await document.fonts.load(`400 48px Caveat`);
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  ctx.font = `400 48px 'Caveat'`;
  ctx.fillStyle = '#000';
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  ctx.fillText(initials, canvas.width / 2, canvas.height / 2);
}
const ExportInitials = () => {
  const canvas = <any>document.getElementById('initials');
  if (!canvas) return null;
  return canvas.toDataURL("image/png");
}

// Watcher for getting initials
watch(() => view_proposal.value.accepted_by, async(newName) => {
  if (newName.length) {
    initials_model.value = GetInitials(newName);
    await DrawInitials(GetInitials(newName));
  }
},{immediate: true});

// Watcher for initials change, trigger drawing
watch(() => initials_model.value, async(newInitials) => {
  if (newInitials) {
    await DrawInitials(newInitials);
  }
},{immediate: true});

// Set/Unset signature pad
onMounted(() => {
  // Signature pad
  signature_pad.value = new SignaturePad(
    <any>document.querySelector("#canvas")
  );

  // Set initial values
  const proposal_copy = {...view_proposal.value}
  proposal_copy.accepted_date = moment().format('MMMM D, YYYY');
  proposal_copy.accepted_by = view_proposal.value.first_name+' '+view_proposal.value.last_name;
  SetState({view_proposal: proposal_copy});

  // Initiate drawing if name exists
  DrawInitials(initials_model.value);
});

SyncCanvasWidth();
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Caveat:wght@400;500;600&display=swap');
</style>
