<template>
  <div class="w-full">
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-stretch">
      <!-- Job Select -->
      <div class="xl:col-span-3">
        <div class="h-full bg-white border border-gray-200 rounded-xl shadow-sm p-5">
          <div class="flex items-center justify-between mb-3">
            <label class="text-gray-800 text-sm font-semibold tracking-wide uppercase">{{t('shared.labels.job')}}</label>
            <span class="text-xs text-gray-400">{{t('shared.actions.search')}}</span>
          </div>

          <BaseSearchSelect
            :options="jobs"
            placeholder="Search by job name or job code"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterJobList"
            :model-value="modelValue"
            @update:modelValue="updateValue"
          />
        </div>
      </div>

      <!-- Job Card -->
      <div class="xl:col-span-9">
        <div v-if="job" class="h-full bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
          <div class="grid grid-cols-1 md:grid-cols-12 gap-6 h-full">
            <!-- Left -->
            <div class="md:col-span-4 flex flex-col justify-center">
              <h2 class="text-2xl font-semibold text-gray-900 leading-snug">{{ job.name }}</h2>
            </div>

            <!-- Right -->
            <div class="md:col-span-8 md:border-l border-gray-200 md:pl-6">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-10 text-sm">
                <div class="flex space-x-2">
                  <span class="text-gray-500 font-medium">{{t('shared.labels.experience')}}:</span>
                  <span class="text-gray-800 font-semibold">{{ job.experience }}</span>
                </div>

                <div class="flex space-x-2">
                  <span class="text-gray-500 font-medium">{{t('shared.labels.demand_letter')}}:</span>
                  <span class="text-gray-800 font-semibold">{{ job.work_order }}</span>
                </div>

                <div class="flex space-x-2">
                  <span class="text-gray-500 font-medium">{{t('shared.labels.client')}}:</span>
                  <span class="text-gray-800 font-semibold">{{ job.client }}</span>
                </div>

                <div class="flex space-x-2 items-center">
                  <AtsApplicationsBadge :count="job.applications_count" />
                </div>
              </div>
            </div>
          </div>
        </div>
        <div
          v-else
          class="h-full bg-white rounded-xl border border-dashed border-gray-300 p-6 flex items-center justify-center text-gray-500 text-sm"
        >{{ t('ats.select_job_to_preview') }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import AtsApplicationsBadge from '../atsParts/AtsApplicationsBadge.vue'
import { useTranslate } from '@/shared/composables/useTranslate.js'

const { t } = useTranslate()
defineProps({
  jobs: Array,
  job: Object,
  modelValue: [String, Number],
})

const emit = defineEmits(['update:modelValue'])

const filterJobList = (option, query) => {
  const jobName = (option.job_name ?? '').toLowerCase()
  const jobCode = (option.job_code ?? '').toLowerCase()
  const displayName = (option.name ?? '').toLowerCase()

  return jobName.includes(query) || jobCode.includes(query) || displayName.includes(query)
}

const updateValue = (val) => {
  emit('update:modelValue', val)
}
</script>
