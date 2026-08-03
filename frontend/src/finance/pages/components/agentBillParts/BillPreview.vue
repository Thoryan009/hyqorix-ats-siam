<template>
  <div id="bill-preview-print" ref="previewRef" class="text-sm text-gray-800">
    <div class="mb-4 text-center">
      <h2 class="text-lg font-bold uppercase">{{ companyInfo.name }}</h2>
      <p class="mt-1 text-xs text-gray-600">{{ companyInfo.address }}</p>
      <p class="text-xs text-gray-600">{{ companyInfo.phone }}</p>
      <p class="text-xs text-gray-600">{{ companyInfo.web }}</p>
    </div>

    <div class="mb-4 border-y border-gray-300 py-3 text-center">
      <h3 class="text-base font-bold tracking-wide">BILL</h3>
      <div class="mt-2 flex flex-wrap justify-center gap-x-8 gap-y-1 text-xs">
        <p><span class="font-semibold">Bill No:</span> {{ billNo }}</p>
        <p><span class="font-semibold">Bill Date:</span> {{ formattedBillDate }}</p>
      </div>
    </div>

    <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
      <div class="rounded border border-gray-200 p-3">
        <h4 class="mb-2 font-semibold text-gray-700">Bill To (Agent)</h4>
        <p><span class="text-gray-500">Agent Name:</span> {{ agent?.agent_name || '-' }}</p>
        <p><span class="text-gray-500">Agent Code:</span> {{ agent?.agent_code || '-' }}</p>
        <p><span class="text-gray-500">Mobile:</span> {{ agent?.phone || '-' }}</p>
        <p><span class="text-gray-500">Email:</span> {{ agent?.email || '-' }}</p>
      </div>

      <div class="rounded border border-gray-200 p-3">
        <h4 class="mb-2 font-semibold text-gray-700">Job Information</h4>
        <p>
          <span class="text-gray-500">Job Title:</span> {{ job?.job_title || '-' }}
          <span class="mx-2 text-gray-300">|</span>
          <span class="text-gray-500">Job Code:</span> {{ job?.job_code || '-' }}
          <span class="mx-2 text-gray-300">|</span>
          <span class="text-gray-500">Client:</span> {{ job?.client || '-' }}
        </p>
      </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="w-full border-collapse text-xs">
        <thead class="bg-gray-50">
          <tr>
            <th class="border-b border-gray-200 px-2 py-2 text-left">SL</th>
            <th class="border-b border-gray-200 px-2 py-2 text-left">Passport No</th>
            <th class="border-b border-gray-200 px-2 py-2 text-left">Candidate Name</th>
            <th class="border-b border-gray-200 px-2 py-2 text-left">Status</th>
            <th
              v-for="field in candidateCostFields"
              :key="field.key"
              class="border-b border-gray-200 px-2 py-2 text-right text-black"
            >
              {{ field.label }}
            </th>
            <th class="border-b border-gray-200 px-2 py-2 text-right">Total Cost (৳)</th>
            <th class="border-b border-gray-200 px-2 py-2 text-right">Sale Price (৳)</th>
            <th class="border-b border-gray-200 px-2 py-2 text-right">Profit (৳)</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!selectedCandidates.length">
            <td :colspan="columnCount" class="border-b border-gray-100 px-2 py-4 text-center text-gray-500">
              No candidates selected
            </td>
          </tr>
          <tr
            v-for="(candidate, index) in selectedCandidates"
            :key="candidate.id"
            class="border-b border-gray-100"
          >
            <td class="px-2 py-2">{{ index + 1 }}</td>
            <td class="px-2 py-2 font-medium">{{ candidate.passport_no }}</td>
            <td class="px-2 py-2">{{ candidate.candidate_name }}</td>
            <td class="px-2 py-2">
              <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">
                {{ candidate.status }}
              </span>
            </td>
            <td
              v-for="field in candidateCostFields"
              :key="field.key"
              class="px-2 py-2 text-right text-black"
            >
              {{ formatCurrency(getCandidateCosts(candidate)[field.key] || 0) }}
            </td>
            <td class="px-2 py-2 text-right font-semibold text-gray-800">
              {{ formatCurrency(getCandidateTotalCost(candidate)) }}
            </td>
            <td class="px-2 py-2 text-right font-semibold text-blue-700">
              {{ formatCurrency(getCandidateSalePrice(candidate)) }}
            </td>
            <td class="px-2 py-2 text-right font-semibold text-green-700">
              {{ formatCurrency(getCandidateProfit(candidate)) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 border-t border-gray-200 pt-3 text-sm">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="font-medium text-gray-700">
          Total Candidates:
          <span class="font-bold text-gray-900">{{ selectedCandidates.length }}</span>
        </p>
        <div class="flex flex-wrap gap-4">
          <p class="font-medium text-gray-700">
            Total Cost:
            <span class="font-bold text-gray-900">{{ formatCurrency(totalCost) }}</span>
          </p>
          <p class="font-medium text-gray-700">
            Total Sale:
            <span class="font-bold text-blue-700">{{ formatCurrency(totalAmount) }}</span>
          </p>
          <p class="font-medium text-gray-700">
            Total Profit:
            <span class="font-bold text-green-700">{{ formatCurrency(totalProfit) }}</span>
          </p>
        </div>
      </div>
      <p class="mt-3 text-xs italic text-gray-600">
        Amount In Words: {{ amountInWords }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { companyInfo } from '@/finance/data/agentBillData'
import {
  candidateCostFields,
  formatBillDate,
  formatCurrency,
  getCandidateCosts,
  getCandidateProfit,
  getCandidateSalePrice,
  getCandidateTotalCost,
  numberToWords,
} from '@/finance/utils/billUtils'

const props = defineProps({
  agent: { type: Object, default: null },
  job: { type: Object, default: null },
  selectedCandidates: { type: Array, default: () => [] },
  totalAmount: { type: Number, default: 0 },
  billNo: { type: String, default: '' },
  billDate: { type: String, default: '' },
})

const previewRef = ref(null)

const columnCount = 4 + candidateCostFields.length + 3

const formattedBillDate = computed(() => formatBillDate(props.billDate))
const amountInWords = computed(() => numberToWords(props.totalAmount))

const totalCost = computed(() =>
  props.selectedCandidates.reduce((sum, candidate) => sum + getCandidateTotalCost(candidate), 0)
)

const totalProfit = computed(() =>
  props.selectedCandidates.reduce((sum, candidate) => sum + getCandidateProfit(candidate), 0)
)

defineExpose({ previewRef })
</script>
