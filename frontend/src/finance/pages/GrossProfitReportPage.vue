<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>Gross Profit Report</PageTitle>
        <p class="mt-1 text-sm text-gray-500">Candidate-wise sale price vs direct expenses</p>
      </div>
    </PageHeader>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <div
        v-for="card in summaryCards"
        :key="card.title"
        class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"
      >
        <p class="text-sm text-gray-500">{{ card.title }}</p>
        <p class="mt-1 text-2xl font-bold text-gray-900">{{ card.value }}</p>
        <p class="mt-1 text-xs text-gray-400">{{ card.subtitle }}</p>
      </div>
    </div>

    <div
      class="mb-4 flex flex-col gap-3 rounded-lg border border-gray-100 bg-white p-4 shadow-sm sm:flex-row sm:flex-wrap sm:items-end"
    >
      <div class="flex flex-1 flex-col sm:min-w-[200px]">
        <label class="mb-1 text-sm text-gray-700">Search</label>
        <BaseInput v-model="filters.search" placeholder="Candidate, passport, or job..." />
      </div>
      <div class="flex flex-col sm:min-w-[160px]">
        <label class="mb-1 text-sm text-gray-700">From Date</label>
        <BaseInput v-model="filters.from_date" type="date" />
      </div>
      <div class="flex flex-col sm:min-w-[160px]">
        <label class="mb-1 text-sm text-gray-700">To Date</label>
        <BaseInput v-model="filters.to_date" type="date" />
      </div>
      <BaseButton
        class="bg-primary text-white hover:opacity-90"
        :disabled="loading"
        @click="loadReport"
      >
        <i class="fa fa-refresh mr-1"></i>
        {{ loading ? 'Loading...' : 'Generate' }}
      </BaseButton>
      <BaseButton
        v-if="hasActiveFilters"
        :className="'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50'"
        @click="resetFilters"
      >Reset</BaseButton>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="sticky left-0 z-10 bg-gray-50 px-3 py-3 text-left font-semibold text-gray-700"
              >Candidate</th>
              <th class="px-3 py-3 text-left font-semibold text-gray-700">Passport</th>
              <th class="px-3 py-3 text-left font-semibold text-gray-700">Job</th>
              <th
                v-for="head in expenseHeads"
                :key="head.id"
                class="whitespace-nowrap px-3 py-3 text-right font-semibold text-gray-700"
              >{{ head.name }}</th>
              <th
                class="whitespace-nowrap px-3 py-3 text-right font-semibold text-gray-700"
                title="Average Client Recruitment Expense"
              >Avg C.R.E</th>
              <th
                class="whitespace-nowrap px-3 py-3 text-right font-semibold text-gray-700"
              >Total Expense</th>
              <th
                class="whitespace-nowrap px-3 py-3 text-right font-semibold text-gray-700"
              >Sale Price</th>
              <th
                class="whitespace-nowrap px-3 py-3 text-right font-semibold text-gray-700"
              >Profit / Loss</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <tr v-if="loading">
              <td
                :colspan="tableColSpan"
                class="px-3 py-8 text-center text-gray-500"
              >Loading report...</td>
            </tr>
            <tr v-else-if="!rows.length">
              <td
                :colspan="tableColSpan"
                class="px-3 py-8 text-center text-gray-500"
              >No candidate data found. Approve direct expense bills and receive payments to populate this report.</td>
            </tr>
            <tr v-for="row in rows" :key="row.application_id" class="hover:bg-gray-50">
              <td class="sticky left-0 z-10 bg-white px-3 py-3 font-medium text-gray-900">
                <div class="flex flex-wrap items-center gap-2">
                  <span>{{ row.candidate_name }}</span>
                  <span
                    class="rounded-full px-2 py-0.5 text-xs font-semibold whitespace-nowrap"
                    :class="getStatusColor(row.current_process)"
                  >{{ formatProcessLabel(row.current_process) }}</span>
                </div>
              </td>
              <td class="px-3 py-3 text-gray-600">{{ row.passport_no || '—' }}</td>
              <td class="px-3 py-3 text-gray-600">
                <span
                  v-if="row.job_code || row.job_name"
                >{{ row.job_code }}{{ row.job_code && row.job_name ? ' — ' : '' }}{{ row.job_name }}</span>
                <span v-else>—</span>
              </td>
              <td
                v-for="head in expenseHeads"
                :key="`${row.application_id}-${head.id}`"
                class="px-3 py-3 text-right text-gray-800"
              >{{ formatCurrency(row.expenses?.[String(head.id)] || 0) }}</td>
              <td
                class="px-3 py-3 text-right font-semibold text-violet-700"
              >{{ formatCurrency(row.avg_cre) }}</td>
              <td
                class="px-3 py-3 text-right font-semibold text-gray-900"
              >{{ formatCurrency(row.total_expense) }}</td>
              <td
                class="px-3 py-3 text-right font-semibold text-blue-700"
              >{{ formatCurrency(row.sale_price) }}</td>
              <td
                class="px-3 py-3 text-right font-bold"
                :class="row.profit_loss >= 0 ? 'text-emerald-700' : 'text-red-700'"
              >{{ formatCurrency(row.profit_loss) }}</td>
            </tr>
          </tbody>
          <tfoot v-if="!loading && rows.length" class="border-t-2 border-gray-300 bg-gray-50">
            <tr class="font-semibold text-gray-900">
              <td class="sticky left-0 z-10 bg-gray-50 px-3 py-3">Total ({{ rows.length }})</td>
              <td class="px-3 py-3">—</td>
              <td class="px-3 py-3">—</td>
              <td
                v-for="head in expenseHeads"
                :key="`footer-${head.id}`"
                class="px-3 py-3 text-right"
              >{{ formatCurrency(footerTotals.expenses[String(head.id)] || 0) }}</td>
              <td
                class="px-3 py-3 text-right text-violet-700"
              >{{ formatCurrency(footerTotals.avg_cre) }}</td>
              <td class="px-3 py-3 text-right">{{ formatCurrency(footerTotals.total_expense) }}</td>
              <td
                class="px-3 py-3 text-right text-blue-700"
              >{{ formatCurrency(footerTotals.sale_price) }}</td>
              <td
                class="px-3 py-3 text-right font-bold"
                :class="footerTotals.profit_loss >= 0 ? 'text-emerald-700' : 'text-red-700'"
              >{{ formatCurrency(footerTotals.profit_loss) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div
        v-if="!loading && (rows.length || summary.rejected_declined_count)"
        class="space-y-2 border-t border-gray-200 bg-white px-4 py-4"
      >
        <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
          <span class="font-medium text-gray-700">Total Profit / Loss (Active Candidates)</span>
          <span
            class="font-semibold"
            :class="footerTotals.profit_loss >= 0 ? 'text-emerald-700' : 'text-red-700'"
          >{{ formatCurrency(footerTotals.profit_loss) }}</span>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
          <span class="font-medium text-gray-700">
            Less: Rejected / Declined Candidate Expense
            <span
              class="ml-1 text-xs font-normal text-gray-500"
            >
              ({{ summary.rejected_declined_count }} candidate{{
              summary.rejected_declined_count === 1 ? '' : 's'
              }})
            </span>
          </span>
          <span
            class="font-semibold text-red-700"
          >− {{ formatCurrency(summary.rejected_declined_expense) }}</span>
        </div>
        <div
          class="flex flex-wrap items-center justify-between gap-2 border-t border-dashed border-gray-200 pt-2 text-sm"
        >
          <span class="font-bold text-gray-900">Adjusted Gross Profit / Loss</span>
          <span
            class="text-base font-bold"
            :class="adjustedGrossProfitLoss >= 0 ? 'text-emerald-700' : 'text-red-700'"
          >{{ formatCurrency(adjustedGrossProfitLoss) }}</span>
        </div>
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { fetchGrossProfitReport } from '@/finance/services/grossProfitReportService'
import { formatCurrency } from '@/finance/utils/billUtils'
import { toast } from '@/shared/config/toastConfig'
import { getStatusColor } from '@/modules/job/utils/statusColors'

const loading = ref(false)
const expenseHeads = ref([])
const rows = ref([])
const summary = ref({
  candidate_count: 0,
  total_sale: 0,
  total_collected: 0,
  total_expense: 0,
  total_avg_cre: 0,
  total_profit_loss: 0,
  rejected_declined_count: 0,
  rejected_declined_expense: 0,
  adjusted_gross_profit_loss: 0,
})

const filters = reactive({
  search: '',
  from_date: '',
  to_date: '',
})

const hasActiveFilters = computed(() =>
  Boolean(filters.search || filters.from_date || filters.to_date)
)

const tableColSpan = computed(() => 7 + expenseHeads.value.length)

const footerTotals = computed(() => {
  const expenses = {}
  for (const head of expenseHeads.value) {
    expenses[String(head.id)] = 0
  }

  let avgCre = 0
  let totalExpense = 0
  let salePrice = 0
  let profitLoss = 0

  for (const row of rows.value) {
    for (const head of expenseHeads.value) {
      const key = String(head.id)
      expenses[key] += Number(row.expenses?.[key] || 0)
    }
    avgCre += Number(row.avg_cre || 0)
    totalExpense += Number(row.total_expense || 0)
    salePrice += Number(row.sale_price || 0)
    profitLoss += Number(row.profit_loss || 0)
  }

  return {
    expenses,
    avg_cre: avgCre,
    total_expense: totalExpense,
    sale_price: salePrice,
    profit_loss: profitLoss,
  }
})

const adjustedGrossProfitLoss = computed(
  () =>
    Number(footerTotals.value.profit_loss || 0) -
    Number(summary.value.rejected_declined_expense || 0)
)

const summaryCards = computed(() => [
  {
    title: 'Candidates',
    value: summary.value.candidate_count,
    subtitle: 'Included in report',
  },
  {
    title: 'Total Sale Price',
    value: formatCurrency(summary.value.total_sale),
    subtitle: 'From receive payment',
  },
  {
    title: 'Total Direct Expense',
    value: formatCurrency(summary.value.total_expense),
    subtitle: 'Approved bill amounts',
  },
  {
    title: 'Total Profit / Loss',
    value: formatCurrency(summary.value.total_profit_loss),
    subtitle: 'Sale price − expenses',
  },
])

async function loadReport() {
  loading.value = true
  try {
    const payload = await fetchGrossProfitReport({
      search: filters.search || undefined,
      from_date: filters.from_date || undefined,
      to_date: filters.to_date || undefined,
    })
    const data = payload?.data ?? payload
    expenseHeads.value = data?.expense_heads ?? []
    rows.value = data?.rows ?? []
    summary.value = {
      candidate_count: data?.summary?.candidate_count ?? 0,
      total_sale: data?.summary?.total_sale ?? 0,
      total_collected: data?.summary?.total_collected ?? 0,
      total_expense: data?.summary?.total_expense ?? 0,
      total_avg_cre: data?.summary?.total_avg_cre ?? 0,
      total_profit_loss: data?.summary?.total_profit_loss ?? 0,
      rejected_declined_count: data?.summary?.rejected_declined_count ?? 0,
      rejected_declined_expense: data?.summary?.rejected_declined_expense ?? 0,
      adjusted_gross_profit_loss:
        data?.summary?.adjusted_gross_profit_loss ?? data?.summary?.net_profit_loss ?? 0,
    }
  } catch (error) {
    toast.error(error?.message || 'Failed to load gross profit report.')
  } finally {
    loading.value = false
  }
}

function resetFilters() {
  filters.search = ''
  filters.from_date = ''
  filters.to_date = ''
  loadReport()
}

function formatProcessLabel(process) {
  if (!process) return 'N/A'
  return String(process)
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (char) => char.toUpperCase())
}

onMounted(loadReport)
</script>
