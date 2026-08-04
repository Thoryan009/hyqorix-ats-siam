<template>
  <div>
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
      <div class="flex flex-1 flex-col sm:min-w-[200px]">
        <label class="mb-1 text-sm text-gray-700">Search</label>
        <BaseInput v-model="filters.search" placeholder="Search by particular, reference, account..." />
      </div>

      <div class="flex flex-col sm:min-w-[180px]">
        <label class="mb-1 text-sm text-gray-700">Transaction Type</label>
        <BaseSelect
          v-model="filters.transactionType"
          :options="transactionTypeFilterOptions"
          placeholder="All Types"
        />
      </div>

      <BaseButton
        v-if="hasActiveFilters"
        class="bg-gray-600 text-white hover:bg-gray-700"
        @click="resetFilters"
      >
        Reset
      </BaseButton>
    </div>

    <BaseTable
      :columns="columns"
      :rows="paginatedRows"
      :current-page="page"
      :per-page="perPage"
      scrollable
    >
      <template #cell-date="{ row }">
        {{ formatDisplayDate(row.date) }}
      </template>

      <template #cell-transaction_type="{ row }">
        <span
          class="rounded-full px-2.5 py-1 text-xs font-semibold"
          :class="typeBadgeClass(row.transaction_type)"
        >
          {{ getTransactionTypeLabel(row.transaction_type) }}
        </span>
      </template>

      <template #cell-from_account="{ row }">
        <div v-if="row.from_account_label" class="text-sm text-gray-800">
          <p class="font-medium">{{ row.from_account_label }}</p>
          <p class="text-xs text-gray-500">{{ getAccountCategoryLabel(row.from_account_category) }}</p>
        </div>
        <span v-else class="text-gray-400">—</span>
      </template>

      <template #cell-to_account="{ row }">
        <div v-if="row.to_account_label" class="text-sm text-gray-800">
          <p class="font-medium">{{ row.to_account_label }}</p>
          <p class="text-xs text-gray-500">{{ getAccountCategoryLabel(row.to_account_category) }}</p>
        </div>
        <div v-else-if="row.account_label" class="text-sm text-gray-800">
          <p class="font-medium">{{ row.account_label }}</p>
          <p class="text-xs text-gray-500">{{ getAccountCategoryLabel(row.account_category) }}</p>
        </div>
        <span v-else class="text-gray-400">—</span>
      </template>

      <template #cell-amount="{ row }">
        <span class="font-semibold text-gray-900">{{ formatCurrency(row.amount) }}</span>
      </template>
    </BaseTable>

    <BasePagination
      :total="filteredRows.length"
      :showing="showing"
      :links="links"
      :per-page="perPage"
      @update:page="setPage"
      @update:perPage="setPerPage"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useAccountTransactionStore } from '@/finance/store/accountTransactionStore'
import {
  accountTransactionDisplayTypes,
  getAccountCategoryLabel,
  getTransactionTypeLabel,
} from '@/finance/data/accountTransactionData'
import { formatCurrency, formatDisplayDate } from '@/finance/utils/billUtils'

const transactionStore = useAccountTransactionStore()

const filters = reactive({
  search: '',
  transactionType: '',
})

const columns = [
  { key: 'sl', label: 'SL' },
  { key: 'date', label: 'Date' },
  { key: 'transaction_type', label: 'Type' },
  { key: 'particular', label: 'Particular' },
  { key: 'from_account', label: 'From / Account' },
  { key: 'to_account', label: 'To / Adjusted' },
  { key: 'amount', label: 'Amount' },
]

const transactionTypeFilterOptions = computed(() => {
  const known = accountTransactionDisplayTypes.map((type) => ({
    id: type.id,
    name: type.label,
  }))

  const knownIds = new Set(known.map((item) => item.id))
  const dynamic = []

  for (const row of transactionStore.transactions) {
    const type = String(row.transaction_type || '').trim()
    if (!type || knownIds.has(type)) continue
    knownIds.add(type)
    dynamic.push({ id: type, name: type })
  }

  dynamic.sort((a, b) => a.name.localeCompare(b.name))
  return [...known, ...dynamic]
})

const page = ref(1)
const perPage = ref(10)
const showing = ref(0)
const links = ref([])

const filteredRows = computed(() => {
  const search = filters.search.trim().toLowerCase()

  return transactionStore.transactions.filter((row) => {
    if (filters.transactionType && row.transaction_type !== filters.transactionType) {
      return false
    }

    if (!search) return true

    const haystack = [
      row.particular,
      row.reference_no,
      row.remarks,
      row.from_account_label,
      row.to_account_label,
      row.account_label,
      getTransactionTypeLabel(row.transaction_type),
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()

    return haystack.includes(search)
  })
})

const paginatedRows = computed(() => {
  const start = (page.value - 1) * perPage.value
  return filteredRows.value.slice(start, start + perPage.value).map((row, index) => ({
    ...row,
    sl: start + index + 1,
  }))
})

const hasActiveFilters = computed(() => Boolean(filters.search.trim() || filters.transactionType))

function resetFilters() {
  filters.search = ''
  filters.transactionType = ''
  page.value = 1
}

function setPage(value) {
  page.value = value
}

function setPerPage(value) {
  perPage.value = Number(value)
  page.value = 1
}

function updatePagination() {
  const count = filteredRows.value.length
  const lastPage = Math.max(1, Math.ceil(count / perPage.value))
  const to = Math.min(page.value * perPage.value, count)

  if (page.value > lastPage) {
    page.value = lastPage
  }

  showing.value = count ? to : 0
  links.value = Array.from({ length: lastPage }, (_, index) => ({
    label: String(index + 1),
    active: page.value === index + 1,
    url: page.value === index + 1 ? null : '#',
  }))
}

watch([filteredRows, page, perPage], updatePagination, { immediate: true })

onMounted(async () => {
  await transactionStore.fetchTransactions(true)
})

function typeBadgeClass(type) {
  const classes = {
    loan: 'bg-violet-100 text-violet-700',
    advanced: 'bg-blue-100 text-blue-700',
    loan_repay: 'bg-emerald-100 text-emerald-700',
    advanced_repay: 'bg-teal-100 text-teal-700',
    adjust_minus: 'bg-red-100 text-red-700',
    adjust_plus: 'bg-amber-100 text-amber-700',
    bill_payment: 'bg-orange-100 text-orange-700',
    receive_payment: 'bg-emerald-100 text-emerald-800',
    opening_balance: 'bg-indigo-100 text-indigo-700',
    deposit: 'bg-cyan-100 text-cyan-700',
    withdraw: 'bg-rose-100 text-rose-700',
    transfer: 'bg-slate-100 text-slate-700',
  }

  const normalized = String(type || '').toLowerCase()
  if (normalized.includes('given') || normalized.includes('asset')) {
    return 'bg-sky-100 text-sky-800'
  }
  if (normalized.includes('receipt') || normalized.includes('liabilit')) {
    return 'bg-amber-100 text-amber-800'
  }

  return classes[type] || 'bg-gray-100 text-gray-700'
}
</script>
