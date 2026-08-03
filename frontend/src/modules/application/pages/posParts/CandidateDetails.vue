<template>
  <div class="bg-white px-4 py-3 rounded-t-xl border border-b-0 border-gray-200">
    <div class="flex justify-between items-center gap-3 mb-3">
      <div class="flex gap-2 items-center">
        <div
          class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-dark text-white"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
            />
          </svg>
        </div>
        <div>
          <h3 class="text-lg font-bold text-gray-900">{{ app.full_name }}</h3>
          <p
            class="px-2 py-0.5 text-sm rounded-md text-black"
            :class="hasClientPayer ? 'bg-orange-100' : 'bg-green-100'"
          >
            Payment: {{ payerLabel }}
          </p>
        </div>
      </div>
      <div
        v-if="app.discount_amount"
        class="px-3 py-1.5 bg-green-50 border border-green-500 rounded-lg"
      >
        <p class="text-sm font-semibold text-green-600">
          Discount: <span class="text-lg">৳{{ app.discount_amount }}</span>
        </p>
      </div>
      <!-- Agent Information -->
      <div class="flex items-center gap-2">
        <div class="flex gap-2 items-center bg-green-400/10 border border-green-400 px-3 py-1 rounded-full">
          <span class="text-sm font-medium text-gray-500">Agent: </span>
          <span
            class="text-sm font-bold bg-gradient-to-r from-green-500 to-purple-600 bg-clip-text text-transparent"
          >
            {{ app.agent ? app.agent : 'Not Available' }}
          </span>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
      <div class="flex items-start gap-2">
        <span class="text-gray-600 font-medium whitespace-nowrap">Email:</span>
        <span class="text-gray-900 truncate">{{ app.email }}</span>
      </div>

      <div class="flex items-start gap-2">
        <span class="text-gray-600 font-medium whitespace-nowrap">Mobile:</span>
        <span class="text-gray-900">{{ app.mobile }}</span>
      </div>

      <div class="flex items-start gap-2">
        <span class="text-gray-600 font-medium whitespace-nowrap">Job:</span>
        <span class="text-gray-900 truncate"
          ><span class="font-medium">{{ app.job }}</span> ({{ app.client }})</span
        >
      </div>

      <div class="flex items-start gap-2">
        <span class="text-gray-600 font-medium whitespace-nowrap">App ID:</span>
        <span class="text-gray-900">{{ app.application_id }}</span>
      </div>

      <div class="flex items-start gap-2">
        <span class="text-gray-600 font-medium whitespace-nowrap">Demand Letter:</span>
        <span class="text-gray-900">{{ app.work_order_id }}</span>
      </div>

      <div class="flex items-start gap-2">
        <span class="text-gray-600 font-medium whitespace-nowrap">Price:</span>
        <span class="text-primary-dark font-semibold">${{ app.application_price }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatJobPayers, hasJobPayer } from '@/modules/job/utils/jobPayerUtils'

const props = defineProps({
  app: Object, // server data 그대로 전달
})

const payerValue = computed(
  () => props.app?.payment_responsibility || props.app?.payer || [],
)

const payerLabel = computed(
  () =>
    props.app?.payment_responsibility_label ||
    props.app?.payer_label ||
    formatJobPayers(payerValue.value),
)

const hasClientPayer = computed(() => hasJobPayer(payerValue.value, 'client'))
</script>
