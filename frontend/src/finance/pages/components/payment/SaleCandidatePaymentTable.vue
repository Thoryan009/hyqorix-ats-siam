<template>
  <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
    <div
      class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 bg-gradient-to-r from-slate-50 to-white px-4 py-3.5"
    >
      <div class="flex items-center gap-3">
        <span
          class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-800 text-white shadow-sm"
        >
          <i class="fa fa-list-alt text-sm"></i>
        </span>
        <div>
          <h3 class="text-sm font-semibold tracking-tight text-slate-900">{{ title }}</h3>
          <p class="mt-0.5 text-xs text-slate-500">
            {{ selectedIds.length }} of {{ candidates.length }} selected
          </p>
        </div>
      </div>
      <BaseButton
        class="rounded-lg bg-slate-800 px-3.5 py-2 text-sm text-white shadow-sm hover:bg-slate-700"
        @click="$emit('toggleAll')"
      >
        Select All
      </BaseButton>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full border-collapse text-sm">
        <thead>
          <tr class="bg-slate-50/80 text-[11px] uppercase tracking-wide text-slate-500">
            <th class="border-b border-slate-200 px-3 py-2.5 text-left font-semibold">
              <input
                type="checkbox"
                class="h-4 w-4 cursor-pointer accent-primary"
                :checked="candidates.length > 0 && selectedIds.length === candidates.length"
                @change="$emit('toggleAll')"
              />
            </th>
            <th class="border-b border-slate-200 px-3 py-2.5 text-left font-semibold">SL</th>
            <th class="border-b border-slate-200 px-3 py-2.5 text-left font-semibold">Passport No</th>
            <th class="border-b border-slate-200 px-3 py-2.5 text-left font-semibold">
              Candidate Name
            </th>
            <th class="border-b border-slate-200 px-3 py-2.5 text-left font-semibold">
              Current Process
            </th>
            <th class="border-b border-slate-200 px-3 py-2.5 text-right font-semibold whitespace-nowrap">
              <div class="flex justify-end">
                <span :class="editableSalePrice ? 'w-28 text-right' : ''">{{ salePriceLabel }}</span>
              </div>
            </th>
            <th
              v-if="showDueColumns"
              class="border-b border-slate-200 px-3 py-2.5 text-right font-semibold whitespace-nowrap"
            >
              Paid (৳)
            </th>
            <th
              v-if="showDueColumns"
              class="border-b border-slate-200 px-3 py-2.5 text-right font-semibold whitespace-nowrap"
            >
              Due (৳)
            </th>
            <th class="border-b border-slate-200 px-3 py-2.5 text-right font-semibold whitespace-nowrap">
              <div class="flex justify-end">
                <span class="w-28 text-right">Receive Amount (৳)</span>
              </div>
            </th>
            <th
              v-if="showPayerColumn"
              class="border-b border-slate-200 px-3 py-2.5 text-left font-semibold"
            >
              Payer
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!candidates.length">
            <td :colspan="columnCount" class="px-3 py-10 text-center text-slate-500">
              Load a job to see candidates.
            </td>
          </tr>
          <tr
            v-for="(candidate, index) in candidates"
            :key="candidate.id"
            class="border-b border-slate-100 transition-colors"
            :class="
              selectedIds.includes(candidate.id)
                ? 'bg-emerald-50/40 hover:bg-emerald-50/70'
                : 'hover:bg-slate-50/80'
            "
          >
            <td class="px-3 py-2.5">
              <input
                type="checkbox"
                class="h-4 w-4 cursor-pointer accent-primary"
                :checked="selectedIds.includes(candidate.id)"
                @change="$emit('toggleRow', candidate.id)"
              />
            </td>
            <td class="px-3 py-2.5 tabular-nums text-slate-500">{{ index + 1 }}</td>
            <td class="px-3 py-2.5 font-mono text-[13px] text-slate-700">
              {{ candidate.passport_no }}
            </td>
            <td class="px-3 py-2.5 font-medium text-slate-900">{{ candidate.candidate_name }}</td>
            <td class="px-3 py-2.5">
              <span
                class="inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700"
              >
                {{ formatApplicationStatusLabel(candidate.process_status || candidate.status) }}
              </span>
            </td>
            <td class="px-3 py-2.5 text-right tabular-nums whitespace-nowrap text-slate-700">
              <div v-if="canEditSalePrice(candidate)" class="flex justify-end">
                <BaseInput
                  :model-value="resolveSalePrice(candidate)"
                  type="number"
                  min="0"
                  step="0.01"
                  :class-name="amountInputClass"
                  :disabled="!selectedIds.includes(candidate.id)"
                  @update:model-value="onSalePriceInput(candidate.id, $event)"
                />
              </div>
              <template v-else>
                {{ formatCurrency(resolveSalePrice(candidate)) }}
              </template>
            </td>
            <td
              v-if="showDueColumns"
              class="px-3 py-2.5 text-right tabular-nums whitespace-nowrap text-slate-600"
            >
              {{ formatCurrency(resolvePaidAmount(candidate)) }}
            </td>
            <td
              v-if="showDueColumns"
              class="px-3 py-2.5 text-right tabular-nums whitespace-nowrap font-medium text-amber-700"
            >
              {{ formatCurrency(resolveDueAmount(candidate)) }}
            </td>
            <td class="px-3 py-2.5">
              <div class="flex flex-col items-end gap-1">
                <BaseInput
                  :model-value="payAmounts[candidate.id]"
                  type="number"
                  min="0"
                  step="0.01"
                  :class-name="amountInputClass"
                  :disabled="isPayAmountDisabled(candidate.id)"
                  @update:model-value="onPayAmountInput(candidate.id, $event)"
                />
                <p
                  v-if="shouldShowPreviousPaidHint(candidate)"
                  class="max-w-[9rem] text-right text-[11px] leading-tight text-slate-500"
                >
                  Previously paid: {{ formatCurrency(resolvePaidAmount(candidate)) }}
                </p>
              </div>
            </td>
            <td v-if="showPayerColumn" class="px-3 py-2.5 font-medium text-violet-700">
              {{ candidate.candidate_name }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div
      v-if="candidates.length"
      class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200/80 bg-slate-50/90 px-4 py-3.5 text-sm"
    >
      <p class="text-slate-600">
        Selected:
        <span class="font-semibold text-slate-900">{{ selectedIds.length }}</span>
      </p>
      <div class="flex flex-wrap items-center gap-4 text-slate-600">
        <p v-if="showDueColumns">
          Total Due:
          <span class="font-semibold tabular-nums text-amber-700">
            {{ formatCurrency(totalDueAmount) }}
          </span>
        </p>
        <p>
          Total Receive Amount (BDT):
          <span class="text-base font-bold tabular-nums text-emerald-700">
            {{ formatCurrency(totalPayAmount) }}
          </span>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import BaseButton from '@/shared/components/base/BaseButton.vue'
import BaseInput from '@/shared/components/base/BaseInput.vue'
import { formatCurrency, getCandidateSalePrice } from '@/finance/utils/billUtils'
import { formatApplicationStatusLabel } from '@/finance/utils/jobListMapper'

const props = defineProps({
  candidates: { type: Array, default: () => [] },
  selectedIds: { type: Array, default: () => [] },
  payAmounts: { type: Object, required: true },
  salePrices: { type: Object, default: () => ({}) },
  payerType: { type: String, default: '' },
  title: { type: String, default: 'Job Candidates' },
  salePriceLabel: { type: String, default: 'Sale Price (৳)' },
  editableSalePrice: { type: Boolean, default: false },
  showDueColumns: { type: Boolean, default: false },
  showPayerColumn: { type: Boolean, default: false },
  payAmountReadonly: { type: Boolean, default: false },
})

const emit = defineEmits(['toggleAll', 'toggleRow', 'updatePayAmount', 'updateSalePrice'])

const amountInputClass =
  'w-28 rounded-lg border border-slate-300 bg-white px-3 py-2 text-right tabular-nums shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/40 disabled:bg-slate-50 disabled:text-slate-400'

function isPayAmountDisabled(candidateId) {
  return props.payAmountReadonly || !props.selectedIds.includes(candidateId)
}

const columnCount = computed(() => {
  let count = 7
  if (props.showDueColumns) count += 2
  if (props.showPayerColumn) count += 1
  return count
})

const totalPayAmount = computed(() => {
  return props.selectedIds.reduce((sum, candidateId) => {
    return sum + Number(props.payAmounts[candidateId] || 0)
  }, 0)
})

const totalDueAmount = computed(() => {
  return props.selectedIds.reduce((sum, candidateId) => {
    const candidate = props.candidates.find((item) => item.id === candidateId)
    return sum + (candidate ? resolveDueAmount(candidate) : 0)
  }, 0)
})

function resolveSalePrice(candidate) {
  if (props.salePrices[candidate.id] !== undefined && props.salePrices[candidate.id] !== null) {
    return Number(props.salePrices[candidate.id]) || 0
  }
  return Number(getCandidateSalePrice(candidate)) || 0
}

function resolvePaidAmount(candidate) {
  return Number(candidate.collected_amount ?? 0) || 0
}

function canEditSalePrice(candidate) {
  // Sale price / client commission is editable only when nothing has been paid yet.
  return props.editableSalePrice && resolvePaidAmount(candidate) <= 0
}

function resolveDueAmount(candidate) {
  const salePrice = resolveSalePrice(candidate)
  const paid = resolvePaidAmount(candidate)
  return Math.max(0, Math.round((salePrice - paid) * 100) / 100)
}

function shouldShowPreviousPaidHint(candidate) {
  const paid = resolvePaidAmount(candidate)
  if (paid <= 0) return false

  const due = resolveDueAmount(candidate)
  const payAmount = Number(props.payAmounts[candidate.id] || 0)
  // When collecting the due balance, surface how much was already paid.
  return Math.abs(payAmount - due) < 0.01
}

function onPayAmountInput(candidateId, value) {
  emit('updatePayAmount', { candidateId, value })
}

function onSalePriceInput(candidateId, value) {
  const candidate = props.candidates.find((item) => item.id === candidateId)
  if (!candidate || !canEditSalePrice(candidate)) return
  emit('updateSalePrice', { candidateId, value })
}
</script>
