<template>
  <div
    class="bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-xl p-6 shadow-lg border border-cyan-200"
  >
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
      <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 text-white rounded-lg p-3 shadow-md">
        <i class="fa fa-database text-2xl"></i>
      </div>
      <h3 class="text-2xl font-bold text-cyan-800">{{ t('application.system_info') }}</h3>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Record ID -->
      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-hashtag text-cyan-500"></i>
          <span class="text-sm font-medium">{{ t('application.record_id') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.id }}</p>
      </div>

      <!-- Application ID -->
      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-barcode text-cyan-500"></i>
          <span class="text-sm font-medium">{{ t('application.module') }} {{ t('shared.labels.id') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.application_id || 'N/A' }}</p>
      </div>

      <!-- Status -->
      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-info-circle text-cyan-500"></i>
          <span class="text-sm font-medium">{{ t('shared.labels.status') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.status || 'N/A' }}</p>
      </div>

      <!-- Applied Through -->
      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-random text-cyan-500"></i>
          <span class="text-sm font-medium">{{ t('application.applied_through') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ appliedThroughLabel }}</p>
      </div>

      <!-- Agent -->
      <div
        v-if="item.applied_through !== 'direct_candidate'"
        class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow"
      >
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-user text-cyan-500"></i>
          <span class="text-sm font-medium">{{ t('shared.labels.agent') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.agent_name || 'N/A' }}</p>
      </div>

      <!-- Payment Responsibility -->
      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-money text-cyan-500"></i>
          <span class="text-sm font-medium">{{ t('application.payment_responsibility') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">
          {{ item.payment_responsibility_label || formatJobPayers(item.payment_responsibility) || 'N/A' }}
        </p>
      </div>

      <!-- Created At -->
      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-calendar-plus-o text-cyan-500"></i>
          <span class="text-sm font-medium">{{ t('shared.labels.created_at') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.created_at || 'N/A' }}</p>
      </div>

      <!-- Updated At -->
      <div class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-calendar-check-o text-cyan-500"></i>
          <span class="text-sm font-medium">{{ t('shared.labels.updated_at') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.updated_at || 'N/A' }}</p>
      </div>

      <!-- Created By -->
      <div
        class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow"
        v-if="item.created_by"
      >
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-user-plus text-cyan-500"></i>
          <span class="text-sm font-medium">{{ t('shared.labels.created_by') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.created_by }}</p>
      </div>

      <!-- Updated By -->
      <div
        class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow"
        v-if="item.updated_by"
      >
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-user-times text-cyan-500"></i>
          <span class="text-sm font-medium">{{ t('shared.labels.updated_by') }}</span>
        </div>
        <p class="text-gray-800 font-semibold">{{ item.updated_by }}</p>
      </div>

      <!-- Remarks -->
      <div
        class="bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow md:col-span-2"
        v-if="item.remarks"
      >
        <div class="flex items-center gap-2 text-gray-600 mb-1">
          <i class="fa fa-comment text-cyan-500"></i>
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

const appliedThroughLabel = computed(() => {
  if (props.item?.applied_through === 'direct_candidate') return 'Direct Candidate'
  if (props.item?.applied_through === 'agent') return 'Agent'
  return props.item?.agent_id ? 'Agent' : 'Direct Candidate'
})
</script>
