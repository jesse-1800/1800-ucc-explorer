<template>
  <v-app :style="theme_main_background" id="backend-app" :theme="backend_theme" class="outfit-font">
    <v-layout class="d-flex flex-column fill-height">
      <v-app-bar v-if="!hide_layout" class="layout-appbar elevation-0 border-b" prominent theme="dark" :style="theme_navbar_style">
        <v-app-bar-nav-icon variant="text" @click="ToggleSidebar"/>
        <AuthorLogoBackend color="light"/>
        <v-spacer/>

        <div class="d-flex align-center" v-if="global_loading">
          <div>Fetching data...</div>
        </div>
        <div v-else>
          <span>{{my_company_name?my_company_name:'Loading'}} &mdash; </span>
          <slot name="title"/>
        </div>

        <v-spacer/>
        <ThemeSwitcher/>
        <LoginBtn v-if="!profile"/>
        <LogoutBtn v-if="profile"/>
      </v-app-bar>

      <v-navigation-drawer v-if="!hide_layout" :style="theme_drawer_style" class="layout-sidebar" v-model="sidebar" location="left" prominent>
        <v-list v-if="profile">
          <v-list-item
            :title="profile.name"
            :subtitle="profile.email"
            :prepend-avatar="profile.picture">
            <small class="text-grey-lighten-1"><i>{{profile.role}}</i></small>
          </v-list-item>
        </v-list>

        <v-list>
          <v-list-item to="/">
            <v-list-item-title>
              <ThemeIcon icon="dashboard">Dashboard</ThemeIcon>
            </v-list-item-title>
          </v-list-item>
          <v-list-group class="proposals-list-group" value="proposals">
            <template #activator="{props}">
              <v-list-item v-bind="props">
                <ThemeIcon icon="all-proposals">Proposals</ThemeIcon>
              </v-list-item>
            </template>
            <v-list-item href="/proposals/create">
              <ThemeIcon icon="new-proposal">New Proposal</ThemeIcon>
            </v-list-item>
            <v-list-item class="d-flex align-center" to="/proposals/" :active="IsProposalPage">
              <ThemeIcon icon="all-proposals" :chip="ProposalCount('')">
                All Proposals
              </ThemeIcon>
            </v-list-item>

            <v-list-item to="/proposals/accepted">
              <ThemeIcon icon="accepted" :chip="ProposalCount('accepted')">
                Accepted
              </ThemeIcon>
            </v-list-item>
            <v-list-item to="/proposals/declined">
              <ThemeIcon icon="declined" :chip="ProposalCount('declined')">
                Declined
              </ThemeIcon>
            </v-list-item>
            <v-list-item to="/proposals/drafts">
              <ThemeIcon icon="drafts" :chip="ProposalCount('draft')">
                Drafts
              </ThemeIcon>
            </v-list-item>
          </v-list-group>
          <v-list-item to="/pricing">
            <ThemeIcon icon="inventory">Inventory</ThemeIcon>
          </v-list-item>
          <v-list-item to="/providers">
            <ThemeIcon icon="providers">Financing Providers</ThemeIcon>
          </v-list-item>
          <v-list-item to="/templates">
            <ThemeIcon icon="templates">Proposal Templates</ThemeIcon>
          </v-list-item>
          <v-list-item to="/it-services">
            <ThemeIcon icon="it-services">IT Services</ThemeIcon>
          </v-list-item>
          <v-list-group class="settings-list-group" value="settings">
            <template #activator="{props}">
              <v-list-item v-bind="props">
                <ThemeIcon icon="settings">Settings</ThemeIcon>
              </v-list-item>
            </template>
            <v-list-item style="padding-left:80px !important" to="/settings/profile"        title="My Profile"></v-list-item>
            <v-list-item style="padding-left:80px !important" to="/settings/business"       title="Business Profile"></v-list-item>
            <v-list-item style="padding-left:80px !important" to="/settings/users"          title="User Accounts" v-if="is_manager||is_admin"></v-list-item>
            <v-list-item style="padding-left:80px !important" to="/settings/proposal"       title="Proposal Setting"></v-list-item>
            <v-list-item style="padding-left:80px !important" to="/settings/custom-fields"  title="Custom Fields"></v-list-item>
            <v-list-item style="padding-left:80px !important" to="/settings/addons"         title="Addons"></v-list-item>
          </v-list-group>

          <template v-if="is_admin">
            <v-divider/>
            <v-list-item to="/partners">
              <ThemeIcon icon="partners">Partners</ThemeIcon>
            </v-list-item>
            <v-list-item to="/users">
              <ThemeIcon icon="users">Users</ThemeIcon>
            </v-list-item>
          </template>
        </v-list>

        <slot name="sidebar"/>
      </v-navigation-drawer>

      <v-main style="background:none">
        <v-card-text :class="`${hide_layout?'pa-1':''}`">
          <slot name="content"/>
        </v-card-text>
      </v-main>

      <v-footer app :class="`layout-footer border-t ${hide_layout?'pa-1':''}`" v-if="$slots.footer" :style="theme_table_style">
        <slot name="footer"/>
      </v-footer>
    </v-layout>

    <ConfirmDialog/>
    <AdminOverlay/>
    <Snackbar/>
  </v-app>
</template>

<script lang="ts" setup>
import {storeToRefs} from 'pinia';
import {useRoute} from "vue-router";
import {GlobalStore} from "@/stores/globals";
import {is_admin,my_partner_id} from "@/composables/GlobalComposables";
import {
  is_manager,
  my_company_name,
  theme_table_style,
  theme_drawer_style,
  theme_navbar_style,
  theme_main_background
} from "@/composables/GlobalComposables";

const route = useRoute();
const store = GlobalStore();
const {SetState} = store;
const IsProposalPage = computed(() => {
  return route.path == '/proposals/'
});
const {sidebar,profile,backend_theme} = storeToRefs(store);
const {global_loading,hide_layout,proposals} = storeToRefs(store);

const ToggleSidebar = () => {
  SetState({sidebar:!sidebar.value});
}
const ProposalCount = ((status:string) => {
  if (!status) {
    return proposals.value.filter(p => {
      return p.partner_id == my_partner_id.value
    }).length;
  }

  return proposals.value.filter(p => {
    return p.status == status && p.partner_id == my_partner_id.value
  }).length;
})
</script>
<style lang="scss">

</style>
