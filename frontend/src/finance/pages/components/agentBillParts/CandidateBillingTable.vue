<template>
  <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
      <h3 class="text-sm font-semibold text-gray-800">Candidates for Billing</h3>
      <BaseButton class="bg-green-600 text-white hover:bg-green-700" @click="$emit('toggleAll')">
        Select All
      </BaseButton>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full border-collapse text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="border-b border-gray-200 px-3 py-2 text-left">
              <input
                type="checkbox"
                class="h-4 w-4 cursor-pointer accent-green-600"
                :checked="candidates.length > 0 && selectedIds.length === candidates.length"
                @change="$emit('toggleAll')"
              />
            </th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">SL</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Passport No</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Candidate Name</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Status</th>
            <th
              v-for="field in candidateCostFields"
              :key="field.key"
              class="border-b border-gray-200 px-3 py-2 text-right text-black"
            >
              {{ field.label }}
            </th>
            <th class="border-b border-gray-200 px-3 py-2 text-right">Total Cost (৳)</th>
            <th class="border-b border-gray-200 px-3 py-2 text-right">Sale Price (৳)</th>
            <th class="border-b border-gray-200 px-3 py-2 text-right">Profit (৳)</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(candidate, index) in candidates"
            :key="candidate.id"
            class="border-b border-gray-100 hover:bg-gray-50"
          >
            <td class="px-3 py-2">
              <input
                type="checkbox"
                class="h-4 w-4 cursor-pointer accent-green-600"
                :checked="selectedIds.includes(candidate.id)"
                @change="$emit('toggleRow', candidate.id)"
              />
            </td>
            <td class="px-3 py-2">{{ index + 1 }}</td>
            <td class="px-3 py-2 font-medium">{{ candidate.passport_no }}</td>
            <td class="px-3 py-2">{{ candidate.candidate_name }}</td>
            <td class="px-3 py-2">
              <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700">
                {{ candidate.status }}
              </span>
            </td>
            <td
              v-for="field in candidateCostFields"
              :key="field.key"
              class="px-2 py-2 text-right"
            >
              <input
                type="number"
                min="0"
                step="1"
                class="w-24 rounded border border-gray-300 px-2 py-1 text-right text-sm text-black focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500"
                :value="getCandidateCosts(candidate)[field.key] || 0"
                @input="onCostInput(candidate.id, field.key, $event)"
              />
            </td>
            <td class="px-3 py-2 text-right font-semibold text-gray-800">
              {{ formatCurrency(getCandidateTotalCost(candidate)) }}
            </td>
            <td class="px-2 py-2 text-right">
              <input
                type="number"
                min="0"
                step="1"
                class="w-24 rounded border border-gray-300 px-2 py-1 text-right text-sm font-semibold text-blue-700 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                :value="getCandidateSalePrice(candidate)"
                @input="onSalePriceInput(candidate.id, $event)"
              />
            </td>
            <td class="px-3 py-2 text-right font-semibold text-green-700">
              {{ formatCurrency(getCandidateProfit(candidate)) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="border-t border-gray-200 bg-gray-50 px-4 py-3 text-sm">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="font-medium text-gray-700">
          Total Selected Candidates:
          <span class="font-bold text-gray-900">{{ selectedIds.length }}</span>
        </p>
        <div class="flex flex-wrap gap-4">
          <p class="font-medium text-gray-700">
            Total Cost:
            <span class="font-bold text-gray-900">{{ formatCurrency(selectedTotalCost) }}</span>
          </p>
          <p class="font-medium text-gray-700">
            Total Sale:
            <span class="font-bold text-blue-700">{{ formatCurrency(totalAmount) }}</span>
          </p>
          <p class="font-medium text-gray-700">
            Total Profit:
            <span class="font-bold text-green-700">{{ formatCurrency(selectedTotalProfit) }}</span>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  candidateCostFields,
  formatCurrency,
  getCandidateCosts,
  getCandidateProfit,
  getCandidateSalePrice,
  getCandidateTotalCost,
} from '@/finance/utils/billUtils'

const props = defineProps({
  candidates: { type: Array, default: () => [] },
  selectedIds: { type: Array, default: () => [] },
  totalAmount: { type: Number, default: 0 },
})

const emit = defineEmits(['toggleAll', 'toggleRow', 'updateCost', 'updateSalePrice'])

function onCostInput(candidateId, fieldKey, event) {
  const value = Math.max(0, Number(event.target.value) || 0)
  emit('updateCost', { candidateId, fieldKey, value })
}

function onSalePriceInput(candidateId, event) {
  const value = Math.max(0, Number(event.target.value) || 0)
  emit('updateSalePrice', { candidateId, value })
}

const selectedCandidates = computed(() =>
  props.candidates.filter((candidate) => props.selectedIds.includes(candidate.id))
)

const selectedTotalCost = computed(() =>
  selectedCandidates.value.reduce((sum, candidate) => sum + getCandidateTotalCost(candidate), 0)
)

const selectedTotalProfit = computed(() =>
  selectedCandidates.value.reduce((sum, candidate) => sum + getCandidateProfit(candidate), 0)
)
</script>
