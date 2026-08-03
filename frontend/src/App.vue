<script setup>
import { computed, watch } from 'vue';
import app from './shared/config/appConfig';
import { RouterView } from 'vue-router'
import { useSettingsQuery } from './modules/home/queries/useSettingsQuery';

document.title = `${app.name}`;

const { data, isLoading } = useSettingsQuery(1)


const settingsData = computed(() => data.value?.data || {})

// const companyName = computed(() => settingsData.value?.company_name || 'Norqel Technologies')
//   document.title = companyName.value

const faviconUrl = computed(() =>
  settingsData.value?.fav_icon_url || '/favicon.ico'
)

console.log('Fetched settings data in App.vue:', settingsData) // Debug log
console.log('Favicon URL:', faviconUrl.value) // Debug log
/**
 * update favicon dynamically
 */
const setFavicon = (iconUrl) => {
  let link = document.querySelector("link[rel~='icon']")

  if (!link) {
    link = document.createElement('link')
    link.rel = 'icon'
    document.head.appendChild(link)
  }

  link.href = iconUrl
}

// watch(
//   companyName,
//   (newName) => {
//     if (newName) {
//       document.title = newName
//     }
//   },
//   { immediate: true }
// )
// watch settings change

watch(
  faviconUrl,
  (newIcon) => {
    if (newIcon) {
      setFavicon(newIcon)
    }
  },
  { immediate: true }
)





</script>

<template>
  <RouterView />
</template>

<style></style>
