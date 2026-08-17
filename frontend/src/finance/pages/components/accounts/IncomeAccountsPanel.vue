<template>
  <div>
    <CreateIncomeAccountModal :income-type="incomeType" />
    <EditIncomeAccountModal :income-type="incomeType" />

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
      <div
        v-for="card in summaryCards"
        :key="card.title"
        class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="text-sm text-gray-500">{{ card.title }}</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ card.value }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ card.subtitle }}</p>
          </div>
          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
            :class="card.iconBg"
          >
            <i :class="[card.icon, card.iconColor, 'text-lg']"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
      <div class="xl:col-span-9">
        <div class="rounded-lg bg-white shadow-sm">
          <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:flex-wrap sm:items-end">
            <div class="flex flex-1 flex-col sm:min-w-[200px]">
              <label class="mb-1 text-sm text-gray-700">Search</label>
              <BaseInput v-model="filters.search" :placeholder="config.searchPlaceholder" />
            </div>

            <div class="flex flex-col sm:min-w-[160px]">
              <label class="mb-1 text-sm text-gray-700">Balance</label>
              <BaseSelect
                v-model="filters.balanceType"
                :options="balanceFilterOptions"
                placeholder="All Balances"
              />
            </div>

            <div class="flex flex-col sm:min-w-[140px]">
              <label class="mb-1 text-sm text-gray-700">Status</label>
              <BaseSelect
                v-model="filters.status"
                :options="statusFilterOptions"
                placeholder="Select status"
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

          <BaseTableSkeleton v-if="isLoading" :columns="columns" show-actions />
          <BaseTable
            v-else
            :columns="columns"
            :rows="paginatedRows"
            :current-page="page"
            :per-page="perPage"
            show-actions
            scrollable
          >
            <template #cell-account_name="{ row }">
              <div class="flex items-center gap-3">
                <div
                  class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50"
                >
                  <i :class="[config.rowIcon, 'text-emerald-600']"></i>
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ row.account_name || row.head_name }}</p>
                  <p class="text-xs text-gray-500">{{ row.category_name }}</p>
                </div>
              </div>
            </template>

            <template #cell-base_price="{ row }">
              <span class="text-sm text-gray-700">{{ formatCurrency(row.base_price) }}</span>
            </template>

            <template #cell-amount="{ row }">
              <span
                class="font-semibold"
                :class="
                  isApplicantLedgerAmountDebit(row.amount ?? row.balance)
                    ? 'text-red-700'
                    : 'text-green-700'
                "
              >
                {{
                  formatApplicantLedgerAmount(row.amount ?? row.balance, formatCurrency)
                }}
              </span>
            </template>

            <template #cell-status="{ row }">
              <span
                class="rounded-full px-3 py-1 text-xs font-semibold"
                :class="
                  row.status === 'Active'
                    ? 'bg-green-100 text-green-700'
                    : 'bg-red-100 text-red-700'
                "
              >
                {{ row.status ?? 'Active' }}
              </span>
            </template>

            <template #actions="{ row }">
              <div class="flex items-center justify-center gap-1">
                <router-link
                  v-can="'finance_account.view'"
                  :to="`/finance/accounts/${row.id}/ledger`"
                >
                  <BaseTableButton icon="fa fa-book" variant="primary" title="Ledger" />
                </router-link>
                <BaseTableButton
                  v-can="'finance_account.edit'"
                  icon="fa fa-pencil"
                  variant="success"
                  title="Edit"
                  @click="incomeStore.openEditModal(incomeType, row)"
                />
              </div>
            </template>
          </BaseTable>

          <BasePagination
            v-if="!isLoading"
            :total="filteredRows.length"
            :showing="showing"
            :links="links"
            :per-page="perPage"
            @update:page="setPage"
            @update:perPage="setPerPage"
          />
        </div>
      </div>

      <div class="space-y-6 xl:col-span-3">
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
          <h3 class="mb-4 text-base font-semibold text-gray-900">{{ config.summaryTitle }}</h3>
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-500">Amount</span>
            <span class="text-lg font-semibold text-gray-800">
              {{ formatCurrency(summary.totalAmount) }}
            </span>
          </div>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
          <h3 class="mb-4 text-base font-semibold text-gray-900">Quick Actions</h3>
          <ul class="space-y-2">
            <li v-for="action in quickActions" :key="action.label">
              <button
                v-can="'finance_account.view'"
                type="button"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm text-gray-700 transition-colors hover:bg-primary/5 hover:text-primary"
                @click="action.onClick?.()"
              >
                <i :class="[action.icon, 'w-4 text-center text-primary']"></i>
                {{ action.label }}
              </button>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import BaseTableSkeleton from '@/shared/components/base/BaseTableSkeleton.vue'
