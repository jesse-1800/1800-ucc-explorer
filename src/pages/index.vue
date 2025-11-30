<template>
  <AppLayout>
    <template #title>Homepage</template>
    <template #content>
      <div ref="map_container" style="width: 100%; height: 500px"></div>

      <pre>{{mapped_ucc_list}}</pre>
    </template>
  </AppLayout>
</template>

<script setup>
import {storeToRefs} from "pinia";
import {GlobalStore} from "@/stores/globals";
import {state_centers} from "@/composables/GlobalComposables";

const map = ref(null);
const store = GlobalStore();
const {ucc_filings,ucc_buyers} = storeToRefs(store);
const map_container = ref(null);
const state_data = computed(() => {
  const reduced_ucc_list = [];
  ucc_filings.value.forEach(ucc_item => {
    const buyer = ucc_buyers.value.find(b=>b.id === ucc_item.buyer_id);
    reduced_ucc_list.push({
      ucc_filing_id: ucc_item.id,
      buyer_id: buyer.buyer_id,
      buyer_city: buyer.buyer_city,
      buyer_state: buyer.buyer_state,
    })
  });

  // Group of state abbrevs and UCC profiles from that state
  const grouped = {};

  // Group items by state
  reduced_ucc_list.forEach(item => {
    const state = item.buyer_state;

    // If this state doesn't exist yet, create it
    if (!grouped[state]) {
      grouped[state] = [];
    }

    // Add the item to this state's array
    grouped[state].push(item);
  });

  // Convert to array format
  const result = [];
  for (const state_name in grouped) {
    result.push({
      state_name: state_name,
      state_items: grouped[state_name]
    });
  }

  return result;
});

const InitializeMap = () => {
  // Initialize the map with transparent background
  map.value = new google.maps.Map(map_container.value, {
    center: { lat: 39.8283, lng: -98.5795 },
    zoom:    4.4,
    minZoom: 4.4,
    maxZoom: 8,
    disableDefaultUI: true,
    mapTypeControl: false,
    backgroundColor: 'transparent',
    styles: [
      {
        featureType: "all",
        elementType: "all",
        stylers: [{ visibility: "off" }]
      }
    ]
  })

  // Restrict map bounds to US only
  const usBounds = new google.maps.LatLngBounds(
    new google.maps.LatLng(24.396308, -125.0), // Southwest
    new google.maps.LatLng(49.384358, -66.93457) // Northeast
  )
  map.value.setOptions({
    restriction: {
      latLngBounds: usBounds,
      strictBounds: false
    }
  })

  // Load GeoJSON
  map.value.data.loadGeoJson(
    'https://raw.githubusercontent.com/PublicaMundi/MappingAPI/master/data/geojson/us-states.json'
  )

  // Style the states
  map.value.data.setStyle({
    fillColor: '#1c54a8',
    strokeColor: '#a9d0fd',
    strokeWeight: 1,
    fillOpacity: 0.7
  })

  // Create a Set of state abbreviations that have data
  const statesWithData = new Set(state_data.value.map(s => s.state_name))

  // Add custom state labels for states WITHOUT data
  state_centers.forEach(stateInfo => {
    // Skip if this state has data (will be shown with red circle)
    if (statesWithData.has(stateInfo.abbrev)) return

    new google.maps.Marker({
      position: { lat: stateInfo.lat, lng: stateInfo.lng },
      map: map.value,
      label: {
        text: stateInfo.abbrev,
        color: 'white',
        fontSize: '11px',
        fontWeight: '600'
      },
      icon: {
        path: 'M 0,0',
        strokeOpacity: 0,
        scale: 1
      }
    })
  })

  // Add markers with red circles ONLY for states in your array
  state_data.value.forEach(state => {
    const stateInfo = state_centers.find(s => s.abbrev === state.state_name)
    if (stateInfo) {
      const itemCount = state.state_items.length

      // Outer ripple layer (largest, most transparent)
      new google.maps.Marker({
        position: { lat: stateInfo.lat, lng: stateInfo.lng },
        map: map.value,
        icon: {
          path: google.maps.SymbolPath.CIRCLE,
          fillColor: 'rgb(255,60,24)',
          fillOpacity: 0.2,
          strokeWeight: 0,
          scale: 45
        }
      })

      // Middle ripple layer
      new google.maps.Marker({
        position: { lat: stateInfo.lat, lng: stateInfo.lng },
        map: map.value,
        icon: {
          path: google.maps.SymbolPath.CIRCLE,
          fillColor: 'rgb(255,60,24)',
          fillOpacity: 0.4,
          strokeWeight: 0,
          scale: 37
        }
      })

      // Inner circle with number (main, most opaque)
      new google.maps.Marker({
        position: { lat: stateInfo.lat, lng: stateInfo.lng },
        map: map.value,
        label: {
          text: itemCount.toString(),
          color: 'white',
          fontSize: '16px',
          fontWeight: 'bold'
        },
        icon: {
          path: google.maps.SymbolPath.CIRCLE,
          fillColor: 'rgb(255,60,24)',
          fillOpacity: 1,
          strokeWeight: 0,
          scale: 30
        }
      })
    }
  })
}
const LoadGoogleMaps = () => {
  if (window.google && window.google.maps) {
    return InitializeMap()
  }
  const script = document.createElement('script')
  script.src = `https://maps.googleapis.com/maps/api/js?key=AIzaSyBof785oPijCcynKX2ckPT8JKF6LYSKO8g`
  script.async = true
  script.defer = true
  script.onload = InitializeMap
  document.head.appendChild(script)
}

onMounted(() => LoadGoogleMaps());
</script>
