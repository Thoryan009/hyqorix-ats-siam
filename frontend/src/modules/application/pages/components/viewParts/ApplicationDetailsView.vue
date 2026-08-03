<template>
  <div
    class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-xl p-6 shadow-lg border border-pink-200"
  >
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
      <div class="bg-gradient-to-br from-pink-500 to-pink-600 text-white rounded-lg p-3 shadow-md">
        <i class="fa fa-briefcase text-2xl"></i>
      </div>
      <h3 class="text-2xl font-bold text-pink-800">{{ t('application.application_details') }}</h3>
    </div>

    <!-- Content Grid -->
    <div class="space-y-4">
      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-hashtag text-pink-500"></i>
          <span class="text-sm font-medium">{{ t('application.module') }} {{ t('shared.labels.id') }}</span>
        </div>
        <p class="text-gray-800 font-semibold text-lg">{{ item.application_id }}</p>
      </div>

      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-info-circle text-pink-500"></i>
          <span class="text-sm font-medium">{{ t('shared.labels.status') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.status }}</p>
      </div>

      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-user text-pink-500"></i>
          <span class="text-sm font-medium">{{ t('application.payment_responsibility') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ paymentResponsibilityLabel }}</p>
      </div>

      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-suitcase text-pink-500"></i>
          <span class="text-sm font-medium">{{ t('shared.labels.job') }} {{ t('shared.labels.name') }}</span>
        </div>
        <p class="text-gray-800 font-semibold text-lg">{{ item.job }}</p>
      </div>

      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-comment text-pink-500"></i>
          <span class="text-sm font-medium">{{ t('application.remarks') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.remarks }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatJobPayers } from '@/modules/job/utils/jobPayerUtils'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const props = defineProps({
  item: Object,
})

const paymentResponsibilityLabel = computed(() => {
  return (
    props.item?.payment_responsibility_label ||
    props.item?.payer_label ||
    formatJobPayers(props.item?.payment_responsibility || props.item?.payer)
  )
})
</script>
