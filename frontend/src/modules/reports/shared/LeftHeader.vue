<template>
  <div class="flex-1">
    <div class="flex items-center gap-x-3 mt-0 mb-1">
      <div class="flex items-center overflow-hidden">
        <img :src="settingsData?.company_logo_url" :alt="settingsData.company_name"
          class="w-17.5 h-17.5 border border-gray-300 p-1 object-cover rounded-full" />

        <div class="ml-3">
          <h1 class="text-3xl font-bold text-gray-900">{{ settingsData.company_name }}</h1>
          <p class="text-[13px]">
            Phone: {{ settingsData.company_phone }} | Email: {{ settingsData.company_email }}
          </p>
        </div>
      </div>
    </div>

    <p class="text-[13px] pt-0">
      {{ settingsData.company_address }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useSettingsQuery } from '@/modules/setting/queries/useSettingsQuery';

// Fetch settings (single record)
const { data } = useSettingsQuery(1)
const settingsData = computed(() => data.value?.data?.data || {})

</script>
