<template>
  <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div
      class="bg-gradient-to-br from-indigo-700 via-indigo-600 to-indigo-800 px-5 py-5 text-white"
    >
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-100/80">
            {{ t('journals.voucher_no') }}
          </p>
          <p class="mt-0.5 text-2xl font-bold tracking-tight">
            {{ journal.voucher_no || '—' }}
          </p>
          <p class="mt-1 text-sm text-indigo-100/90">
            {{ journal.voucher_date_label || journal.voucher_date || '—' }}
            <span v-if="journal.transaction_type_name">
              · {{ journal.transaction_type_name }}
            </span>
          </p>
        </div>
        <span
          class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white ring-1 ring-white/25"
        >
          {{ journal.status || t('journals.waiting_for_approval') }}
        </span>
      </div>

      <div class="mt-4 grid grid-cols-2 gap-3 border-t border-white/15 pt-4 sm:grid-cols-3">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-100/70">
            {{ t('journals.total_debit') }}
          </p>
          <p class="mt-0.5 text-lg font-bold tabular-nums">
            {{ formatAmount(journal.total_debit) }}
          </p>
        </div>
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-100/70">
            {{ t('journals.total_credit') }}
          </p>
          <p class="mt-0.5 text-lg font-bold tabular-nums">
            {{ formatAmount(journal.total_credit) }}
          </p>
        </div>
        <div class="col-span-2 sm:col-span-1">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-100/70">
            {{ t('journals.balance') }}
          </p>
          <p class="mt-0.5 inline-flex items-center gap-1.5 text-sm font-semibold">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
            {{ t('journals.balanced') }}
          </p>
        </div>
      </div>
    </div>

    <div class="divide-y divide-slate-100 px-5">
      <div class="grid grid-cols-2 gap-4 py-4 md:grid-cols-4">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
            {{ t('journals.voucher_date') }}
          </p>
          <p class="mt-0.5 text-sm font-semibold text-slate-900">
            {{ journal.voucher_date_label || journal.voucher_date || '—' }}
          </p>
        </div>
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
            {{ t('journals.transaction_type') }}
          </p>
          <p class="mt-0.5 text-sm font-semibold text-slate-900">
            {{ journal.transaction_type_name || journal.transaction_type || '—' }}
          </p>
        </div>
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
            {{ t('journals.reference_no') }}
          </p>
          <p class="mt-0.5 text-sm font-semibold text-slate-900">
            {{ journal.reference_no || '—' }}
          </p>
        </div>
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
            {{ t('journals.project_client_demand') }}
          </p>
          <p class="mt-0.5 text-sm font-semibold text-slate-900">
            {{ projectLabel }}
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 py-4 sm:grid-cols-2">
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
            {{ t('journals.party_type') }}
          </p>
          <p class="mt-0.5 text-sm font-semibold capitalize text-slate-900">
            {{ journal.party_type || '—' }}
          </p>
        </div>
        <div>
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
            {{ t('journals.party_ledger') }}
          </p>
          <p class="mt-0.5 text-sm font-semibold text-slate-900">
            {{ partyLabel }}
          </p>
        </div>
      </div>

      <div v-if="journal.narration" class="py-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
          {{ t('journals.narration') }}
        </p>
        <p class="mt-1 text-sm leading-relaxed text-slate-700">
          {{ journal.narration }}
        </p>
      </div>

      <div
        v-if="journal.created_by || journal.created_at"
        class="grid grid-cols-2 gap-4 py-4"
      >
        <div v-if="journal.created_by">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
            {{ t('journals.prepared_by') }}
          </p>
          <p class="mt-0.5 text-sm font-semibold text-slate-900">
            {{ journal.created_by }}
          </p>
        </div>
        <div v-if="journal.created_at">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
            {{ t('journals.submitted_at') }}
          </p>
          <p class="mt-0.5 text-sm font-semibold text-slate-900">
            {{ journal.created_at }}
          </p>
        </div>
      </div>
    </div>

    <div class="border-t border-slate-100">
      <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
        <h3 class="text-sm font-semibold text-slate-800">
          {{ t('journals.journal_lines') }}
        </h3>
        <span class="text-xs font-medium text-slate-500">
          {{ lines.length }} {{ t('journals.lines_count') }}
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
              <th class="px-4 py-2.5">#</th>
              <th class="px-4 py-2.5">{{ t('journals.account') }}</th>
              <th class="px-4 py-2.5">{{ t('journals.sub_ledger') }}</th>
              <th class="px-4 py-2.5">{{ t('journals.cost_revenue_type') }}</th>
              <th class="px-4 py-2.5 text-right">{{ t('journals.debit_label') }}</th>
              <th class="px-4 py-2.5 text-right">{{ t('journals.credit_label') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(line, index) in lines" :key="line.id || index" class="text-slate-800">
              <td class="px-4 py-3 tabular-nums text-slate-400">{{ index + 1 }}</td>
              <td class="px-4 py-3">
                <p class="font-medium text-slate-900">
                  {{ line.account_code || '—' }}
                </p>
                <p class="text-xs text-slate-500">{{ line.account_name || '' }}</p>
              </td>
              <td class="px-4 py-3 text-slate-600">{{ line.sub_ledger || '—' }}</td>
              <td class="px-4 py-3 text-slate-600">
                {{ getCostTypeLabel(line.cost_type) || '—' }}
              </td>
              <td class="px-4 py-3 text-right font-medium tabular-nums text-slate-900">
                {{ Number(line.debit) > 0 ? formatAmount(line.debit) : '—' }}
              </td>
              <td class="px-4 py-3 text-right font-medium tabular-nums text-slate-900">
                {{ Number(line.credit) > 0 ? formatAmount(line.credit) : '—' }}
              </td>
            </tr>
            <tr v-if="!lines.length">
              <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">
                {{ t('journals.posting_preview_empty') }}
              </td>
            </tr>
          </tbody>
          <tfoot v-if="lines.length" class="border-t border-slate-200 bg-slate-50">
            <tr class="text-sm font-semibold text-slate-900">
              <td colspan="4" class="px-4 py-3 text-right">{{ t('journals.total') }}</td>
              <td class="px-4 py-3 text-right tabular-nums">
                {{ formatAmount(journal.total_debit) }}
              </td>
              <td class="px-4 py-3 text-right tabular-nums">
                {{ formatAmount(journal.total_credit) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import { getCostTypeLabel, getProjectLabel } from '../../data/postJournalStatic'

const props = defineProps({
  journal: {
    type: Object,
    required: true,
  },
})

const { t } = useTranslate()

const lines = computed(() =>
  Array.isArray(props.journal?.lines) ? props.journal.lines : [],
)

const partyLabel = computed(() => {
  const code = props.journal?.party_code
  const name = props.journal?.party_name
  if (code && name) return `${code} – ${name}`
  return name || code || '—'
})

const projectLabel = computed(() => {
  const label = getProjectLabel(props.journal?.project_id)
  return label || props.journal?.project_id || '—'
})

const formatAmount = (value) => {
  if (value === null || value === undefined || value === '') return '0.00'
  return Number(value).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}
</script>
