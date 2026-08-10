<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>Trial Balance</PageTitle>
        <p class="mt-1 text-sm text-gray-500">
          Closing account balances as of the selected assessment year end
        </p>
      </div>
    </PageHeader>

    <div
      class="mb-4 flex flex-col gap-3 rounded-lg border border-gray-100 bg-white p-4 shadow-sm sm:flex-row sm:flex-wrap sm:items-end"
    >
      <div class="flex flex-col sm:min-w-45">
        <label class="mb-1 text-sm font-medium text-gray-700">Assessment Year</label>
        <select
          v-model.number="selectedYear"
          class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
          @change="onYearChange"
        >
          <option v-for="year in yearOptions" :key="year" :value="year">
            {{ year }}{{ year === currentYear ? ' (Current)' : '' }}
          </option>
        </select>
      </div>
      <p class="text-sm text-gray-500 sm:mb-2">As of {{ formatDisplayDate(yearDateRange.to_date) }}</p>
      <BaseButton class="bg-primary text-white hover:opacity-90" :disabled="loading" @click="loadReport">
        <i class="fa fa-refresh mr-1"></i>
        {{ loading ? 'Loading...' : 'Refresh' }}
      </BaseButton>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
      <div class="border-b border-gray-200 bg-emerald-50 px-4 py-3 text-center">
        <h3 class="text-lg font-bold text-gray-900">Trial Balance</h3>
        <p class="mt-0.5 text-sm text-gray-600">As of {{ formatDisplayDate(yearDateRange.to_date) }}</p>
      </div>

      <div v-if="loading" class="px-4 py-12 text-center text-gray-500">
        Loading trial balance...
      </div>
      <div v-else-if="!rows.length" class="px-4 py-12 text-center text-gray-500">
        No account balances found for this period.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
          <thead>
            <tr class="bg-emerald-100 text-gray-900">
              <th class="border border-gray-300 px-4 py-3 text-left font-semibold">Account</th>
              <th class="border border-gray-300 px-4 py-3 text-right font-semibold">Debit</th>
              <th class="border border-gray-300 px-4 py-3 text-right font-semibold">Credit</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="row in rows"
              :key="row.account_id"
              class="hover:bg-gray-50"
            >
              <td class="border border-gray-200 px-4 py-2.5 text-gray-900">
                {{ row.account_name }}
              </td>
              <td class="border border-gray-200 px-4 py-2.5 text-right tabular-nums text-gray-900">
                {{ row.debit_balance > 0 ? formatAmount(row.debit_balance) : '' }}
              </td>
              <td class="border border-gray-200 px-4 py-2.5 text-right tabular-nums text-gray-900">
                {{ row.credit_balance > 0 ? formatAmount(row.credit_balance) : '' }}
              </td>
            </tr>
            <tr class="bg-emerald-50 font-bold text-gray-900">
              <td class="border border-gray-300 px-4 py-3">Total</td>
              <td class="border border-gray-300 px-4 py-3 text-right tabular-nums">
                {{ formatAmount(debitTotal) }}
              </td>
              <td class="border border-gray-300 px-4 py-3 text-right tabular-nums">
                {{ formatAmount(creditTotal) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div
      v-if="!loading && rows.length"
      class="mt-4 rounded-lg border p-4 text-center text-base font-semibold"
      :class="
        isBalanced
          ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
          : 'border-amber-200 bg-amber-50 text-amber-900'
      "
    >
      <template v-if="isBalanced">Balanced!</template>
      <template v-else>
        Not balanced — difference {{ formatAmount(Math.abs(difference)) }}
        ({{ difference > 0 ? 'Debit heavier' : 'Credit heavier' }})
      </template>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { fetchTrialBalance } from '@/finance/services/trialBalanceService'
import { formatDisplayDate } from '@/finance/utils/billUtils'
import { toast } from '@/shared/config/toastConfig'

const route = useRoute()
const currentYear = new Date().getFullYear()
const selectedYear = ref(currentYear)
const loading = ref(false)
const rows = ref([])
const debitTotal = ref(0)
const creditTotal = ref(0)
const difference = ref(0)
const isBalanced = ref(true)

const yearOptions = computed(() => {
  const years = []
  for (let year = currentYear + 1; year >= currentYear - 5; year -= 1) {
    years.push(year)
  }
  return years
})

const yearDateRange = computed(() => ({
  from_date: `${selectedYear.value}-01-01`,
  to_date: `${selectedYear.value}-12-31`,
}))

function formatAmount(amount) {
  return new Intl.NumberFormat('en-IN', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(Number(amount) || 0)
}

function resolveYearFromQuery() {
  const fromDate = route.query.from_date
  const toDate = route.query.to_date

  if (typeof fromDate === 'string' && fromDate.length >= 4) {
    const year = Number(fromDate.slice(0, 4))
    if (yearOptions.value.includes(year)) {
      selectedYear.value = year
      return
    }
  }

  if (typeof toDate === 'string' && toDate.length >= 4) {
    const year = Number(toDate.slice(0, 4))
    if (yearOptions.value.includes(year)) {
      selectedYear.value = year
    }
  }
}

async function onYearChange() {
  await loadReport()
}

async function loadReport() {
  loading.value = true
  try {
    const payload = await fetchTrialBalance({
      from_date: yearDateRange.value.from_date,
      to_date: yearDateRange.value.to_date,
    })
    const data = payload?.data ?? payload
    rows.value = Array.isArray(data?.rows) ? data.rows : []
    debitTotal.value = Number(data?.debit_total || 0)
    creditTotal.value = Number(data?.credit_total || 0)
    difference.value = Number(data?.difference || 0)
    isBalanced.value = data?.is_balanced !== false
  } catch (error) {
    toast.error(error?.message || 'Failed to load trial balance.')
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  resolveYearFromQuery()
  await loadReport()
})
</script>
