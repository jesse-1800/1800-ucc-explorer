<template>
  <AppLayout>
    <template #title>Homepage</template>
    <template #content>
      <div ref="map_container" style="width: 100%; height: 500px"></div>
    </template>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import {state_centers} from "@/composables/GlobalComposables";

const map_container = ref(null)
const map = ref(null)
const state_data = ref([
  { name: "Florida", value: 100 },
  { name: "Texas", value: 90 }
])

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

  // Create a Set of state names that have data
  const statesWithData = new Set(state_data.value.map(s => s.name))

  // Add custom state labels for states WITHOUT data
  Object.entries(state_centers).forEach(([stateName, stateInfo]) => {
    // Skip if this state has data (will be shown with red circle)
    if (statesWithData.has(stateName)) return

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
    const stateInfo = state_centers[state.name]
    if (stateInfo) {
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
          text: state.value.toString(),
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
