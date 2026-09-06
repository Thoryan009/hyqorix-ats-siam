<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ t('journals.management') }}</PageTitle>
        <p class="mt-1 text-sm text-gray-500">{{ t('journals.static_note') }}</p>
      </div>
      <BaseButton
        v-can="'journal.bill_entry'"
        className="bg-indigo-600 text-white hover:bg-indigo-700"
        @click="$router.push({ name: 'Post Journal' })"
      >
        {{ t('journals.post_journal') }}
      </BaseButton>
    </PageHeader>

    <div class="mb-4 flex justify-end">
      <TableFilters
        :filters="filters"
        :has-active-filters="hasActiveFilters"
        @reset="resetFilters"
      />
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
        <h2 class="text-base font-semibold text-slate-800 sm:text-lg">
          {{ t('journals.sheet_title') }}
        </h2>
      </div>

      <div v-if="isLoading" class="px-4 py-10 text-center text-sm text-slate-500">
        {{ t('journals.loading') }}
      </div>

      <div v-else-if="!rows.length" class="px-4 py-10 text-center text-sm text-slate-500">
        {{ t('journals.empty_note') }}
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
          <thead>
            <tr class="bg-[#1e4b8c] text-left text-white">
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.je_no') }}
              </th>
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.date') }}
              </th>
              <th class="min-w-[260px] border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.transaction') }}
              </th>
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.account_code') }}
              </th>
              <th class="min-w-[220px] border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.account_name') }}
              </th>
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.party_ref') }}
              </th>
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 text-right font-semibold">
                {{ t('journals.debit') }}
              </th>
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 text-right font-semibold">
                {{ t('journals.credit') }}
              </th>
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.project_client') }}
              </th>
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.status') }}
              </th>
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.created_by') }}
              </th>
              <th class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 font-semibold">
                {{ t('journals.action') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(row, index) in rows"
              :key="row.id"
              :class="rowClass(row, index)"
            >
              <td class="whitespace-nowrap border border-slate-200 px-3 py-2 font-medium text-slate-800">
                {{ row.je_no }}
              </td>
              <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-slate-700">
                {{ row.date }}
              </td>
              <td class="border border-slate-200 px-3 py-2 text-slate-700">
                {{ row.transaction }}
              </td>
              <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-slate-800">
                {{ row.account_code }}
              </td>
              <td class="border border-slate-200 px-3 py-2 text-slate-800">
                {{ row.account_name }}
              </td>
              <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-slate-700">
                {{ row.party_ref || '' }}
              </td>
              <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-right tabular-nums text-slate-800">
                {{ formatAmount(row.debit) }}
              </td>
              <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-right tabular-nums text-slate-800">
                {{ formatAmount(row.credit) }}
              </td>
              <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-slate-700">
                {{ row.project_client || '' }}
              </td>
              <td
                v-if="isFirstLineOfJe(index)"
                :rowspan="rowspanForJe(row.je_no)"
                class="whitespace-nowrap border border-slate-200 px-3 py-2 align-top"
              >
                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium" :class="statusClass(row)">
                  {{ row.status }}
                </span>
              </td>
              <td
                v-if="isFirstLineOfJe(index)"
                :rowspan="rowspanForJe(row.je_no)"
                class="border border-slate-200 px-3 py-2 align-top text-slate-700"
              >
                <p v-if="row.created_by" class="text-sm font-medium text-slate-800">{{ row.created_by }}</p>
                <p v-if="row.created_at" class="mt-0.5 text-xs text-slate-500">{{ row.created_at }}</p>
                <p v-if="row.is_reversed && row.reversed_by" class="mt-2 text-xs text-rose-600">
                  {{ t('journals.reversed_by') }}: {{ row.reversed_by }}
                </p>
                <p v-if="row.is_reversed && row.reversed_at" class="text-xs text-rose-500">
                  {{ row.reversed_at }}
                </p>
              </td>
              <td
                v-if="isFirstLineOfJe(index)"
                :rowspan="rowspanForJe(row.je_no)"
                class="whitespace-nowrap border border-slate-200 px-3 py-2 align-top"
              >
                <div class="flex flex-col gap-1.5">
                  <BaseButton
                  v-can="'journal.view'"
                    className="bg-slate-100 text-slate-700 hover:bg-slate-200 px-3 py-1.5 text-xs"
                    @click="viewJournal(row.journal_id)"
                  >
                    {{ t('journals.view') }}
                  </BaseButton>
                  <BaseButton
                  v-can="'journal.reverse'"
                    v-if="row.can_reverse"
                    className="bg-rose-50 text-rose-700 hover:bg-rose-100 px-3 py-1.5 text-xs"
                    :disabled="reverseLoading"
                    @click="confirmReverse(row.journal_id, row.je_no)"
                  >
                    {{ reverseLoading ? t('journals.reversing') : t('journals.reverse') }}
                  </BaseButton>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="!isLoading" class="mt-4">
      <BasePagination
        :total="total"
        :showing="showing"
        :links="links"
        :per-page="perPage"
        @update:page="setPage"
        @update:perPage="setPerPage"
      />
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import { usePagination } from '@/shared/composables/usePagination'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { showConfirmDialog } from '@/shared/utils/sweetAlertUtils'
import { toast } from '@/shared/config/toastConfig'
import { flattenJournalRows } from '../data/postJournalStatic'
import { useJournalsQuery } from '../queries/useJournalsQuery'
import { useJournalMutations } from '../queries/useJournalMutations'

const router = useRouter()
const { t } = useTranslate()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  from_date: '',
  to_date: '',
})

