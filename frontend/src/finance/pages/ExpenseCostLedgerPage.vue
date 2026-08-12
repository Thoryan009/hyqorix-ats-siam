<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <div class="mb-1 flex items-center gap-2 text-sm text-gray-500">
          <router-link to="/finance/accounts" class="hover:text-green-700">Accounts</router-link>
          <span>/</span>
          <span class="text-gray-700">{{ config?.label ?? 'Expense Cost Ledger' }}</span>
        </div>
        <PageTitle>Expense Head Account Ledger</PageTitle>
        <p v-if="account" class="mt-1 text-sm text-gray-600">
          {{ account.head_name }} · {{ account.category_name }}
        </p>
      </div>
    </PageHeader>

    <div v-if="isLoading" class="rounded-lg bg-white p-6 text-center text-gray-500 shadow-sm">
      Loading expense head ledger...
    </div>

    <div v-else-if="!account" class="rounded-lg bg-white p-6 text-center text-gray-500 shadow-sm">
      Account not found.
      <router-link
        :to="{ path: '/finance/accounts', query: { tab: config?.tabId } }"
        class="ml-2 text-green-700 hover:underline"
      >
        Back to Accounts
      </router-link>
    </div>

    <template v-else>
      <div class="mb-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div class="space-y-2">
            <BaseLabel for="from_date">From Date</BaseLabel>
            <BaseInput id="from_date" v-model="fromDate" type="date" />
          </div>
          <div class="space-y-2">
            <BaseLabel for="to_date">To Date</BaseLabel>
            <BaseInput id="to_date" v-model="toDate" type="date" />
          </div>
          <div class="flex items-end gap-2">
            <BaseButton class="bg-gray-100 text-gray-800 hover:bg-gray-200" @click="clearDateFilter">
              Clear Filter
            </BaseButton>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <div class="xl:col-span-9">
          <div class="rounded-lg bg-white shadow-sm">
            <div class="border-b border-gray-200 px-4 py-3">
              <h3 class="text-base font-semibold text-gray-800">Ledger Statement</h3>
              <p class="mt-1 text-sm text-gray-600">
                {{ account.head_name }} · Base Price: {{ formatCurrency(account.base_price) }}
                <span v-if="fromDate || toDate" class="ml-2">
                  | Period:
                  {{ fromDate ? formatDisplayDate(fromDate) : 'Start' }}
                  to
                  {{ toDate ? formatDisplayDate(toDate) : 'End' }}
                </span>
              </p>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full border-collapse text-xs">
                <thead class="bg-gray-50">
                  <tr>
                    <th
                      v-for="column in columns"
                      :key="column.key"
                      class="border-b border-gray-200 px-2 py-2 text-left whitespace-nowrap"
                      :class="amountColumnClass(column.key)"
                    >
                      {{ column.label }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!filteredRows.length">
                    <td :colspan="columns.length" class="px-3 py-6 text-center text-gray-500">
                      No ledger entries found for the selected date range.
                    </td>
                  </tr>
                  <tr
                    v-for="row in paginatedRows"
                    :key="row.id"
                    class="border-b border-gray-100 hover:bg-gray-50"
                  >
                    <td class="px-2 py-2 whitespace-nowrap">{{ formatDisplayDate(row.date) }}</td>
                    <td class="px-2 py-2 font-medium">{{ row.particular }}</td>
                    <td class="px-2 py-2">{{ row.voucher_no || '—' }}</td>
                    <td class="px-2 py-2">{{ row.demand_letter || '—' }}</td>
                    <td class="px-2 py-2">{{ row.job || '—' }}</td>
                    <td class="px-2 py-2">{{ row.client_name || '—' }}</td>
                    <td class="px-2 py-2 text-right text-red-700">
                      {{ row.dr_amount ? formatCurrency(row.dr_amount) : '—' }}
                    </td>
                    <td class="px-2 py-2 text-right text-green-700">
                      {{ formatLedgerCreditAmount(row.cr_amount, formatCurrency) }}
                    </td>
                    <td class="px-2 py-2">
                      <span
                        v-if="row.bill_status"
                        class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase"
                        :class="billStatusClass(row.bill_status)"
                      >
                        {{ row.bill_status }}
                      </span>
                      <span v-else>—</span>
                    </td>
                    <td
                      class="px-2 py-2 text-right font-semibold"
                      :class="
                        isExpenseLedgerAmountDebit(row.balance)
                          ? 'text-red-700'
                          : 'text-green-700'
                      "
                    >
                      {{ formatExpenseLedgerAmount(row.balance, formatCurrency) }}
                    </td>
                    <td class="px-2 py-2 text-gray-600">{{ row.remarks || '—' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="filteredRows.length" class="mt-3">
              <BasePagination
                :total="filteredRows.length"
                :showing="showing"
                :links="links"
                :per-page="perPage"
                :per-page-options="perPageOptions"
                @update:page="setPage"
                @update:perPage="setPerPage"
              />
            </div>
          </div>

          <div class="mt-4">
            <router-link :to="{ path: '/finance/accounts', query: { tab: config?.tabId } }">
              <BaseButton :className="'bg-white text-gray-800 ring-1 ring-gray-300 hover:bg-gray-50'">
                <i class="fa fa-arrow-left mr-1"></i> Back to Accounts
              </BaseButton>
            </router-link>
          </div>
        </div>

        <div class="space-y-6 xl:col-span-3">
          <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-gray-900">Account Summary</h3>
            <dl class="space-y-3 text-sm">
              <div class="flex items-start justify-between gap-3">
                <dt class="text-gray-500">Expense Head</dt>
                <dd class="text-right font-medium text-gray-900">{{ account.head_name }}</dd>
              </div>
              <div class="flex items-start justify-between gap-3">
                <dt class="text-gray-500">Category</dt>
                <dd class="text-right text-gray-800">{{ account.category_name }}</dd>
              </div>
              <div class="flex items-start justify-between gap-3">
                <dt class="text-gray-500">Base Price</dt>
                <dd class="text-right text-gray-800">{{ formatCurrency(account.base_price) }}</dd>
              </div>
              <div class="flex items-start justify-between gap-3">
                <dt class="text-gray-500">Status</dt>
                <dd class="text-right">
                  <span
                    class="rounded-full px-3 py-1 text-xs font-semibold"
                    :class="
                      account.status === 'Active'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-red-100 text-red-700'
                    "
                  >
                    {{ account.status ?? 'Active' }}
                  </span>
                </dd>
              </div>
              <div class="border-t border-gray-100 pt-3">
                <div class="flex items-center justify-between">
                  <dt class="font-medium text-gray-700">Amount</dt>
                  <dd class="text-lg font-semibold" :class="isExpenseLedgerAmountDebit(accountAmount) ? 'text-red-700' : 'text-green-700'">
                    {{ formatExpenseLedgerAmount(accountAmount, formatCurrency) }}
                  </dd>
                </div>
              </div>
            </dl>
          </div>

          <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-gray-900">Ledger Summary</h3>
            <dl class="space-y-3 text-sm">
              <div class="flex items-center justify-between">
                <dt class="text-gray-500">Total Entries</dt>
                <dd class="font-medium text-gray-900">{{ ledgerRows.length }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-gray-500">Filtered Entries</dt>
                <dd class="font-medium text-gray-900">{{ filteredRows.length }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-gray-500">Total DR (Bills)</dt>
                <dd class="font-medium text-red-700">{{ formatCurrency(ledgerTotals.drAmount) }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-gray-500">Total CR (Received)</dt>
                <dd class="font-medium text-green-700">{{ formatCurrency(ledgerTotals.crAmount) }}</dd>
              </div>
            </dl>
          </div>

          <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-gray-900">Quick Actions</h3>
            <ul class="space-y-2">
              <li v-for="action in quickActions" :key="action.label">
                <button
                  v-can="'transaction.view'"
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
    </template>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useExpenseCostAccountsStore } from '@/finance/store/expenseCostAccountsStore'
import { useAccountLedgerStore } from '@/finance/store/accountLedgerStore'
import { useFinanceAccountStore } from '@/finance/store/financeAccountStore'
import { getExpenseCostConfig } from '@/finance/config/expenseCostAccountConfigs'
import { filterLedgerByDate } from '@/finance/data/agentLedgerData'
import { formatCurrency, formatDisplayDate } from '@/finance/utils/billUtils'
import {
  formatExpenseLedgerAmount,
  formatLedgerCreditAmount,
  isExpenseLedgerAmountDebit,
} from '@/finance/utils/partyLedgerCsvUtils'
import BasePagination from '@/shared/components/base/BasePagination.vue'
import { useClientLedgerPagination } from '@/finance/composables/useClientLedgerPagination'

const route = useRoute()
const router = useRouter()
const costAccountsStore = useExpenseCostAccountsStore()
const ledgerStore = useAccountLedgerStore()
const financeAccountStore = useFinanceAccountStore()

const fromDate = ref('')
const toDate = ref('')
const isLoading = ref(true)

const costType = computed(() => route.params.costType)
const accountId = computed(() => Number(route.params.accountId))
const config = computed(() => getExpenseCostConfig(costType.value))
const account = ref(null)
const ledgerRows = ref([])

async function loadLedgerPage(id = accountId.value) {
  if (!id || !costType.value) {
    account.value = null
    ledgerRows.value = []
    isLoading.value = false
    return
  }

  isLoading.value = true

  try {
    await costAccountsStore.fetchAccounts(costType.value, true)
    account.value = costAccountsStore.getAccount(costType.value, id)

    if (!account.value) {
      const fetchedAccount = await financeAccountStore.fetchAccountById(id)
      account.value = fetchedAccount
    }

    ledgerRows.value = account.value ? await ledgerStore.fetchAccountLedger(id) : []
  } catch {
    account.value = null
    ledgerRows.value = []
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadLedgerPage()
})

watch(accountId, (id) => {
  loadLedgerPage(id)
})

const columns = [
  { key: 'date', label: 'Date' },
  { key: 'particular', label: 'Particular' },
  { key: 'voucher_no', label: 'Bill No' },
  { key: 'demand_letter', label: 'Demand Letter' },
  { key: 'job', label: 'Job' },
  { key: 'client_name', label: 'Reference' },
  { key: 'dr_amount', label: 'DR (Bill)' },
  { key: 'cr_amount', label: 'CR (Received)' },
  { key: 'bill_status', label: 'Bill Status' },
  { key: 'balance', label: 'Amount' },
  { key: 'remarks', label: 'Remarks' },
]

const filteredRows = computed(() =>
  filterLedgerByDate(ledgerRows.value, fromDate.value, toDate.value)
)

const {
  perPage,
  perPageOptions,
  showing,
  links,
  paginatedRows,
  setPage,
  setPerPage,
  resetPage,
} = useClientLedgerPagination(filteredRows)

watch([fromDate, toDate], () => {
  resetPage()
})

const ledgerTotals = computed(() =>
  ledgerRows.value.reduce(
    (totals, row) => ({
      drAmount: totals.drAmount + (Number(row.dr_amount) || 0),
      crAmount: totals.crAmount + (Number(row.cr_amount) || 0),
    }),
    { drAmount: 0, crAmount: 0 }
  )
)

const accountAmount = computed(() => {
  if (account.value?.balance != null) {
    return Number(account.value.balance) || 0
  }

  const rows = ledgerRows.value
  if (!rows.length) return 0

  return Number(rows[rows.length - 1].balance) || 0
})

const quickActions = computed(() => [
  {
    label: 'Back to Accounts',
    icon: 'fa fa-arrow-left',
    onClick: () =>
      router.push({ path: '/finance/accounts', query: { tab: config.value?.tabId } }),
  },
  {
    label: 'Clear Date Filter',
    icon: 'fa fa-filter',
    onClick: clearDateFilter,
  },
])

function amountColumnClass(key) {
  if (['dr_amount', 'cr_amount', 'balance'].includes(key)) {
    return 'text-right'
  }
  return ''
}

function billStatusClass(status) {
  const classes = {
    pending: 'bg-amber-100 text-amber-700',
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
  }

  return classes[status] || 'bg-gray-100 text-gray-700'
}

function clearDateFilter() {
  fromDate.value = ''
  toDate.value = ''
}
</script>
