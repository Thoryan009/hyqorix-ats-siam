<template>
  <div
    class="min-h-screen flex items-center md:pb-10 justify-center px-4 bg-cover bg-center bg-no-repeat"
    :style="{
      backgroundImage: `url(${settingsData?.login_background_image_url || loginBackgroundImage})`,
      backgroundPosition: 'bottom',
      backgroundRepeat: 'no-repeat',
      backgroundSize: 'cover',
    }"
  >
    <div class="w-full max-w-md bg-white/90 backdrop-blur-lg rounded-2xl shadow-2xl p-8">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { useSettingsQuery } from '@/modules/home/queries/useSettingsQuery'
import { computed } from 'vue'

const { data: settingsDataResponse } = useSettingsQuery()
const settingsData = computed(() => settingsDataResponse.value?.data || {})
const loginBackgroundImage = import.meta.env.VITE_APP_LOGIN_BACKGROUND_IMAGE_URL
</script>
