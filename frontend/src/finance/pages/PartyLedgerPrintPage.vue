<template>
  <div v-if="isLoading" class="p-8 text-center text-sm text-gray-500 print:hidden">
    Loading ledger...
  </div>

  <div v-else-if="!account" class="p-8 text-center text-sm text-gray-500 print:hidden">
    {{ config?.partyLabel ?? 'Party' }} account not found.
  </div>

  <div v-else :id="printElementId" class="party-ledger-print bg-white text-black">
    <div class="border-b border-gray-300 px-4 py-3">
      <h3 class="text-base font-semibold">Ledger Statement</h3>
      <p class="mt-1 text-sm">
        {{ account[config.codeKey] }} - {{ account[config.nameKey] }}
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
          <tr v-for="row in filteredRows" :key="row.id" class="border-b border-gray-100">
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
              {{ formatLedgerCreditAmount(row.cr_amount, formatCurrency) }}
            </td>
            <td class="px-2 py-2">{{ row.payment_method || '-' }}</td>
            <td
              class="px-2 py-2 text-right font-semibold"
              :class="
                useAmountLabel
                  ? isApplicantLedgerAmountDebit(row.balance)
                    ? 'text-red-700'
                    : 'text-green-700'
                  : ''
              "
            >
              {{
                useAmountLabel
                  ? formatApplicantLedgerAmount(row.balance, formatCurrency)
                  : formatLedgerBalanceAmount(row.balance, formatCurrency)
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
import { usePartyAccountsStore } from '@/finance/store/partyAccountsStore'
import { useFinanceAccountStore } from '@/finance/store/financeAccountStore'
import { filterLedgerByDate } from '@/finance/data/partyLedgerData'
import { useAccountLedgerStore } from '@/finance/store/accountLedgerStore'
import { getPartyConfig } from '@/finance/config/partyAccountConfigs'
import { formatCurrency, formatDisplayDate } from '@/finance/utils/billUtils'
import {
  formatApplicantLedgerAmount,
  formatLedgerBalanceAmount,
  formatLedgerCreditAmount,
  isApplicantLedgerAmountDebit,
} from '@/finance/utils/partyLedgerCsvUtils'
import { printWithOrientation } from '@/shared/utils/printOrientation'

const props = defineProps({
  partyType: {
    type: String,
    required: true,
  },
})

const route = useRoute()
const partyAccountsStore = usePartyAccountsStore()
const financeAccountStore = useFinanceAccountStore()
const ledgerStore = useAccountLedgerStore()

const fromDate = computed(() => (typeof route.query.from === 'string' ? route.query.from : ''))
const toDate = computed(() => (typeof route.query.to === 'string' ? route.query.to : ''))

const config = computed(() => getPartyConfig(props.partyType))
const printElementId = computed(() => `${props.partyType}-ledger-print`)
const useAmountLabel = computed(() => Boolean(config.value?.useAmountLabel))
const referenceColumnLabel = computed(
  () => config.value?.ledgerReferenceLabel || 'Client Name'
)

const columns = computed(() => [
  { key: 'date', label: 'Date' },
  { key: 'particular', label: 'Particular' },
  { key: 'voucher_no', label: 'Voucher No' },
  { key: 'demand_letter', label: 'Demand Letter' },
  { key: 'job', label: 'Job' },
  { key: 'client_name', label: referenceColumnLabel.value },
  { key: 'dr_amount', label: 'DR (Charge)' },
  { key: 'discount', label: 'Discount' },
  { key: 'cr_amount', label: 'CR (Payment)' },
  { key: 'payment_method', label: 'Payment Method' },
  { key: 'balance', label: useAmountLabel.value ? 'Amount' : 'Balance' },
  { key: 'remarks', label: 'Remarks' },
])

const accountId = computed(() => Number(route.params.accountId || route.params.vendorId))
const account = ref(null)
const ledgerRows = ref([])
const isLoading = ref(true)

async function loadLedgerPage(id = accountId.value) {
  if (!id || !props.partyType) {
    account.value = null
    ledgerRows.value = []
    isLoading.value = false
    return
  }

  isLoading.value = true

  try {
    await partyAccountsStore.fetchAccounts(props.partyType, true)
    account.value = partyAccountsStore.getAccount(props.partyType, id)

    if (!account.value) {
      const fetchedAccount = await financeAccountStore.fetchAccountById(id)
      account.value = fetchedAccount?.category === config.value?.accountCategory
        ? fetchedAccount
        : null
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
  printWithOrientation('landscape', '8mm', printElementId.value)
}

let hasAutoPrinted = false

watch(
  account,
  async (record) => {
    if (!record || hasAutoPrinted || !config.value || isLoading.value) return

    document.title = `${config.value.partyLabel} Ledger - ${record[config.value.codeKey]}`

    await nextTick()
    hasAutoPrinted = true
    setTimeout(() => {
      printWithOrientation('landscape', '8mm', printElementId.value)
    }, 600)
  },
  { immediate: true }
)
</script>

<style scoped>
.party-ledger-print {
  max-width: 100%;
  margin: 0 auto;
  padding: 8mm;
  font-family: Arial, Helvetica, sans-serif;
}
</style>

<style>
@media print {
  [id$='-ledger-print'] .overflow-x-auto {
    overflow: visible;
  }

  [id$='-ledger-print'] table {
    width: 100%;
    font-size: 9px;
  }

  [id$='-ledger-print'] th,
  [id$='-ledger-print'] td {
    padding: 3px 4px;
    color: #000 !important;
  }
}
</style>
