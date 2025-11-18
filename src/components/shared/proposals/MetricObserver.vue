<template>
  <!--Only serves as metric tracker-->
</template>

<script lang="ts" setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {ProposalServer} from "@/plugins/proposal-server";

const store = GlobalStore();
const page_data = ref<any>([]);
const pdf_pages = ref<any>([]);
const save_interval = ref<any>(null);
const {view_proposal,profile} = storeToRefs(store);

const SaveMetrics = () => {
  const form = new FormData;
  form.append("metric_id", view_proposal.value?.metric?.id);
  form.append("page_data", JSON.stringify(page_data.value));

  if (!page_data.value.length) return false;

  ProposalServer(null).post('/proposals/save-metrics',form).then(res => {
    console.log("Metric Observer: ", res.data);
  });
}

watch(() => view_proposal.value, (the_proposal:any) => {
  setTimeout(() => {
    pdf_pages.value = Array.from(document.getElementsByClassName("pdf-page"));

    // build fresh structure
    page_data.value = pdf_pages.value.map((page: HTMLElement) => {
      const saved = the_proposal?.metric.page_data?.find(
        (x: any) => x.title === page.getAttribute("title")
      );
      return {
        title: page.getAttribute("title"),
        views: saved?.views ?? 0,
        seconds: saved?.seconds ?? 0,
        _start: 0, // always reset when reloading
      };
    });

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        const idx = pdf_pages.value.indexOf(entry.target as HTMLElement);
        const data = page_data.value[idx];
        if (!data) return;

        if (entry.isIntersecting) {
          data.views++;
          data._start = Date.now();
        } else if (data._start) {
          data.seconds += Math.round((Date.now() - data._start) / 1000);
          data._start = 0;
        }
      });
    }, { threshold: 0.1 });

    pdf_pages.value.forEach((p:any) => observer.observe(p));
  }, 300);
});

onMounted(() => {
  // Only run if not being viewed by a Partner/Salesperson
  if (!profile.value) {
    console.log("Metric Observer Running...");
    save_interval.value = setInterval(() => {
      SaveMetrics();
    },5000);
  } else {
    store.LogSuccess("User logged in. Metric Observer skipped")
  }
});

onUnmounted(() => {
  store.LogError("Metric Observer Stopped.");
  clearInterval(save_interval.value);
});
</script>
