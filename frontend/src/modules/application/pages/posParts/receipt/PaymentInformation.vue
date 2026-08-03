<template>
  <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-3 shadow-sm border border-blue-100">


    <div class="grid grid-cols-3 gap-6 text-sm">
      <div
        v-for="(item, i) in visibleItems"
        :key="i"
        class="bg-white rounded-lg p-3 shadow-sm"
      >
        <p class="text-xs text-gray-500 mb-1">{{ item.label }}</p>
        <p class="font-semibold text-gray-800">{{ item.value }}</p>
      </div>
    </div>

    <!-- Toggle Button -->
    <div class="mt-4 text-center">
      <button
        v-if="items.length > 3"
        @click="expanded = !expanded"
        class="text-sm font-semibold text-blue-600 hover:underline"
      >
        {{ expanded ? 'Show Less ▲' : 'See More ▼' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  receipt: Object
})

const expanded = ref(false)

const items = computed(() => [
  { label: 'Payment Date', value: props.receipt.payment_date_formatted },
  { label: 'Payment Time', value: props.receipt.payment_time_formatted },
  { label: 'Bill No', value: props.receipt.bill_no },
  { label: 'Payment Method', value: props.receipt.payment_method },
  { label: 'Payer Name', value: props.receipt.payer_name },
  { label: 'Payer Type', value: props.receipt.payer },
  { label: 'Mobile', value: props.receipt.payer_mobile },
  { label: 'Email', value: props.receipt.payer_email },
  { label: 'Application ID', value: props.receipt.payer_application_id },
  { label: 'Applied Job', value: props.receipt.applied_job },
])

const visibleItems = computed(() =>
  expanded.value ? items.value : items.value.slice(0, 3)
)
</script>
