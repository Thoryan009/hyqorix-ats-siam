<template>
  <div
    class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 shadow-lg border border-green-200"
  >
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
      <div
        class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg p-3 shadow-md"
      >
        <i class="fa fa-phone text-2xl"></i>
      </div>
      <h3 class="text-2xl font-bold text-green-800">{{t('application.contact')}} {{t('shared.titles.information')}}</h3>
    </div>

    <!-- Content Grid -->
    <div class="space-y-4">
      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-mobile text-green-500 text-xl"></i>
          <span class="text-sm font-medium">{{t('shared.labels.phone')}}</span>
        </div>
        <a
          :href="`tel:${item.mobile}`"
          class="text-gray-800 font-semibold hover:text-green-600 transition-colors"
        >
          {{ item.mobile }}
        </a>
      </div>

      <div
        v-if="role != 'client'"
        class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow"
      >
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-envelope text-green-500"></i>
          <span class="text-sm font-medium">{{t('shared.labels.email')}}</span>
        </div>
        <a
          :href="`mailto:${item.email}`"
          class="text-gray-800 font-semibold hover:text-green-600 transition-colors break-all"
        >
          {{ item.email }}
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()

defineProps({
  item: Object,
})
</script>
