<template>
  <v-app
    class="outfit-font"
    :theme="frontend_theme"
    :style="{'--partner-brand-color': get_brand_color}">
    <v-layout class="d-flex flex-column fill-height">
      <!--Navbar-->
      <v-app-bar class="view-prop-header border-b" elevation="0" prominent>
        <template v-if="props.preview_hash">
          <v-btn @click="ToggleModal('preview_proposal',false)" icon="mdi-close"/>
          <v-btn @click="InitProposalPage" icon="mdi-refresh"/>
          <v-btn @click="$emit('fullscreen')" icon="mdi-fullscreen"/>
        </template>
        <v-app-bar-nav-icon v-else class="ml-3" variant="text" @click="sidebar=!sidebar"/>
        <v-spacer/>

        <Filler width="150" type="text">
          <span class="d-none d-md-inline">
            <v-icon class="mr-1">mdi-playlist-edit</v-icon>
            {{ view_proposal?.title }}
          </span>
          <span class="ml-1" v-if="props.preview_hash">(PREVIEW)</span>
        </Filler>

        <v-spacer/>

        <v-btn @click="store.SetState({frontend_theme:'dark'})" v-tooltip="`Switch to Dark Theme`" icon="mdi-lightbulb-on-outline"  v-if="frontend_theme==='light'"/>
        <v-btn @click="store.SetState({frontend_theme:'light'})" v-tooltip="`Switch to Light Theme`" icon="mdi-brightness-4" v-else></v-btn>

        <AuthorLogoFrontend/>
      </v-app-bar>

      <!--Sidebar-->
      <v-navigation-drawer class="view-prop-sidebar" width="350" v-model="sidebar" location="left" prominent>
        <Filler class="ml-5" width="300" type="text,image"/>

        <v-list>
          <template v-for="(group, g) in grouped_links" :key="g">
            <!--For Pages with Children pages-->
            <v-list-group v-if="group.children.length" :value="group.title">
              <template #activator="{ props }">
                <v-list-item v-bind="props">
                  <template #prepend>
                    <v-icon @click.stop="ScrollToView(group.page)">mdi-file-document-outline</v-icon>
                  </template>
                  <v-list-item-title @click.stop="ScrollToView(group.page)">
                    {{ group.title }}
                  </v-list-item-title>
                </v-list-item>
              </template>
              <v-list-item
                :key="i"
                :title="child.title"
                @click="ScrollToView(child)"
                v-for="(child, i) in group.children">
              </v-list-item>
            </v-list-group>

            <!--For Pages with no children-->
            <v-list-item
              v-else
              :title="group.title"
              @click="ScrollToView(group.page)"
              prepend-icon="mdi-file-document-outline">
            </v-list-item>
          </template>
        </v-list>
      </v-navigation-drawer>

      <!--Main-->
      <v-main class="view-prop-main" :style="`background:${frontend_theme=='light'?'#c3ccdc':'unset'}`">
        <template v-if="view_proposal">
          <div id="view-proposal" v-html="view_proposal.template_html"></div>
        </template>
        <template v-else>
          <div id="view-proposal">
            <div class="pdf-page" style="padding: 50px">
              <Filler class="mb-5" width="100%" type="text,image"/>
              <Filler class="mb-5" width="100%" type="text,image"/>
              <Filler class="mb-5" width="100%" type="text,image"/>

              <h1 class="text-center" v-if="!global_loading && has_fully_loaded">
                <span><v-icon>mdi-emoticon-sad-outline</v-icon></span>
                <br>
                <span>This proposal may have been expired or deleted.</span>
              </h1>
            </div>
          </div>
        </template>

        <ZoomButtons/>
      </v-main>

      <!--Footer-->
      <v-footer app class="view-prop-footer border-t">
        <Filler width="150" type="text">
          <div v-if="OfferedMonthlyCost(view_proposal)>0">
            <small class="d-none d-md-block">Monthly Payment</small>
            <div style="font-size:20px">
              <b>${{OfferedMonthlyCost(view_proposal).toFixed(2)}}</b> / month
            </div>
          </div>
        </Filler>

        <v-spacer/>

        <footer-btns
          @accept="AcceptProposal"
          @decline="DeclineProposal">
        </footer-btns>
      </v-footer>

      <!--Modals and Overlays-->
      <Overlay/>
      <MetricObserver/>
      <AcceptModal v-if="view_prop_config?.accept_modal"/>

    </v-layout>
    <Snackbar/>
  </v-app>
</template>

<script lang="ts" setup>
import {storeToRefs} from 'pinia';
import {useRoute} from "vue-router";
import {GlobalStore} from "@/stores/globals";
import {ProposalServer} from "@/plugins/proposal-server";
import {OfferedMonthlyCost} from "@/composables/ProductComposable";
import {get_brand_color,ToggleModal} from "@/composables/GlobalComposables";
import {AreContractPagesDisplayed, IsProposalSigned} from "@/composables/ProposalComposable.ts";

const route = useRoute();
const sidebar = ref(true);
const store = GlobalStore();
const page_links = ref(<any>[]);
const has_fully_loaded = ref(false);
const {SetState,FetchCatalogData} = store;
const props = defineProps(['preview_hash']);
const grouped_links = computed(() => {
  return page_links.value.reduce((acc:any,page:any) => {
    let parent = acc.find((g:any) => page.title.startsWith(g.title) && page.title !== g.title)
    if (!parent) {
      parent = { title: page.title, page, children: [] }
      acc.push(parent)
    } else {
      parent.children.push(page)
    }
    return acc
  },[])
});

