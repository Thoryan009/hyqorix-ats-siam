<template>
  <div v-if="!account" class="p-8 text-center text-sm text-gray-500 print:hidden">
    Account not found.
  </div>

  <div v-else id="account-ledger-print" class="account-ledger-print bg-white text-black">
    <div class="border-b border-gray-300 px-4 py-3">
      <h3 class="text-base font-semibold">Ledger Statement</h3>
      <p class="mt-1 text-sm">
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
            class="border-b border-gray-100"
          >
            <td class="px-2 py-2 whitespace-nowrap">{{ formatDisplayDate(row.date) }}</td>
            <td class="px-2 py-2 font-medium">{{ row.particular }}</td>
            <td class="px-2 py-2">{{ row.voucher_no || '-' }}</td>
            <td class="px-2 py-2">{{ row.demand_letter || '-' }}</td>
            <td class="px-2 py-2">{{ row.job || '-' }}</td>
            <td class="px-2 py-2">{{ row.client_name || '-' }}</td>
            <td class="px-2 py-2 text-right">
              {{ row.dr_amount ? formatCurrency(row.dr_amount) : '-' }}
            </td>
            <td class="px-2 py-2 text-right">
              {{ row.discount ? formatCurrency(row.discount) : '-' }}
            </td>
            <td class="px-2 py-2 text-right">
              {{ formatAccountLedgerCreditAmount(row.cr_amount, formatCurrency) }}
            </td>
            <td class="px-2 py-2">{{ row.payment_method || '-' }}</td>
            <td
              class="px-2 py-2 text-right font-semibold"
              :class="
                useAmountLabel
                  ? isAccountLedgerAmountDebit(row.balance)
                    ? 'text-red-700'
                    : 'text-green-700'
                  : ''
              "
            >
              {{
                useAmountLabel
                  ? formatAccountLedgerAmount(row.balance, formatCurrency)
                  : formatAccountLedgerBalanceAmount(row.balance, formatCurrency)
              }}
            </td>
            <td class="px-2 py-2">{{ row.remarks || '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex justify-end print:hidden">
      <BaseButton class="bg-green-600 text-white hover:bg-green-700" @click="handlePrint">
        <i class="fa fa-print mr-1"></i> Print / PDF
      </BaseButton>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { ACCOUNT_CATEGORIES } from '@/finance/data/accountCategoryCodes'
import { useAccountStore } from '@/finance/store/accountStore'
import { filterLedgerByDate } from '@/finance/data/accountLedgerData'
import { useAccountLedgerStore } from '@/finance/store/accountLedgerStore'
import { formatCurrency, formatDisplayDate } from '@/finance/utils/billUtils'
import {
  formatAccountLedgerAmount,
  formatAccountLedgerBalanceAmount,
  formatAccountLedgerCreditAmount,
  isAccountLedgerAmountDebit,
} from '@/finance/utils/accountLedgerCsvUtils'
import { printWithOrientation } from '@/shared/utils/printOrientation'

const route = useRoute()
const accountStore = useAccountStore()
const ledgerStore = useAccountLedgerStore()

const fromDate = computed(() => (typeof route.query.from === 'string' ? route.query.from : ''))
const toDate = computed(() => (typeof route.query.to === 'string' ? route.query.to : ''))

const accountId = computed(() => Number(route.params.accountId))
const account = ref(null)
const ledgerRows = ref([])

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

const useAmountLabel = computed(
  () => isIncomeHeadLedger.value || isAssetOrLiabilitiesLedger.value
)

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
  if (!id) return

  account.value = await accountStore.ensureAccount(id)
  ledgerRows.value = account.value ? await ledgerStore.fetchAccountLedger(id) : []
}

onMounted(async () => {
  await loadLedgerPage()
})

watch(accountId, async (id) => {
  await loadLedgerPage(id)
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

function handlePrint() {
  printWithOrientation('landscape', '8mm', 'account-ledger-print')
}

let hasAutoPrinted = false

watch(
  account,
  async (record) => {
    if (!record || hasAutoPrinted) return

    document.title = `Account Ledger - ${record.account_name}`

    await nextTick()
    hasAutoPrinted = true
    setTimeout(() => {
      printWithOrientation('landscape', '8mm', 'account-ledger-print')
    }, 600)
  },
  { immediate: true }
)
</script>

<style scoped>
.account-ledger-print {
  max-width: 100%;
  margin: 0 auto;
  padding: 8mm;
  font-family: Arial, Helvetica, sans-serif;
}
</style>

<style>
@media print {
  #account-ledger-print .overflow-x-auto {
    overflow: visible;
  }

  #account-ledger-print table {
    width: 100%;
    font-size: 9px;
  }

  #account-ledger-print th,
  #account-ledger-print td {
    padding: 3px 4px;
    color: #000 !important;
  }
}
</style>
