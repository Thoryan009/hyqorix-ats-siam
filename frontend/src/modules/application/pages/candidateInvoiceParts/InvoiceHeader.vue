<template>
  <!-- Header -->
  <div class="flex justify-between items-start mb-2 gap-6">
    <!-- Left -->
    <div class="flex-1">
      <div class="flex items-center gap-x-3 mt-0 mb-3">
        <div class="flex items-center overflow-hidden">
          <img :src="settingsData?.company_logo_url" :alt="settingsData.company_name"
            class="w-17.5 h-17.5 border border-gray-300 p-2 object-cover rounded-full" />

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
    <!-- Right -->
    <div class="flex flex-col items-end justify-end">
      <!-- QR -->
      <div class="w-full h-full overflow-hidden flex items-center justify-end">
        <QRCodeVue3 v-if="transaction?.payer_application_id" :value="String(transaction.payer_application_id)" :size="70" :dotsOptions="{
          type: 'square',
          color: '#000000',
        }" />
      </div>

      <!-- Application ID -->
      <div class="mt-3">
        <p class="text-black text-[14px] font-medium">
          {{ transaction?.payer_application_id }}
        </p>
      </div>
    </div>
  </div>

  <!-- Divider -->
  <div class="h-0.75 bg-gradient-to-r from-black to-black"></div>
</template>

<script setup>
import { useSettingsQuery } from '@/modules/setting/queries/useSettingsQuery';
import QRCodeVue3 from 'qrcode.vue'
import { computed } from 'vue';

defineProps({
  transaction: {
    type: Object,
    required: true,
  },
})

// Fetch settings (single record)
const { data } = useSettingsQuery(1)
const settingsData = computed(() => data.value?.data?.data || {})

</script>
