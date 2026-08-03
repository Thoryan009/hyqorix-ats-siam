<template>
  <div class="mb-5 rounded-xl border border-indigo-100 bg-white p-4 shadow-sm">
    <div class="mb-3 flex items-center gap-2 border-b border-indigo-100 pb-2">
      <i class="fa fa-search text-indigo-500"></i>
      <span class="text-sm font-semibold text-gray-800">{{t('shared.labels.quick_search_candidate')}}</span>
    </div>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
      <CandidateAvatar
        :image-url="candidate.worker_image_url"
        :alt="candidate.full_name"
        size="lg"
        show-placeholder-text
      />

      <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-start justify-between gap-2">
          <h3 class="text-lg font-semibold text-gray-900">{{ candidate.full_name }}</h3>
          <span class="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-primary">
            {{ candidate.application_status }}
          </span>
        </div>

        <div class="mt-3 grid grid-cols-1 gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
          <p class="text-gray-600">
            <span class="font-medium text-gray-500">{{t('shared.labels.passport_no')}}:</span>
            {{ candidate.passport_no }}
          </p>
          <p class="text-gray-600">
            <span class="font-medium text-gray-500">{{t('application.sex')}}:</span>
            {{ candidate.sex || 'N/A' }}
          </p>
          <p class="truncate text-gray-600">
            <span class="font-medium text-gray-500">{{t('shared.labels.job')}}:</span>
            {{ candidate.job_name_with_code }}
          </p>
          <p class="truncate text-gray-600">
            <span class="font-medium text-gray-500">{{t('shared.labels.client')}}:</span>
            {{ candidate.client_name }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import CandidateAvatar from '@/shared/components/ui/CandidateAvatar.vue'
import { useTranslate } from '@/shared/composables/useTranslate.js'
const { t } = useTranslate()
defineProps({
  candidate: {
    type: Object,
    required: true,
  },
})
</script>
