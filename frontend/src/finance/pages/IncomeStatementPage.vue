<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>Income Statement</PageTitle>
        <p class="mt-1 text-sm text-gray-500">
          Two-sided account — Operating expenses vs Gross Profit & income collections
        </p>
      </div>
    </PageHeader>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div
        v-for="card in summaryCards"
        :key="card.title"
        class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"
      >
        <p class="text-sm text-gray-500">{{ card.title }}</p>
        <p class="mt-1 text-2xl font-bold tabular-nums" :class="card.valueClass || 'text-gray-900'">
          {{ card.value }}
        </p>
      </div>
    </div>

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
      <p class="text-sm text-gray-500 sm:mb-2">
        {{ yearDateRange.from_date }} — {{ yearDateRange.to_date }}
      </p>
      <BaseButton class="bg-primary text-white hover:opacity-90" :disabled="loading" @click="loadReport">
        <i class="fa fa-refresh mr-1"></i>
        {{ loading ? 'Loading...' : 'Refresh' }}
      </BaseButton>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
      <div v-if="loading" class="px-4 py-12 text-center text-gray-500">
        Loading income statement...
      </div>
      <div v-else class="overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
          <thead>
            <tr class="bg-primary-gradient text-gray-900">
              <th class="border border-gray-300 px-3 py-3 text-left font-semibold">
                Debit (Expenses)
              </th>
              <th class="border border-gray-300 px-3 py-3 text-right font-semibold">
                Amount (BDT)
              </th>
              <th class="border border-gray-300 px-3 py-3 text-left font-semibold">
                Credit (Income)
              </th>
              <th class="border border-gray-300 px-3 py-3 text-right font-semibold">
                Amount (BDT)
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, index) in pairedRows" :key="`row-${index}`">
              <td
                class="border border-gray-200 px-3 py-2"
                :class="row.debit?.is_balancing ? 'font-semibold text-primary-dark' : 'text-gray-800'"
              >
                {{ row.debit?.label || '' }}
              </td>
              <td
                class="border border-gray-200 px-3 py-2 text-right tabular-nums"
                :class="row.debit?.is_balancing ? 'font-semibold text-primary-dark' : 'text-gray-900'"
              >
                {{ row.debit ? formatCurrency(row.debit.amount) : '' }}
              </td>
              <td
                class="border border-gray-200 px-3 py-2"
                :class="row.credit?.is_balancing ? 'font-semibold text-red-700' : 'text-gray-800'"
              >
                {{ row.credit?.label || '' }}
              </td>
              <td
                class="border border-gray-200 px-3 py-2 text-right tabular-nums"
                :class="row.credit?.is_balancing ? 'font-semibold text-red-700' : 'text-gray-900'"
              >
                {{ row.credit ? formatCurrency(row.credit.amount) : '' }}
              </td>
            </tr>
            <tr class="bg-gray-50 font-bold text-gray-900">
              <td class="border border-gray-300 px-3 py-3">Total</td>
              <td class="border border-gray-300 px-3 py-3 text-right tabular-nums">
                {{ formatCurrency(debitTotal) }}
              </td>
              <td class="border border-gray-300 px-3 py-3">Total</td>
              <td class="border border-gray-300 px-3 py-3 text-right tabular-nums">
                {{ formatCurrency(creditTotal) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div
      v-if="!loading"
      class="mt-5 rounded-lg border p-4 text-center text-base font-semibold"
      :class="
        summary.is_profit
          ? 'border-primary/30 bg-primary-light text-primary-dark'
          : 'border-red-200 bg-red-50 text-red-800'
      "
    >
      {{ summary.is_profit ? 'Net Profit' : 'Net Loss' }}:
      {{ formatCurrency(Math.abs(summary.net_profit)) }}
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { fetchIncomeStatement } from '@/finance/services/incomeStatementService'
import { formatCurrency } from '@/finance/utils/billUtils'
import { toast } from '@/shared/config/toastConfig'

const route = useRoute()
const currentYear = new Date().getFullYear()
const selectedYear = ref(currentYear)
const loading = ref(false)
const debitLines = ref([])
const creditLines = ref([])
const debitTotal = ref(0)
const creditTotal = ref(0)
const summary = ref({
  gross_profit: 0,
  total_income_collections: 0,
  total_income: 0,
  total_operating_expense: 0,
  net_profit: 0,
  is_profit: true,
})

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

const pairedRows = computed(() => {
  const maxLen = Math.max(debitLines.value.length, creditLines.value.length)
  const rows = []
  for (let i = 0; i < maxLen; i += 1) {
    rows.push({
      debit: debitLines.value[i] || null,
      credit: creditLines.value[i] || null,
    })
  }
  return rows
})

const summaryCards = computed(() => [
  {
    title: 'Gross Profit',
    value: formatCurrency(summary.value.gross_profit),
    valueClass: summary.value.gross_profit >= 0 ? 'text-primary' : 'text-red-700',
  },
  {
    title: 'Income Collections',
    value: formatCurrency(summary.value.total_income_collections),
    valueClass: 'text-primary',
  },
  {
    title: 'Operating Expenses',
    value: formatCurrency(summary.value.total_operating_expense),
    valueClass: 'text-gray-900',
  },
  {
    title: summary.value.is_profit ? 'Net Profit' : 'Net Loss',
    value: formatCurrency(Math.abs(summary.value.net_profit)),
    valueClass: summary.value.is_profit ? 'text-primary' : 'text-red-700',
  },
])

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
    const payload = await fetchIncomeStatement({
      from_date: yearDateRange.value.from_date,
      to_date: yearDateRange.value.to_date,
    })
    const data = payload?.data ?? payload
    debitLines.value = data?.debit_lines ?? []
    creditLines.value = data?.credit_lines ?? []
    debitTotal.value = Number(data?.debit_total || 0)
    creditTotal.value = Number(data?.credit_total || 0)
    summary.value = {
      gross_profit: Number(data?.summary?.gross_profit || 0),
      total_income_collections: Number(data?.summary?.total_income_collections || 0),
      total_income: Number(data?.summary?.total_income || 0),
      total_operating_expense: Number(data?.summary?.total_operating_expense || 0),
      net_profit: Number(data?.summary?.net_profit || 0),
      is_profit: data?.summary?.is_profit !== false,
    }
  } catch (error) {
    toast.error(error?.message || 'Failed to load income statement.')
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  resolveYearFromQuery()
  await loadReport()
})
</script>
