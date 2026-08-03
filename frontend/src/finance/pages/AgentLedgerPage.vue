<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <div class="mb-1 flex items-center gap-2 text-sm text-gray-500">
          <router-link to="/finance/accounts?tab=agent-accounts" class="hover:text-green-700">
            Agent Accounts
          </router-link>
          <span>/</span>
          <span class="text-gray-700">Agent Ledger</span>
        </div>
        <PageTitle>Agent Ledger</PageTitle>
        <p v-if="agent" class="mt-1 text-sm text-gray-600">
          {{ agent.agent_code }} - {{ agent.agent_name }}
        </p>
      </div>

      <div v-if="agent" class="flex flex-wrap items-center gap-2">
        <BaseButton class="bg-blue-600 text-white hover:bg-blue-700" @click="handleExportCsv">
          <i class="fa fa-download mr-1"></i> Export CSV
        </BaseButton>
        <BaseButton class="bg-green-600 text-white hover:bg-green-700" @click="handlePrint">
          <i class="fa fa-print mr-1"></i> Print / PDF
        </BaseButton>
      </div>
    </PageHeader>

    <div v-if="isLoading" class="rounded-lg bg-white p-6 text-center text-gray-500 shadow-sm">
      Loading agent ledger...
    </div>

    <div v-else-if="!agent" class="rounded-lg bg-white p-6 text-center text-gray-500 shadow-sm">
      Agent not found.
      <router-link to="/finance/accounts?tab=agent-accounts" class="ml-2 text-green-700 hover:underline">
        Back to Agent Accounts
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

      <div id="agent-ledger-print" class="rounded-lg bg-white shadow-sm">
        <div class="border-b border-gray-200 px-4 py-3 print:border-gray-300">
          <h3 class="text-base font-semibold text-gray-800">Ledger Statement</h3>
          <p class="mt-1 text-sm text-gray-600">
            {{ agent.agent_code }} - {{ agent.agent_name }}
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
                <td class="px-2 py-2 text-right text-red-700">
                  {{ row.dr_amount ? formatCurrency(row.dr_amount) : '-' }}
                </td>
                <td class="px-2 py-2 text-right text-orange-700">
                  {{ row.discount ? formatCurrency(row.discount) : '-' }}
                </td>
                <td class="px-2 py-2 text-right text-green-700">
                  {{ formatLedgerCreditAmount(row.cr_amount, formatCurrency) }}
                </td>
                <td class="px-2 py-2">{{ row.payment_method || '-' }}</td>
                <td
                  class="px-2 py-2 text-right font-semibold"
                  :class="
                    isApplicantLedgerAmountDebit(row.balance) ? 'text-red-700' : 'text-green-700'
                  "
                >
                  {{ formatApplicantLedgerAmount(row.balance, formatCurrency) }}
                </td>
                <td class="px-2 py-2 text-gray-600">{{ row.remarks || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="mt-4 print:hidden">
        <router-link to="/finance/accounts?tab=agent-accounts">
          <BaseButton :className="'bg-white text-gray-800 ring-1 ring-gray-300 hover:bg-gray-50'">
            <i class="fa fa-arrow-left mr-1"></i> Back to Agent Accounts
          </BaseButton>
        </router-link>
      </div>
    </template>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useAgentAccountStore } from '@/finance/store/agentAccountStore'
import { useFinanceAccountStore } from '@/finance/store/financeAccountStore'
import { filterLedgerByDate } from '@/finance/data/agentLedgerData'
import { useAccountLedgerStore } from '@/finance/store/accountLedgerStore'
import { ACCOUNT_CATEGORIES } from '@/finance/data/accountCategoryCodes'
import { formatCurrency, formatDisplayDate } from '@/finance/utils/billUtils'
import {
  downloadAgentLedgerCsv,
  formatLedgerCreditAmount,
} from '@/finance/utils/agentLedgerCsvUtils'
import {
  formatApplicantLedgerAmount,
  isApplicantLedgerAmountDebit,
} from '@/finance/utils/partyLedgerCsvUtils'

const route = useRoute()
const router = useRouter()
const accountStore = useAgentAccountStore()
const financeAccountStore = useFinanceAccountStore()
const ledgerStore = useAccountLedgerStore()

const fromDate = ref('')
const toDate = ref('')
const isLoading = ref(true)
const agent = ref(null)
const ledgerRows = ref([])

const columns = [
  { key: 'date', label: 'Date' },
  { key: 'particular', label: 'Particular' },
  { key: 'voucher_no', label: 'Voucher No' },
  { key: 'demand_letter', label: 'Demand Letter' },
  { key: 'job', label: 'Job' },
  { key: 'client_name', label: 'Client Name' },
  { key: 'dr_amount', label: 'DR (Charge)' },
  { key: 'discount', label: 'Discount' },
  { key: 'cr_amount', label: 'CR (Received)' },
  { key: 'payment_method', label: 'Payment Method' },
  { key: 'balance', label: 'Balance' },
  { key: 'remarks', label: 'Remarks' },
]

const agentId = computed(() => Number(route.params.agentId))

const filteredRows = computed(() =>
  filterLedgerByDate(ledgerRows.value, fromDate.value, toDate.value)
)

async function loadLedgerPage(id = agentId.value) {
  if (!id) {
    agent.value = null
    ledgerRows.value = []
    isLoading.value = false
    return
  }

  isLoading.value = true

  try {
    await accountStore.fetchAccounts(true)
    agent.value = accountStore.getAccount(id)

    if (!agent.value) {
      const fetchedAccount = await financeAccountStore.fetchAccountById(id)
      agent.value =
        fetchedAccount?.category === ACCOUNT_CATEGORIES.AGENT ? fetchedAccount : null
    }

    ledgerRows.value = agent.value ? await ledgerStore.fetchAccountLedger(id) : []
  } catch {
    agent.value = null
    ledgerRows.value = []
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadLedgerPage()
})

watch(agentId, (id) => {
  loadLedgerPage(id)
})

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
  if (!agent.value) return

  downloadAgentLedgerCsv(filteredRows.value, {
    agentName: agent.value.agent_name,
    agentCode: agent.value.agent_code,
    fromDate: fromDate.value,
    toDate: toDate.value,
  })
}

function handlePrint() {
  const routeData = router.resolve({
    name: 'Agent Ledger Print',
    params: { agentId: agentId.value },
    query: {
      ...(fromDate.value ? { from: fromDate.value } : {}),
      ...(toDate.value ? { to: toDate.value } : {}),
    },
  })

  window.open(routeData.href, '_blank')
}
</script>