const pagination = usePagination({ perPage: 25 })
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading, refetch } = useJournalsQuery(page, perPage, filters)
pagination.bindMeta(data)

const { reverse, reverseLoading } = useJournalMutations({
  onReverseSuccess: (response) => {
    const voucher = response?.data?.voucher_no || response?.voucher_no || ''
    toast.success(t('journals.reversed_success', { voucher }))
    refetch()
  },
})

const journals = computed(() => data.value?.data?.data ?? [])
const rows = computed(() => flattenJournalRows(journals.value))

const jeLineCounts = computed(() => {
  const counts = {}
  rows.value.forEach((row) => {
    counts[row.je_no] = (counts[row.je_no] || 0) + 1
  })
  return counts
})

const isFirstLineOfJe = (index) => {
  if (index === 0) return true
  return rows.value[index].je_no !== rows.value[index - 1].je_no
}

const rowspanForJe = (jeNo) => jeLineCounts.value[jeNo] || 1

const rowClass = (row, index) => {
  const groupIndex = [...new Set(rows.value.map((item) => item.je_no))].indexOf(row.je_no)
  const zebra = groupIndex % 2 === 0 ? 'bg-white' : 'bg-slate-50'
  const start = isFirstLineOfJe(index) ? 'border-t-2 border-t-[#1e4b8c]/30' : ''
  return `${zebra} ${start}`
}

const statusClass = (row) => {
  const raw = String(row.status_raw || row.status || '').toLowerCase()
  if (raw.includes('reversed')) return 'bg-rose-50 text-rose-700'
  if (raw.includes('posted')) return 'bg-emerald-50 text-emerald-700'
  if (raw.includes('approved')) return 'bg-blue-50 text-blue-700'
  if (raw.includes('return')) return 'bg-amber-50 text-amber-700'
  if (raw.includes('pending') || raw.includes('waiting')) return 'bg-amber-50 text-amber-700'
  return 'bg-slate-100 text-slate-700'
}

const viewJournal = (id) => {
  router.push({ name: 'Journal View', params: { id } })
}

const confirmReverse = async (id, voucher) => {
  const result = await showConfirmDialog({
    title: t('journals.reverse_confirm_title'),
    text: t('journals.reverse_confirm_text', { voucher }),
    icon: 'warning',
    confirmButtonText: t('journals.reverse_journal'),
    cancelButtonText: t('journals.close'),
    confirmButtonColor: '#e11d48',
  })

  if (result.isConfirmed) {
    await reverse.mutateAsync(id)
  }
}

const formatAmount = (value) => {
  if (value === null || value === undefined || value === '') return '—'
  return Number(value).toLocaleString('en-US')
}
</script>