import { useIncomeAccountsStore } from '@/finance/store/incomeAccountsStore'
import { useIncomeCategoryStore } from '@/finance/store/incomeCategoryStore'
import { useIncomeHeadStore } from '@/finance/store/incomeHeadStore'
import { useFinanceAccountStore } from '@/finance/store/financeAccountStore'
import { getIncomeAccountConfig } from '@/finance/config/incomeAccountConfigs'
import { formatCurrency } from '@/finance/utils/billUtils'
import {
  formatApplicantLedgerAmount,
  isApplicantLedgerAmountDebit,
} from '@/finance/utils/partyLedgerCsvUtils'
import { partyAccountStatusFilterOptions } from '@/finance/data/partyAccountConstants'
import CreateIncomeAccountModal from './CreateIncomeAccountModal.vue'
import EditIncomeAccountModal from './EditIncomeAccountModal.vue'

const props = defineProps({
  incomeType: {
    type: String,
    required: true,
  },
})

const config = computed(() => getIncomeAccountConfig(props.incomeType))
const incomeStore = useIncomeAccountsStore()
const categoryStore = useIncomeCategoryStore()
const headStore = useIncomeHeadStore()
const financeAccountStore = useFinanceAccountStore()
const router = useRouter()

const isLoading = computed(() => {
  const category = config.value?.accountCategory
  return category ? financeAccountStore.isCategoryLoading(category) : false
})

const balanceFilterOptions = [
  { value: 'positive', label: 'Positive Balance' },
  { value: 'negative', label: 'Negative Balance' },
  { value: 'zero', label: 'Zero Balance' },
]

const statusFilterOptions = partyAccountStatusFilterOptions

const filters = reactive({
  search: '',
  balanceType: '',
  status: 'Active',
})

const columns = [
  { key: 'sl', label: 'SL' },
  { key: 'account_name', label: 'Account Name' },
  { key: 'base_price', label: 'Base Price' },
  { key: 'amount', label: 'Amount' },
  { key: 'status', label: 'Status' },
]

const accounts = computed(() => incomeStore.getAccounts(props.incomeType))

const summary = computed(() => {
  const list = accounts.value
  const totalAmount = list.reduce(
    (sum, account) => sum + Number(account.amount ?? account.balance ?? 0),
    0
  )

  return {
    totalAccounts: list.length,
    totalAmount,
    withAmount: list.filter(
      (account) => Number(account.amount ?? account.balance ?? 0) !== 0
    ).length,
    zeroAmount: list.filter(
      (account) => Number(account.amount ?? account.balance ?? 0) === 0
    ).length,
  }
})

