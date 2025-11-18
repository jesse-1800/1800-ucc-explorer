<template>
  <v-app>
    <v-main>
      <router-view />
    </v-main>
  </v-app>
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {useRouter} from "vue-router";
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";

const store = GlobalStore();
const router = <any>useRouter();
const {sidebar} = storeToRefs(store);
const { isAuthenticated } = useAuth0();
const {getAccessTokenSilently} = useAuth0();

const DetectScreenOnResize = () => {
  const isMobile = window.innerWidth <= 768;
  store.SetState({is_mobile: isMobile});
};

onMounted(async() => {
  // Open sidebar by default
  sidebar.value = true;

  // Redirect from a dirty Auth0 URL
  const url = new URL(window.location.href);

  // Redirect if Auth0 params in URL
  const hasAuth0Params = url.searchParams.has('code') && url.searchParams.has('state');
  if (hasAuth0Params) {
    return router.push("/dashboard");
  }

  // Set global is_mobile var
  DetectScreenOnResize();
  window.addEventListener('resize', DetectScreenOnResize);
});
onUnmounted(() => {
  window.removeEventListener('resize', DetectScreenOnResize);
});


// Fetches all data when logged in
watch(isAuthenticated, async() => {
  store.global_loading = true;
  const token = await getAccessTokenSilently();
  store.FetchPartnerUsers(token).then(()=> {
    store.FetchAllData(token).then(() => {
      store.global_loading = false;
    });
  })
});


</script>