const {
  profile,
  view_proposal,
  global_loading,
  frontend_theme,
  view_prop_config
} = storeToRefs(store);

const Loading = (value:boolean) => {
  SetState({global_loading:value});
}
const ScrollToView = (page_object:HTMLElement) => {
  const page = document.querySelector(`#${page_object.id}`);
  if (page) {
    page.scrollIntoView({behavior: "smooth"});
  }
}
const AcceptProposal = () => {
  store.SetState({
    view_prop_config: {
      ...store.view_prop_config,
      accept_modal: true
    }
  });
}
const DeclineProposal = () => {
  if (confirm("Are you sure you want to decline this proposal?")) {
    SetState({global_loading:true});
    const server = ProposalServer(null);
    const form = new FormData();
    form.append("proposal_id", view_proposal.value.id);
    server.post('/proposals/decline',form).then(() => {
      SetState({global_loading:false});
      location.reload();
    });
  }
}
const LoadProposalInfo = async() => {
  let {hash} = <any>route.params;
  if (props.preview_hash) {
    hash = props.preview_hash;
  }
  const form = new FormData();
  form.append("hash_code", hash);
  Loading(true);

  // For the sake of letting PHP know that im logged in. I'm passing a fake token
  // I ain't passing the actual token but tricking it to think im logged in
  // This isn't bypassing the authentication, it's just for page_views counter.
  const token = profile.value? 'logged-in' : null;
  return ProposalServer(token).post('/proposals/finder',form).then(res=>{
    // Set the proposal data in pinia store
    SetState({view_proposal:res.data});
  });
}
const InitProposalPage = async () => {
  Loading(true);
  page_links.value = [];
  await FetchCatalogData();
  await LoadProposalInfo().finally(()=>{
    Loading(false);
    has_fully_loaded.value = true;
    const pdf_style = document.createElement('style');

    // Load the css_content into body (only way)
    document.getElementById('contract-page-hide-style')?.remove();
    pdf_style.id = 'contract-page-hide-style';
    pdf_style.innerHTML = <string>view_proposal.value?.template_css;

    // Hide regular & contract pages if specified in proposal settings
    if (!AreContractPagesDisplayed(view_proposal.value)) {
      pdf_style.innerHTML += '.contract-page{display: none !important}';
    }

    // Check each page display properties
    view_proposal.value?.contract_pages.forEach((page: any) => {
      // Hide if display is false
      if (page.display == false) {
        pdf_style.innerHTML += `#${page.id}{display: none !important}`;
      }
      // Also hide if proposal is signed AND hide_if_signed is true
      else if (IsProposalSigned(view_proposal.value) && page.hide_if_signed) {
        pdf_style.innerHTML += `#${page.id}{display: none !important}`;
      }
    });

    document.head.appendChild(pdf_style);

    // Expose Accept Proposal outside vue
    (window as any).AcceptProposal = AcceptProposal;
    (window as any).ToggleLeaseOptions = ToggleLeaseOptions;

    // Read title attr from pages to make navbar links
    const pdf_pages = document.querySelectorAll('.pdf-page');

    // Grab the titles to use them into sidebar menus
    pdf_pages.forEach((page,index) => {
      const title = page.getAttribute('title');
      if (title) {
        // skip if contract page is hidden
        if (page.classList.contains('contract-page')) {
          // Immediately hide contract pages
          if (!AreContractPagesDisplayed(view_proposal.value)) {
            return;
          }
        }

        // Otherwise, check each regular/contract pages if should display.
        if (IsPageVisible(page.id)) {
          page_links.value.push({
            title: title,
            index: index,
            id: page.id,
          });
        }
      }
    });
  });
}
const ToggleLeaseOptions = () => {
  const lease_options = document.querySelectorAll('.lease-term-options');
  const term_indicator = document.querySelector('#term-indicator');

  if (lease_options) {
    lease_options.forEach((lease_option) => {
      lease_option.classList.toggle('d-none');
      if (lease_option.classList.contains('d-none')) {
        term_indicator?.classList.remove('mdi-chevron-up');
        term_indicator?.classList.add('mdi-chevron-down');
      } else {
        term_indicator?.classList.remove('mdi-chevron-down');
        term_indicator?.classList.add('mdi-chevron-up');
      }
    })
  }
}
const IsPageVisible = (page_id: string) => {
  const find_page = view_proposal.value?.contract_pages.find((page: any) => {
    return page.id === page_id;
  });

  if (find_page) {
    // display=false then directly hide
    if (find_page.display == false) {
      return false;
    }
    // display=true, proposal=signed, hide_if_signed=true then hide
    else if (IsProposalSigned(view_proposal.value) && find_page.hide_if_signed) {
      return false;
    }

    // Otherwise, page is visible
    return true;
  }

  return false;
};

onMounted(async () => {
  // Initialize the proposal page
  InitProposalPage();
});
</script>

<style lang="scss">
@use "@/styles/grapesjs-actual.scss" as *;
@mixin css_gradient() {
  background: #ffe0ed;
  background: linear-gradient(180deg,rgba(255, 224, 237, 1) 0%, rgba(207, 229, 255, 1) 100%) !important;
}

.view-prop-header,
.view-prop-sidebar,
.view-prop-footer {
  position: fixed !important;
  max-height:fit-content;
}
</style>