const summaryCards = computed(() => [
  {
    title: 'Total Accounts',
    value: summary.value.totalAccounts,
    subtitle: `${config.value.incomeTypeLabel} income heads`,
    icon: 'fa fa-list',
    iconBg: 'bg-blue-50',
    iconColor: 'text-blue-600',
  },
  {
    title: 'Total Amount',
    value: formatCurrency(summary.value.totalAmount),
    subtitle: 'Combined ledger amount',
    icon: 'fa fa-money',
    iconBg: 'bg-green-50',
    iconColor: 'text-green-600',
  },
  {
    title: 'With Amount',
    value: summary.value.withAmount,
    subtitle: 'Accounts with collections',
    icon: 'fa fa-arrow-up',
    iconBg: 'bg-emerald-50',
    iconColor: 'text-emerald-600',
  },
  {
    title: 'Zero Amount',
    value: summary.value.zeroAmount,
    subtitle: 'No collections yet',
    icon: 'fa fa-minus-circle',
    iconBg: 'bg-amber-50',
    iconColor: 'text-amber-600',
  },
])

const quickActions = computed(() => [
  {
    label: `Create ${config.value.incomeTypeLabel} Account`,
    icon: 'fa fa-plus',
    onClick: () => incomeStore.openCreateModal(props.incomeType),
  },
  {
    label: 'Income Setup',
    icon: 'fa fa-tags',
    onClick: () => router.push('/finance/income-management'),
  },
  {
    label: 'Main Accounts',
    icon: 'fa fa-bank',
    onClick: () => router.replace({ path: '/finance/accounts' }),
  },
])

const page = ref(1)
const perPage = ref(10)
const showing = ref(0)
const links = ref([])

const filteredRows = computed(() => {
  const query = filters.search.trim().toLowerCase()

  return accounts.value.filter((account) => {
    const matchesSearch =
      !query ||
      String(account.account_name ?? '')
        .toLowerCase()
        .includes(query) ||
      String(account.head_name ?? '')
        .toLowerCase()
        .includes(query) ||
      String(account.category_name ?? '')
        .toLowerCase()
        .includes(query)

    const balance = Number(account.amount ?? account.balance ?? 0)
    const matchesBalance =
      !filters.balanceType ||
      (filters.balanceType === 'positive' && balance > 0) ||
      (filters.balanceType === 'negative' && balance < 0) ||
      (filters.balanceType === 'zero' && balance === 0)

    const status = String(account.status || 'Active')
    const matchesStatus =
      !filters.status ||
      filters.status === 'all' ||
      status.toLowerCase() === String(filters.status).toLowerCase()

    return matchesSearch && matchesBalance && matchesStatus
  })
})

const paginatedRows = computed(() => {
  const start = (page.value - 1) * perPage.value
  return filteredRows.value.slice(start, start + perPage.value).map((row, index) => ({
    ...row,
    sl: start + index + 1,
  }))
})

const hasActiveFilters = computed(() =>
  Boolean(
    filters.search ||
      filters.balanceType ||
      (filters.status && filters.status !== 'Active')
  )
)

const resetFilters = () => {
  filters.search = ''
  filters.balanceType = ''
  filters.status = 'Active'
  page.value = 1
}

const updatePagination = () => {
  const count = filteredRows.value.length
  const lastPage = Math.max(1, Math.ceil(count / perPage.value))
  const to = Math.min(page.value * perPage.value, count)

  showing.value = to
  links.value = Array.from({ length: lastPage }, (_, index) => ({
    label: String(index + 1),
    active: page.value === index + 1,
    url: page.value === index + 1 ? null : '#',
  }))

  if (page.value > lastPage) {
    page.value = lastPage
  }
}

watch([filteredRows, page, perPage, () => accounts.value.length], updatePagination, {
  immediate: true,
})

watch(
  () => props.incomeType,
  () => {
    resetFilters()
  }
)

const setPage = (value) => {
  if (value && value !== page.value) {
    page.value = value
  }
}

const setPerPage = (value) => {
  perPage.value = Number(value)
  page.value = 1
}

onMounted(async () => {
  await Promise.all([
    categoryStore.fetchCategories(true),
    headStore.fetchHeads({ force: true, page: 1, perPage: 300 }),
  ])
})
</script>
