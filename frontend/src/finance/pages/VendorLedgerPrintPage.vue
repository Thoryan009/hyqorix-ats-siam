<template>
  <div v-if="!vendor" class="p-8 text-center text-sm text-gray-500 print:hidden">
    Vendor not found.
  </div>

  <div v-else id="vendor-ledger-print" class="vendor-ledger-print bg-white text-black">
    <div class="border-b border-gray-300 px-4 py-3">
      <h3 class="text-base font-semibold">Ledger Statement</h3>
      <p class="mt-1 text-sm">
        {{ vendor.vendor_code }} - {{ vendor.vendor_name }}
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
            <td class="px-2 py-2 text-right font-semibold">
              {{ formatLedgerBalanceAmount(row.balance, formatCurrency) }}
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
import { computed, nextTick, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useRoute } from 'vue-router'
import { usePartyAccountsStore } from '@/finance/store/partyAccountsStore'
import { computeLedgerBalances, filterLedgerByDate } from '@/finance/data/agentLedgerData'
import { useVendorLedgerStore } from '@/finance/store/vendorLedgerStore'
import { formatCurrency, formatDisplayDate } from '@/finance/utils/billUtils'
import {
  formatLedgerBalanceAmount,
  formatLedgerCreditAmount,
} from '@/finance/utils/vendorLedgerCsvUtils'
import { printWithOrientation } from '@/shared/utils/printOrientation'

const route = useRoute()
const partyAccountsStore = usePartyAccountsStore()
const ledgerStore = useVendorLedgerStore()
const { entriesByVendor } = storeToRefs(ledgerStore)

const fromDate = computed(() => (typeof route.query.from === 'string' ? route.query.from : ''))
const toDate = computed(() => (typeof route.query.to === 'string' ? route.query.to : ''))

const columns = [
  { key: 'date', label: 'Date' },
  { key: 'particular', label: 'Particular' },
  { key: 'voucher_no', label: 'Voucher No' },
  { key: 'demand_letter', label: 'Demand Letter' },
  { key: 'job', label: 'Job' },
  { key: 'client_name', label: 'Client Name' },
  { key: 'dr_amount', label: 'DR (Charge)' },
  { key: 'discount', label: 'Discount' },
  { key: 'cr_amount', label: 'CR (Payment)' },
  { key: 'payment_method', label: 'Payment Method' },
  { key: 'balance', label: 'Balance' },
  { key: 'remarks', label: 'Remarks' },
]

const vendorId = computed(() => Number(route.params.vendorId))
const vendor = computed(() => partyAccountsStore.getAccount('vendor', vendorId.value))

const ledgerRows = computed(() => {
  const entries = entriesByVendor.value[String(vendorId.value)] ?? []
  return computeLedgerBalances(entries)
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
  printWithOrientation('landscape', '8mm', 'vendor-ledger-print')
}

let hasAutoPrinted = false

watch(
  vendor,
  async (record) => {
    if (!record || hasAutoPrinted) return

    document.title = `Vendor Ledger - ${record.vendor_code}`

    await nextTick()
    hasAutoPrinted = true
    setTimeout(() => {
      printWithOrientation('landscape', '8mm', 'vendor-ledger-print')
    }, 600)
  },
  { immediate: true }
)
</script>

<style scoped>
.vendor-ledger-print {
  max-width: 100%;
  margin: 0 auto;
  padding: 8mm;
  font-family: Arial, Helvetica, sans-serif;
}
</style>

<style>
@media print {
  #vendor-ledger-print .overflow-x-auto {
    overflow: visible;
  }

  #vendor-ledger-print table {
    width: 100%;
    font-size: 9px;
  }

  #vendor-ledger-print th,
  #vendor-ledger-print td {
    padding: 3px 4px;
    color: #000 !important;
  }
}
</style>
