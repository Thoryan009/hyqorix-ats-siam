<template>
  <div class="flex-1">
    <div class="flex items-center gap-x-3 mb-3">
      <div class="flex items-center overflow-hidden">
        <img
          :src="settingsData?.company_logo_url"
          :alt="settingsData?.company_name"
          class="w-17.5 h-17.5 border-2 border-gray-300 object-cover rounded-full"
        />

        <div class="ml-3">
          <h1 class="text-3xl font-bold text-gray-900">
            {{ settingsData?.company_name }}
          </h1>

          <p class="text-[13px] text-gray-900">
            Phone: {{ settingsData?.company_phone }}
            <span class="mx-1">|</span>
            Email: {{ settingsData?.company_email }}
          </p>
        </div>
      </div>
    </div>

    <p class="text-[13px] text-gray-900">
      {{ settingsData?.company_address }}
    </p>
  </div>
</template>

<script setup>
import { useSettingsQuery } from '@/modules/setting/queries/useSettingsQuery';
import { computed } from 'vue';

// Fetch settings (single record)
const { data } = useSettingsQuery(1)
const settingsData = computed(() => data.value?.data?.data || {})
</script>
