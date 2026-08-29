<template>
  <article class="mx-auto w-full max-w-4xl overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-100">
    <!-- Header -->
    <header class="border-b border-slate-100 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0 space-y-1">
          <p class="text-xs font-medium uppercase tracking-wider text-slate-400">
            {{ t('journals.journal_information') }}
          </p>
          <h2 class="text-2xl font-bold tracking-tight text-slate-900">
            {{ journal.voucher_no || '—' }}
          </h2>
          <p class="text-sm text-slate-500">
            {{ journal.voucher_date_label || journal.voucher_date || '—' }}
            <span v-if="journal.transaction_type_name" class="text-slate-400"> · </span>
            <span v-if="journal.transaction_type_name">{{ journal.transaction_type_name }}</span>
          </p>
        </div>
        <span
          class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1"
          :class="statusBadgeClass"
        >
          <span class="h-1.5 w-1.5 rounded-full" :class="statusDotClass"></span>
          {{ journal.status || t('journals.waiting_for_approval') }}
        </span>
      </div>
    </header>

    <!-- Totals strip -->
    <div class="grid grid-cols-1 divide-y divide-slate-100 border-b border-slate-100 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
      <div class="px-6 py-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
          {{ t('journals.total_debit') }}
        </p>
        <p class="mt-1 text-xl font-bold tabular-nums text-slate-900">
          {{ formatAmount(journal.total_debit) }}
        </p>
      </div>
      <div class="px-6 py-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
          {{ t('journals.total_credit') }}
        </p>
        <p class="mt-1 text-xl font-bold tabular-nums text-slate-900">
          {{ formatAmount(journal.total_credit) }}
        </p>
      </div>
      <div class="px-6 py-4">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
          {{ t('journals.balance') }}
        </p>
        <p class="mt-1 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700">
          <i class="fa fa-check-circle text-emerald-500"></i>
          {{ t('journals.balanced') }}
        </p>
      </div>
    </div>

    <!-- Meta details -->
    <section class="border-b border-slate-100 px-6 py-5">
      <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-slate-400">
        {{ t('journals.journal_information') }}
      </h3>
      <dl class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
        <div v-for="item in detailItems" :key="item.label" class="min-w-0">
          <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
            {{ item.label }}
          </dt>
          <dd class="mt-0.5 text-sm font-medium leading-snug text-slate-900">
            {{ item.value }}
          </dd>
        </div>
      </dl>

      <div v-if="journal.narration" class="mt-5 rounded-lg bg-slate-50 px-4 py-3 ring-1 ring-slate-100">
        <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
          {{ t('journals.narration') }}
        </p>
        <p class="mt-1.5 text-sm leading-relaxed text-slate-700">
          {{ journal.narration }}
        </p>
      </div>

      <div
        v-if="journal.manager_comment"
        class="mt-4 rounded-lg border border-amber-100 bg-amber-50/60 px-4 py-3"
      >
        <p class="text-[11px] font-medium uppercase tracking-wide text-amber-700/80">
          {{ t('journals.manager_comment') }}
        </p>
        <p class="mt-1.5 text-sm leading-relaxed text-amber-950">
          {{ journal.manager_comment }}
        </p>
      </div>

      <div
        v-if="receiptUrls.length"
        class="mt-5 rounded-lg bg-slate-50 px-4 py-3 ring-1 ring-slate-100"
      >
        <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
          {{ t('journals.receipts') }}
        </p>
        <JournalReceiptGallery :items="receiptGalleryItems" />
      </div>
    </section>

    <!-- Journal lines -->
    <section class="px-6 py-5">
      <div class="mb-3 flex items-center justify-between">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">
          {{ t('journals.journal_lines') }}
        </h3>
        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
          {{ lines.length }} {{ t('journals.lines_count') }}
        </span>
      </div>

      <div class="overflow-hidden rounded-xl border border-slate-200">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-slate-200 bg-slate-50/80">
                <th class="w-10 px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                  #
                </th>
                <th class="px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                  {{ t('journals.account') }}
                </th>
                <th class="hidden px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500 md:table-cell">
                  {{ t('journals.sub_ledger') }}
                </th>
                <th class="hidden px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500 lg:table-cell">
                  {{ t('journals.cost_revenue_type') }}
                </th>
                <th class="w-28 px-3 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                  {{ t('journals.debit_label') }}
                </th>
                <th class="w-28 px-3 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                  {{ t('journals.credit_label') }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr
                v-for="(line, index) in lines"
                :key="line.id || index"
                class="transition-colors hover:bg-slate-50/70"
              >
                <td class="px-3 py-3 tabular-nums text-slate-400">{{ index + 1 }}</td>
                <td class="px-3 py-3">
                  <p class="font-semibold text-slate-900">{{ line.account_code || '—' }}</p>
                  <p class="mt-0.5 max-w-[220px] truncate text-xs text-slate-500">
                    {{ line.account_name || '' }}
                  </p>
                  <p class="mt-1 text-xs text-slate-400 md:hidden">
                    <span v-if="line.sub_ledger">{{ line.sub_ledger }}</span>
                    <span v-if="line.sub_ledger && getCostTypeLabel(line.cost_type)"> · </span>
                    <span v-if="getCostTypeLabel(line.cost_type)">
                      {{ getCostTypeLabel(line.cost_type) }}
                    </span>
                  </p>
                </td>
                <td class="hidden px-3 py-3 text-slate-600 md:table-cell">
                  {{ line.sub_ledger || '—' }}
                </td>
                <td class="hidden px-3 py-3 text-slate-600 lg:table-cell">
                  {{ getCostTypeLabel(line.cost_type) || '—' }}
                </td>
                <td class="px-3 py-3 text-right font-medium tabular-nums text-slate-900">
                  <span v-if="Number(line.debit) > 0">{{ formatAmount(line.debit) }}</span>
                  <span v-else class="text-slate-300">—</span>
                </td>
                <td class="px-3 py-3 text-right font-medium tabular-nums text-slate-900">
                  <span v-if="Number(line.credit) > 0">{{ formatAmount(line.credit) }}</span>
                  <span v-else class="text-slate-300">—</span>
                </td>
              </tr>
              <tr v-if="!lines.length">
                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">
                  {{ t('journals.posting_preview_empty') }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          v-if="lines.length"
          class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900"
        >
          <span>{{ t('journals.total') }}</span>
          <div class="flex gap-8 tabular-nums">
            <span>
              <span class="mr-2 text-xs font-medium uppercase tracking-wide text-slate-400">
                {{ t('journals.debit_label') }}
              </span>
              {{ formatAmount(journal.total_debit) }}
            </span>
            <span>
              <span class="mr-2 text-xs font-medium uppercase tracking-wide text-slate-400">
                {{ t('journals.credit_label') }}
              </span>
              {{ formatAmount(journal.total_credit) }}
            </span>
          </div>
        </div>
      </div>
    </section>

    <!-- Audit footer -->
    <footer
      v-if="hasAuditInfo"
      class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4 text-xs text-slate-500"
    >
      <div class="flex flex-wrap gap-x-6 gap-y-2">
        <span v-if="journal.created_by">
          {{ t('journals.created_by') }}:
          <span class="font-medium text-slate-700">{{ journal.created_by }}</span>
        </span>
        <span v-if="journal.created_at">
          {{ t('journals.created_at') }}:
          <span class="font-medium text-slate-700">{{ journal.created_at }}</span>
        </span>
      </div>

      <div
        v-if="journal.is_reversed || journal.reversed_by || journal.reversed_at"
        class="flex flex-wrap gap-x-6 gap-y-2 rounded-lg border border-rose-100 bg-rose-50/60 px-3 py-2 text-rose-700"
      >
        <span v-if="journal.reversed_by">
          {{ t('journals.reversed_by') }}:
          <span class="font-medium">{{ journal.reversed_by }}</span>
        </span>
        <span v-if="journal.reversed_at">
          {{ t('journals.reversed_at') }}:
          <span class="font-medium">{{ journal.reversed_at }}</span>
        </span>
        <RouterLink
          v-if="journal.reversal_journal_id && journal.reversal_voucher_no"
          :to="{ name: 'Journal View', params: { id: journal.reversal_journal_id } }"
          class="font-medium text-rose-800 underline-offset-2 hover:underline"
        >
          {{ t('journals.view_reversal_entry', { voucher: journal.reversal_voucher_no }) }}
        </RouterLink>
      </div>

      <div
        v-if="journal.is_reversal && journal.original_voucher_no"
        class="flex flex-wrap gap-x-6 gap-y-2 rounded-lg border border-indigo-100 bg-indigo-50/60 px-3 py-2 text-indigo-700"
      >
        <span>{{ t('journals.reversal_of') }}:</span>
        <RouterLink
          v-if="journal.reverses_journal_id"
          :to="{ name: 'Journal View', params: { id: journal.reverses_journal_id } }"
          class="font-medium underline-offset-2 hover:underline"
        >
          {{ journal.original_voucher_no }}
        </RouterLink>
      </div>
    </footer>
  </article>
</template>

<script setup>
import { computed } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import JournalReceiptGallery from './JournalReceiptGallery.vue'
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

const receiptUrls = computed(() => {
  if (Array.isArray(props.journal?.receipt_urls)) {
    return props.journal.receipt_urls.filter(Boolean)
  }
  return props.journal?.receipt_url ? [props.journal.receipt_url] : []
})

const receiptGalleryItems = computed(() =>
  receiptUrls.value.map((src, index) => ({
    key: `receipt-${index}-${src}`,
    src,
    alt: `${t('journals.receipt')} ${index + 1}`,
  })),
)

const partyLabel = computed(() => {
  const code = props.journal?.party_code
  const name = props.journal?.party_name
  if (code && name) return `${code} – ${name}`
  return name || code || '—'
})

const projectLabel = computed(() => {
  if (props.journal?.project_name) return props.journal.project_name
  const label = getProjectLabel(props.journal?.project_id)
  return label || props.journal?.project_id || '—'
})

const detailItems = computed(() => [
  {
    label: t('journals.reference_no'),
    value: props.journal?.reference_no || '—',
  },
  {
    label: t('journals.project_client_demand'),
    value: projectLabel.value,
  },
  {
    label: t('journals.party_type'),
    value: props.journal?.party_type || '—',
  },
  {
    label: t('journals.party_ledger'),
    value: partyLabel.value,
  },
])

const hasAuditInfo = computed(() =>
  Boolean(
    props.journal?.created_by
      || props.journal?.created_at
      || props.journal?.is_reversed
      || props.journal?.is_reversal
      || props.journal?.reversed_by
      || props.journal?.reversed_at,
  ),
)

const statusRaw = computed(() =>
  String(props.journal?.status_raw || props.journal?.status || '').toLowerCase(),
)

const statusBadgeClass = computed(() => {
  if (statusRaw.value.includes('reversed')) {
    return 'bg-rose-50 text-rose-700 ring-rose-200'
  }
  if (statusRaw.value.includes('approved')) {
    return 'bg-emerald-50 text-emerald-700 ring-emerald-200'
  }
  if (statusRaw.value.includes('return')) {
    return 'bg-rose-50 text-rose-700 ring-rose-200'
  }
  if (statusRaw.value.includes('pending') || statusRaw.value.includes('waiting')) {
    return 'bg-amber-50 text-amber-700 ring-amber-200'
  }
  if (statusRaw.value.includes('posted')) {
    return 'bg-slate-100 text-slate-700 ring-slate-200'
  }
  return 'bg-indigo-50 text-indigo-700 ring-indigo-200'
})

const statusDotClass = computed(() => {
  if (statusRaw.value.includes('reversed')) return 'bg-rose-500'
  if (statusRaw.value.includes('approved')) return 'bg-emerald-500'
  if (statusRaw.value.includes('return')) return 'bg-rose-500'
  if (statusRaw.value.includes('pending') || statusRaw.value.includes('waiting')) {
    return 'bg-amber-500'
  }
  if (statusRaw.value.includes('posted')) return 'bg-slate-500'
  return 'bg-indigo-500'
})

const formatAmount = (value) => {
  if (value === null || value === undefined || value === '') return '0.00'
  return Number(value).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}
</script>
