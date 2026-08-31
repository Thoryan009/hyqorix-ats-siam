<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ t('journals.party_ledger_title') }}</PageTitle>
        <p class="mt-1 text-sm text-gray-500">{{ t('journals.party_ledger_subtitle') }}</p>
      </div>
    </PageHeader>

    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
      <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-3.5">
        <div class="flex items-start gap-3">
          <span
            class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700"
          >
            <i class="fa fa-filter text-sm"></i>
          </span>
          <div>
            <p class="text-sm font-semibold text-slate-900">{{ t('journals.party_ledger_filters') }}</p>
            <p class="mt-0.5 max-w-2xl text-xs leading-relaxed text-slate-500">
              {{ t('journals.party_ledger_note') }}
            </p>
          </div>
        </div>
        <BaseButton
          v-if="hasActiveFilters"
          className="border border-slate-200 bg-white text-slate-700 hover:bg-slate-50"
          @click="resetFilters"
        >
          <i class="fa fa-undo mr-1.5"></i>
          {{ t('shared.actions.reset') }}
        </BaseButton>
      </div>

      <div class="grid grid-cols-1 gap-4 p-4 md:grid-cols-2 xl:grid-cols-3">
        <div class="space-y-1.5">
          <label class="text-xs font-medium uppercase tracking-wide text-slate-500" for="ledger_from_date">
            {{ t('shared.filters.from_date') }}
          </label>
          <BaseInput
            id="ledger_from_date"
            v-model="filters.from_date"
            type="date"
            className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100"
          />
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-medium uppercase tracking-wide text-slate-500" for="ledger_to_date">
            {{ t('shared.filters.to_date') }}
          </label>
          <BaseInput
            id="ledger_to_date"
            v-model="filters.to_date"
            type="date"
            className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100"
          />
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-medium uppercase tracking-wide text-slate-500" for="ledger_search">
            {{ t('shared.actions.search') }}
          </label>
          <BaseInput
            id="ledger_search"
            v-model="filters.searchQuery"
            :placeholder="t('journals.party_ledger_search_placeholder')"
            className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100"
          />
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-medium uppercase tracking-wide text-slate-500" for="ledger_party_type">
            {{ t('journals.party_type') }}
          </label>
          <BaseSelect
            id="ledger_party_type"
            v-model="filters.party_type"
            :options="partyLedgerTypeOptions"
            :placeholder="t('journals.all_party_types')"
          />
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-medium uppercase tracking-wide text-slate-500" for="ledger_party">
            {{ t('journals.party_ledger_party') }}
          </label>
          <BaseSearchSelect
            id="ledger_party"
            v-model="filters.party_id"
            :options="partyOptions"
            :placeholder="partyPlaceholder"
            :disabled="isPartyLoading"
            :filter-fn="filterPartyOption"
            teleport-dropdown
            list-class-name="max-h-72"
          />
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-medium uppercase tracking-wide text-slate-500" for="ledger_party_ref">
            {{ t('journals.party_ref') }}
          </label>
          <BaseInput
            id="ledger_party_ref"
            v-model="filters.party_ref"
            :placeholder="t('journals.party_ref_filter_placeholder')"
            className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100"
          />
        </div>
      </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
      <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 bg-slate-50 px-4 py-3">
        <h2 class="text-base font-semibold text-slate-800">
          {{ t('journals.party_ledger_sheet_title') }}
        </h2>
        <span
          v-if="!isLoading && rows.length"
          class="inline-flex items-center rounded-full bg-slate-200/70 px-2.5 py-0.5 text-[11px] font-semibold text-slate-700"
        >
          {{ t('journals.party_ledger_results', { count: total || rows.length }) }}
        </span>
      </div>

      <div v-if="isLoading" class="px-4 py-10 text-center text-sm text-slate-500">
        {{ t('journals.party_ledger_loading') }}
      </div>

      <div v-else-if="!rows.length" class="px-4 py-10 text-center text-sm text-slate-500">
        {{ t('journals.party_ledger_empty') }}
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
          <thead>
            <tr class="bg-[#1e4b8c] text-left text-white">
              <th
                v-for="column in columns"
                :key="column.key"
                class="whitespace-nowrap border border-[#163a6d] px-3 py-2.5 font-semibold"
                :class="column.align === 'right' ? 'text-right' : 'text-left'"
              >
                {{ column.label }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="item in displayRows"
              :key="item.key"
              :class="item.kind === 'group' ? 'bg-slate-100/90' : rowClass(item.row)"
            >
              <template v-if="item.kind === 'group'">
                <td :colspan="columns.length" class="border border-slate-200 px-3 py-2">
                  <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex rounded-full bg-indigo-100 px-2.5 py-0.5 text-[11px] font-semibold text-indigo-800">
                      {{ item.row.party_type }}
                    </span>
                    <span class="text-sm font-semibold text-slate-900">{{ item.row.party_ref }}</span>
                  </div>
                </td>
              </template>
              <template v-else>
                <td class="whitespace-nowrap border border-slate-200 px-3 py-2 font-medium text-slate-800">
                  {{ item.row.party_ref }}
                </td>
                <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-slate-700">
                  {{ item.row.party_type }}
                </td>
                <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-slate-700">
                  {{ item.row.date_label || item.row.date }}
                </td>
                <td class="whitespace-nowrap border border-slate-200 px-3 py-2 font-medium text-slate-800">
                  {{ item.row.je_no }}
                </td>
                <td class="min-w-[220px] border border-slate-200 px-3 py-2 text-slate-700">
                  {{ item.row.particulars }}
                </td>
                <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-slate-800">
                  {{ item.row.account_code || '—' }}
                </td>
                <td class="min-w-[180px] border border-slate-200 px-3 py-2 text-slate-800">
                  {{ item.row.account_name || '—' }}
                </td>
                <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-right tabular-nums text-slate-800">
                  {{ formatAmount(item.row.debit) }}
                </td>
                <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-right tabular-nums text-slate-800">
                  {{ formatAmount(item.row.credit) }}
                </td>
                <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-right tabular-nums font-semibold text-slate-900">
                  {{ formatAmount(item.row.running_balance) }}
                </td>
                <td class="whitespace-nowrap border border-slate-200 px-3 py-2 text-center">
                  <span
                    class="inline-flex min-w-[2rem] justify-center rounded px-2 py-0.5 text-xs font-bold"
                    :class="item.row.balance_type === 'Dr' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700'"
                  >
                    {{ item.row.balance_type }}
                  </span>
                </td>
              </template>
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
import { computed, watch } from 'vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import { usePagination } from '@/shared/composables/usePagination'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { usePartyLedgerQuery } from '../queries/usePartyLedgerQuery'
import { usePartyLedgerOptionsQuery } from '../queries/usePartyLedgerOptionsQuery'
import { partyLedgerTypeOptions } from '../data/partyLedgerOptions'

const { t } = useTranslate()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  from_date: '',
  to_date: '',
  party_type: '',
  party_id: '',
  party_ref: '',
})

