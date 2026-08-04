<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <div class="mb-1 flex items-center gap-2 text-sm text-gray-500">
          <router-link to="/finance/accounts" class="hover:text-green-700">
            Accounts
          </router-link>
          <span>/</span>
          <span class="text-gray-700">Account Ledger</span>
        </div>
        <PageTitle>Account Ledger</PageTitle>
        <p v-if="account" class="mt-1 text-sm text-gray-600">
          {{ account.account_name || account.head_name }}
          <span v-if="account.account_label"> - {{ account.account_label }}</span>
          <span v-else-if="account.category_name"> · {{ account.category_name }}</span>
        </p>
      </div>

      <div v-if="account" class="flex flex-wrap items-center gap-2">
        <BaseButton class="bg-blue-600 text-white hover:bg-blue-700" @click="handleExportCsv">
          <i class="fa fa-download mr-1"></i> Export CSV
        </BaseButton>
        <BaseButton class="bg-green-600 text-white hover:bg-green-700" @click="handlePrint">
          <i class="fa fa-print mr-1"></i> Print / PDF
        </BaseButton>
      </div>
    </PageHeader>

    <div v-if="isLoading" class="rounded-lg bg-white p-6 text-center text-gray-500 shadow-sm">
      Loading account ledger...
    </div>

    <div v-else-if="!account" class="rounded-lg bg-white p-6 text-center text-gray-500 shadow-sm">
      Account not found.
      <router-link to="/finance/accounts" class="ml-2 text-green-700 hover:underline">
        Back to Accounts
      </router-link>
    </div>

    <template v-else>
      <div class="mb-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm print:hidden">
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

      <div id="account-ledger-print" class="rounded-lg bg-white shadow-sm">
        <div class="border-b border-gray-200 px-4 py-3 print:border-gray-300">
          <h3 class="text-base font-semibold text-gray-800">Ledger Statement</h3>
          <p class="mt-1 text-sm text-gray-600">
            {{ account.account_name }} - {{ account.account_label }}
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
                v-for="row in filteredRows"
                :key="row.id"
                class="border-b border-gray-100 hover:bg-gray-50"
              >
                <td class="px-2 py-2 whitespace-nowrap">{{ formatDisplayDate(row.date) }}</td>
                <td class="px-2 py-2 font-medium">{{ row.particular }}</td>
                <td class="px-2 py-2">{{ row.voucher_no || '-' }}</td>
                <td class="px-2 py-2">{{ row.demand_letter || '-' }}</td>
                <td class="px-2 py-2">{{ row.job || '-' }}</td>
                <td class="px-2 py-2">{{ row.client_name || '-' }}</td>
                <td class="px-2 py-2 text-right" :class="drAmountClass">
                  {{ row.dr_amount ? formatCurrency(row.dr_amount) : '-' }}
                </td>
                <td class="px-2 py-2 text-right text-orange-700">
                  {{ row.discount ? formatCurrency(row.discount) : '-' }}
                </td>
                <td class="px-2 py-2 text-right" :class="crAmountClass">
                  {{ formatAccountLedgerCreditAmount(row.cr_amount, formatCurrency) }}
                </td>
                <td class="px-2 py-2">{{ row.payment_method || '-' }}</td>
                <td
                  class="px-2 py-2 text-right font-semibold"
                  :class="amountValueClass(row.balance)"
                >
                  {{
                    useAmountLabel
                      ? formatAccountLedgerAmount(row.balance, formatCurrency)
                      : formatAccountLedgerBalanceAmount(row.balance, formatCurrency)
                  }}
                </td>
                <td class="px-2 py-2 text-gray-600">{{ row.remarks || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="mt-4 print:hidden">
        <router-link to="/finance/accounts">
          <BaseButton :className="'bg-white text-gray-800 ring-1 ring-gray-300 hover:bg-gray-50'">
            <i class="fa fa-arrow-left mr-1"></i> Back to Accounts
          </BaseButton>
        </router-link>
      </div>
    </template>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ACCOUNT_CATEGORIES } from '@/finance/data/accountCategoryCodes'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useAccountStore } from '@/finance/store/accountStore'
import { useFinanceAccountStore } from '@/finance/store/financeAccountStore'
import { filterLedgerByDate } from '@/finance/data/accountLedgerData'
import { useAccountLedgerStore } from '@/finance/store/accountLedgerStore'
import { formatCurrency, formatDisplayDate } from '@/finance/utils/billUtils'
import {
  downloadAccountLedgerCsv,
  formatAccountLedgerAmount,
  formatAccountLedgerBalanceAmount,
  formatAccountLedgerCreditAmount,
  isAccountLedgerAmountDebit,
} from '@/finance/utils/accountLedgerCsvUtils'

const route = useRoute()
const router = useRouter()
const accountStore = useAccountStore()
const financeAccountStore = useFinanceAccountStore()
const ledgerStore = useAccountLedgerStore()

const fromDate = ref('')
const toDate = ref('')

