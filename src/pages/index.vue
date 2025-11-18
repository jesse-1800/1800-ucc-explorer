
<template>
  <AppLayout>
    <template #title>Homepage</template>
    <template #content>
      <v-row class="mt-lg-5">
        <v-col cols="12" lg="8" md="8" offset-lg="2" offset-md="2">
          <v-row>
            <v-col cols="12" lg="4" md="4" sm="12">
              <LinkCard title="Create a Proposal" href="/proposals/create" icon="new-proposal"/>
            </v-col>
            <v-col cols="12" lg="4" md="4" sm="12">
              <LinkCard title="View All Proposals" href="/proposals" icon="all-proposals"/>
            </v-col>
            <v-col cols="12" lg="4" md="4" sm="12">
              <LinkCard title="Inventory" href="/pricing" icon="inventory"/>
            </v-col>
            <v-col cols="12" lg="4" md="4" sm="12">
              <LinkCard title="Financing Providers" href="/providers" icon="providers"/>
            </v-col>
            <v-col cols="12" lg="4" md="4" sm="12">
              <LinkCard title="Proposal Templates" href="/templates" icon="templates"/>
            </v-col>
            <v-col cols="12" lg="4" md="4" sm="12">
              <LinkCard title="IT Services" href="/it-services" icon="it-services"/>
            </v-col>
          </v-row>
        </v-col>
      </v-row>
    </template>
  </AppLayout>
</template>

<script setup>
import {useRouter} from "vue-router";
import LinkCard from "@/components/shared/dashboard/LinkCard.vue";

const router = useRouter()
</script>