const pagination = usePagination({ perPage: 50 })
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = usePartyLedgerQuery(page, perPage, filters)
pagination.bindMeta(data)

const partyTypeRef = computed(() => filters.party_type)
const { data: partiesData, isLoading: isPartyLoading } = usePartyLedgerOptionsQuery(partyTypeRef)

const partyOptions = computed(() => {
  const parties = partiesData.value?.data?.data ?? []
  return [
    { id: '', code: '', name: t('journals.all_parties') },
    ...parties.map((party) => ({
      id: party.id,
      name: `${party.code} – ${party.name}`,
      code: party.code,
    })),
  ]
})

const partyPlaceholder = computed(() =>
  isPartyLoading.value ? t('journals.loading_parties') : t('journals.select_party'),
)

watch(
  () => filters.party_type,
  () => {
    filters.party_id = ''
  },
)

const rows = computed(() => data.value?.data?.data ?? [])

const displayRows = computed(() => {
  const items = []
  let lastPartyKey = ''

  rows.value.forEach((row) => {
    const partyKey = `${row.party_type}::${row.party_ref}`

    if (partyKey !== lastPartyKey) {
      items.push({
        key: `group-${partyKey}`,
        kind: 'group',
        row,
      })
      lastPartyKey = partyKey
    }

    items.push({
      key: `row-${row.id}`,
      kind: 'entry',
      row,
    })
  })

  return items
})

const columns = computed(() => [
  { key: 'party_ref', label: t('journals.party_ref') },
  { key: 'party_type', label: t('journals.party_type') },
  { key: 'date', label: t('journals.date') },
  { key: 'je_no', label: t('journals.je_no') },
  { key: 'particulars', label: t('journals.particulars') },
  { key: 'account_code', label: t('journals.account_code') },
  { key: 'account_name', label: t('journals.account_name') },
  { key: 'debit', label: t('journals.debit'), align: 'right' },
  { key: 'credit', label: t('journals.credit'), align: 'right' },
  { key: 'running_balance', label: t('journals.running_balance'), align: 'right' },
  { key: 'balance_type', label: t('journals.balance_type'), align: 'center' },
])

const rowClass = (row) => {
  const partyKey = `${row.party_type}::${row.party_ref}`
  const partyKeys = [...new Set(rows.value.map((item) => `${item.party_type}::${item.party_ref}`))]
  const groupIndex = partyKeys.indexOf(partyKey)
  return groupIndex % 2 === 0 ? 'bg-white' : 'bg-slate-50/80'
}

const filterPartyOption = (option, query) => {
  const label = String(option?.name ?? '').toLowerCase()
  const code = String(option?.code ?? '').toLowerCase()
  return label.includes(query) || code.includes(query)
}

const formatAmount = (value) => {
  if (value === null || value === undefined || value === '') return ''
  return Number(value).toLocaleString('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  })
}
</script>