const accountId = computed(() => Number(route.params.accountId))
const account = ref(null)
const ledgerRows = ref([])
const isLoading = ref(true)

const isCapitalLedger = computed(() => {
  const category = String(account.value?.category || '').toLowerCase()
  return (
    category === ACCOUNT_CATEGORIES.CAPITAL ||
    category === ACCOUNT_CATEGORIES.AGENT_ADVANCED ||
    category === ACCOUNT_CATEGORIES.SALE ||
    category === ACCOUNT_CATEGORIES.BILLS_RECEIVABLE ||
    category === ACCOUNT_CATEGORIES.INCOME_RECEIVABLE
  )
})

const isIncomeHeadLedger = computed(() => {
  const category = String(account.value?.category || '').toLowerCase()
  return (
    category === ACCOUNT_CATEGORIES.CLIENT_INCOME ||
    category === ACCOUNT_CATEGORIES.RECRUITMENT_INCOME ||
    category === ACCOUNT_CATEGORIES.OTHER_INCOME
  )
})

const isAssetOrLiabilitiesLedger = computed(() => {
  const category = String(account.value?.category || '').toLowerCase()
  return (
    category === ACCOUNT_CATEGORIES.ASSET ||
    category === ACCOUNT_CATEGORIES.LIABILITIES
  )
})

const isAssetLedger = computed(() => {
  return String(account.value?.category || '').toLowerCase() === ACCOUNT_CATEGORIES.ASSET
})

const isLiabilitiesLedger = computed(() => {
  return String(account.value?.category || '').toLowerCase() === ACCOUNT_CATEGORIES.LIABILITIES
})

const useAmountLabel = computed(
  () => isIncomeHeadLedger.value || isAssetOrLiabilitiesLedger.value
)

const drAmountClass = computed(() =>
  isAssetLedger.value ? 'text-green-700' : 'text-red-700'
)

const crAmountClass = computed(() =>
  isLiabilitiesLedger.value ? 'text-red-700' : 'text-green-700'
)

function amountValueClass(balance) {
  if (!useAmountLabel.value) return 'text-gray-900'

  const isDebit = isAccountLedgerAmountDebit(balance)

  if (isAssetLedger.value) {
    return isDebit ? 'text-green-700' : 'text-red-700'
  }

  if (isLiabilitiesLedger.value) {
    return isDebit ? 'text-green-700' : 'text-red-700'
  }

  return isDebit ? 'text-red-700' : 'text-green-700'
}

const columns = computed(() => [
  { key: 'date', label: 'Date' },
  { key: 'particular', label: 'Particular' },
  { key: 'voucher_no', label: 'Voucher No' },
  { key: 'demand_letter', label: 'Demand Letter' },
  { key: 'job', label: 'Job' },
  { key: 'client_name', label: 'Reference' },
  {
    key: 'dr_amount',
    label: isCapitalLedger.value
      ? 'DR'
      : isIncomeHeadLedger.value
        ? 'DR (Bill)'
        : isAssetOrLiabilitiesLedger.value
          ? 'DR'
          : 'DR (Payment)',
  },
  { key: 'discount', label: 'Discount' },
  {
    key: 'cr_amount',
    label: isCapitalLedger.value || isAssetOrLiabilitiesLedger.value ? 'CR' : 'CR (Received)',
  },
  { key: 'payment_method', label: 'Payment Method' },
  { key: 'balance', label: useAmountLabel.value ? 'Amount' : 'Balance' },
  { key: 'remarks', label: 'Remarks' },
])

async function loadLedgerPage(id = accountId.value) {
  if (!id) {
    account.value = null
    ledgerRows.value = []
    isLoading.value = false
    return
  }

  isLoading.value = true

  try {
    account.value = await accountStore.ensureAccount(id)

    if (!account.value) {
      account.value = await financeAccountStore.fetchAccountById(id)
    }

    ledgerStore.invalidateAccountLedger(id)
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

const filteredRows = computed(() =>
  filterLedgerByDate(ledgerRows.value, fromDate.value, toDate.value)
)

function amountColumnClass(key) {
  if (['dr_amount', 'discount', 'cr_amount', 'balance'].includes(key)) {
    return 'text-right'
  }
  return ''
}

function clearDateFilter() {
  fromDate.value = ''
  toDate.value = ''
}

function handleExportCsv() {
  if (!account.value) return

  downloadAccountLedgerCsv(filteredRows.value, {
    accountName: account.value.account_name,
    accountLabel: account.value.account_label,
    category: account.value.category,
    fromDate: fromDate.value,
    toDate: toDate.value,
    useAmountLabel: useAmountLabel.value,
  })
}

function handlePrint() {
  const routeData = router.resolve({
    name: 'Account Ledger Print',
    params: { accountId: accountId.value },
    query: {
      ...(fromDate.value ? { from: fromDate.value } : {}),
      ...(toDate.value ? { to: toDate.value } : {}),
    },
  })

  window.open(routeData.href, '_blank')
}
</script>
