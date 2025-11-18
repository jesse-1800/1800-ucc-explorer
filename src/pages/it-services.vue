<template>
  <AppLayout>
    <template #title>IT Services</template>
    <template #content>
      <TieredServicesTable/>
      <div class="my-10"></div>
      <ItemsServicesTable/>
    </template>
  </AppLayout>
</template>

<script lang="ts" setup>
  import {useAuth0} from "@auth0/auth0-vue";
  import {GlobalStore} from "@/stores/globals";

  const store = GlobalStore();
  const {getAccessTokenSilently} = useAuth0();

  onMounted(async () => {
    const token = await getAccessTokenSilently();
    store.FetchItServiceItemsAndTiers(token);
  });
</script>
