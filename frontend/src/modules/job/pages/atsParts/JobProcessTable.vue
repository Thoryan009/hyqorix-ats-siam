<template>
  <div class="p-6">
    <!-- Title -->
    <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-800">
      <slot name="icon">
        <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
          />
        </svg>
      </slot>
      <slot name="title">{{t('ats.application_process_status')}}</slot>
    </h3>

    <!-- Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700"
            >{{ t('ats.process_stage') }}</th>
            <th
              class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-700"
            >{{ t('shared.labels.count') }}</th>
            <!-- <th
              class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-700"
            >Progress</th> -->
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr
            v-for="(process, key) in processCounts"
            :key="key"
            class="transition-colors hover:bg-blue-50"
          >
            <!-- Stage Name -->
            <td class="whitespace-nowrap px-4 py-3">
              <div class="flex items-center gap-2">
                <span
                  class="inline-flex h-2 w-2 rounded-full"
                  :class="process.count > 0 ? 'bg-green-500' : 'bg-gray-300'"
                ></span>
                <span class="text-sm font-medium text-gray-900">{{ t(`ats.process.${process.name}`) }}</span>
                <!--  -->
              </div>
            </td>

            <!-- Count -->
            <td class="whitespace-nowrap px-4 py-3 text-center">
              <span
                class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
                :class="process.count > 0 ? 'bg-primary-light text-primary' : 'bg-gray-100 text-gray-600'"
              >{{ process.count }}</span>
            </td>

            <!-- Progress -->
            <!-- <td class="whitespace-nowrap px-4 py-3">
              <div class="flex items-center justify-center gap-2">
                <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-200">
                  <div
                    class="h-full rounded-full transition-all duration-500"
                    :class="
                      process.count > 0 ? 'bg-gradient-to-r from-primary-gradient to-primary' : 'bg-gray-300'
                    "
                    :style="{ width: `${getProgressPercentage(process.count, totalApplications)}%` }"
                  ></div>
                </div>
                <span
                  class="text-xs font-medium text-gray-600"
                >{{ getProgressPercentage(process.count, totalApplications) }}%</span>
              </div>
            </td> -->
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { useTranslate } from '@/shared/composables/useTranslate.js'

const { t } = useTranslate()
defineProps({
  processCounts: {
    type: Object,
    required: true,
  },
  totalApplications: {
    type: Number,
    required: true,
  },
  formatProcessName: {
    type: Function,
    default: (key) => key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()),
  },
  getProgressPercentage: {
    type: Function,
    default: (count, total) => (total > 0 ? Math.round((count / total) * 100) : 0),
  },
})
</script>
