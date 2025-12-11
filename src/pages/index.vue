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
import {useAuth0} from "@auth0/auth0-vue";
import {GlobalStore} from "@/stores/globals";
import {UccServer} from "@/plugins/ucc-server";
import {state_centers} from "@/composables/GlobalComposables";
import {my_partner_id} from "@/composables/GlobalComposables";

const map = ref(null);
const state_data = ref([]);
const map_container = ref(null);
const {getAccessTokenSilently} = useAuth0();

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

  // Add custom state labels for states (just labels)
  state_centers.forEach(state_center => {
    // We will not display this label if state data has value
    // because the state data creates a new label for itself.
    if (state_data.value.filter(s=>s.state===state_center.abbrev).length>0) return;
    new google.maps.Marker({
      position: { lat: state_center.lat, lng: state_center.lng },
      map: map.value,
      label: {
        text: state_center.abbrev,
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
  });
}
const LoadGoogleMaps = () => {
  const script = document.createElement('script')
  script.src = `https://maps.googleapis.com/maps/api/js?key=AIzaSyBof785oPijCcynKX2ckPT8JKF6LYSKO8g`
  script.async = true
  script.defer = true
  document.head.appendChild(script)
}
const FetchBuyersData = async () => {
  const token = await getAccessTokenSilently();
  UccServer(token).get(`/data/map-data/${my_partner_id.value}`).then(res=>{
    console.log(res.data);
    state_data.value = res.data;
    InitializeMap();
    AddStateMarkers();
  });
}
const CreateCustomMarker = (state, count) => {
  const marker_div = document.createElement('div');
  marker_div.style.cssText = `
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background-color: rgb(255,60,24);
    border-radius: 50%;
    color: white;
    font-weight: bold;
  `;

  const state_label = document.createElement('div');
  state_label.textContent = state;
  state_label.style.fontSize = '12px';

  const count_label = document.createElement('div');
  count_label.textContent = count.toString();
  count_label.style.fontSize = '16px';

  marker_div.appendChild(state_label);
  marker_div.appendChild(count_label);

  return marker_div;
}
const AddStateMarkers = () => {
  // Add markers with red circles ONLY for states
  // This will put a new label on top of the original one.

  state_data.value.forEach(state_item => {
    const state_center = state_centers.find(s => s.abbrev === state_item.state)
    if (state_center) {

      // Outer ripple layer (largest, most transparent)
      new google.maps.Marker({
        position: { lat: state_center.lat, lng: state_center.lng },
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
        position: { lat: state_center.lat, lng: state_center.lng },
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
        position: { lat: state_center.lat, lng: state_center.lng },
        map: map.value,
        label: {
          text: `${state_item.state} (${state_item.count.toString()})`,
          color: 'white',
          fontSize: '12px',
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

onMounted(() => {
  LoadGoogleMaps();
  FetchBuyersData();
});
</script>
