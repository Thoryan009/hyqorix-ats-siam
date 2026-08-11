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
        <p class="mt-1 text-2xl font-bold tabular-nums" :class="card.valueClass || 'text-gray-900'">
          {{ card.value }}
        </p>
        <p class="mt-1 text-xs text-gray-400">{{ card.subtitle }}</p>
      </div>
    </div>

    <div class="mb-4 rounded-lg border border-gray-100 bg-white p-4 shadow-sm">
      <TableFilters
        :show-search="false"
        :filters="filters"
        :has-active-filters="hasActiveFilters"
        @reset="onResetFilters"
      >
        <div class="flex flex-col">
          <label class="mb-1 text-sm text-gray-700">Job</label>
          <BaseSearchSelect
            v-model="filters.job_id"
            :options="jobOptions"
            placeholder="Search job"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterJobList"
          />
        </div>

        <div v-if="authStore.userType !== 'client'" class="flex flex-col">
          <label class="mb-1 text-sm text-gray-700">Client</label>
          <BaseSearchSelect
            v-model="filters.client_id"
            :options="clientOptions"
            placeholder="Search client"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterByName"
          />
        </div>

        <div class="flex flex-col">
          <label class="mb-1 text-sm text-gray-700">DL</label>
          <BaseSearchSelect
            v-model="filters.work_order_id"
            :options="workOrderOptions"
            placeholder="Search demand letter"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterByName"
          />
        </div>

        <div
          v-if="authStore.userType !== 'agent' && authStore.userType !== 'client' && authStore.userType !== 'principal'"
          class="flex flex-col"
        >
          <label class="mb-1 text-sm text-gray-700">Agent</label>
          <BaseSearchSelect
            v-model="filters.agent_id"
            :options="agentOptions"
            placeholder="Search agent"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterByName"
          />
        </div>

        <div class="flex flex-col">
          <label class="mb-1 text-sm text-gray-700">Country</label>
          <BaseSearchSelect
            v-model="filters.country_id"
            :options="countryOptions"
            placeholder="Search country"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterByName"
          />
        </div>

        <div
          v-if="authStore.userType !== 'principal' && authStore.userType !== 'agent' && authStore.userType !== 'client'"
          class="flex flex-col"
        >
          <label class="mb-1 text-sm text-gray-700">Principal</label>
          <BaseSearchSelect
            v-model="filters.principal_id"
            :options="principalOptions"
            placeholder="Search principal"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterByName"
          />
        </div>
      </TableFilters>

      <div class="mt-4 flex flex-wrap gap-2">
        <BaseButton
          class="bg-primary text-white hover:opacity-90"
          :disabled="isBusy"
          @click="loadSummary"
        >
          <i class="fa fa-refresh mr-1"></i>
          {{ loading ? 'Loading...' : 'Refresh Summary' }}
        </BaseButton>
        <BaseButton
          class="bg-red-600 text-white hover:bg-red-700"
          :disabled="isBusy"
          @click="generatePdf"
        >
          <i class="fa fa-file-pdf-o mr-1"></i>
          {{ exporting === 'pdf' ? 'Generating...' : 'Generate PDF' }}
        </BaseButton>
        <BaseButton
          class="bg-primary text-white hover:opacity-90"
          :disabled="isBusy"
          @click="generateCsv"
        >
          <i class="fa fa-file-excel-o mr-1"></i>
          {{ exporting === 'csv' ? 'Generating...' : 'Generate CSV' }}
        </BaseButton>
      </div>
    </div>

    <Teleport to="body">
      <Transition name="export-fade">
        <div
          v-if="exporting"
          class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-900/45 px-4 backdrop-blur-sm"
        >
          <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <div class="mb-5 flex items-center gap-4">
              <div
                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-white shadow-md"
                :class="exporting === 'pdf' ? 'bg-red-600' : 'bg-primary'"
              >
                <i
                  class="fa text-2xl"
                  :class="exporting === 'pdf' ? 'fa-file-pdf-o' : 'fa-file-excel-o'"
                ></i>
              </div>
              <div>
                <p class="text-lg font-semibold text-gray-900">
                  {{ exporting === 'pdf' ? 'Generating PDF' : 'Generating CSV' }}
                </p>
                <p class="mt-0.5 text-sm text-gray-500">{{ exportStatusText }}</p>
              </div>
            </div>

            <div class="mb-2 flex items-center justify-between text-sm font-medium text-gray-700">
              <span>Progress</span>
              <span class="tabular-nums text-primary">{{ exportProgress }}%</span>
            </div>
            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
              <div
                class="h-full rounded-full bg-primary transition-all duration-300 ease-out"
                :style="{ width: `${exportProgress}%` }"
              ></div>
            </div>
            <p class="mt-3 text-center text-xs text-gray-400">Please wait. Do not close this page.</p>
          </div>
        </div>
      </Transition>
    </Teleport>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useAuthStore } from '@/modules/auth/store/authStore'
import {
  useReportAgentsQuery,
  useReportClientsQuery,
  useReportCountryQuery,
  useReportJobsQuery,
  useReportPrincipalsQuery,
  useReportWorkOrdersQuery,
} from '@/modules/reports/queries/useReportsQuery'
import {
  exportGrossProfitCsv,
  exportGrossProfitPdf,
  fetchGrossProfitReport,
} from '@/finance/services/grossProfitReportService'
import { formatCurrency } from '@/finance/utils/billUtils'
import { removeEmptyKeys } from '@/shared/helpers/objectHelper'
import { toast } from '@/shared/config/toastConfig'

const authStore = useAuthStore()
const loading = ref(false)
const exporting = ref('')
const exportProgress = ref(0)
const isBusy = computed(() => loading.value || Boolean(exporting.value))
const exportStatusText = computed(() => {
  if (exportProgress.value >= 100) return 'Download ready'
  if (exportProgress.value >= 70) return 'Preparing your file…'
  if (exportProgress.value >= 35) return 'Building report from server…'
  return 'Collecting report data…'
})

let progressTimer = null

function startExportProgress() {
  exportProgress.value = 6
  clearInterval(progressTimer)
  progressTimer = setInterval(() => {
    if (exportProgress.value >= 90) return
    const remaining = 90 - exportProgress.value
    exportProgress.value += Math.max(1, Math.round(remaining * 0.07))
  }, 280)
}

function stopExportProgress() {
  clearInterval(progressTimer)
  progressTimer = null
}

function finishExportProgress() {
  stopExportProgress()
  exportProgress.value = 100
  return new Promise((resolve) => {
    window.setTimeout(resolve, 450)
  })
}
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

const { filters, resetFilters } = useTableFilters({
  from_date: null,
  to_date: null,
  job_id: null,
  client_id: null,
  work_order_id: null,
  agent_id: null,
  country_id: null,
  principal_id: null,
})

const hasActiveFilters = computed(() =>
  Object.values(filters.value).some((value) => value !== null && value !== '')
)

const { data: countryData } = useReportCountryQuery()
const { data: agentData } = useReportAgentsQuery()
const { data: workOrdersData } = useReportWorkOrdersQuery()
const { data: jobsData } = useReportJobsQuery()
const { data: clientsData } = useReportClientsQuery()
const { data: principalData } = useReportPrincipalsQuery()

const toSelectOptions = (queryResult) => {
  const payload = queryResult?.data
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  return []
}

const countryOptions = computed(() => toSelectOptions(countryData.value))
const workOrderOptions = computed(() => toSelectOptions(workOrdersData.value))
const jobOptions = computed(() => toSelectOptions(jobsData.value))
const clientOptions = computed(() => toSelectOptions(clientsData.value))
const agentOptions = computed(() => toSelectOptions(agentData.value))
const principalOptions = computed(() => toSelectOptions(principalData.value))

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
    title: 'Adjusted Gross Profit / Loss',
    value: formatCurrency(summary.value.adjusted_gross_profit_loss),
    subtitle: 'After rejected / declined expense',
    valueClass:
      summary.value.adjusted_gross_profit_loss >= 0 ? 'text-primary' : 'text-red-700',
  },
])

function activeFilters() {
  return removeEmptyKeys({ ...filters.value })
}

async function loadSummary() {
  loading.value = true
  try {
    const payload = await fetchGrossProfitReport(activeFilters())
    const data = payload?.data ?? payload
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
    toast.error(error?.message || 'Failed to load gross profit summary.')
  } finally {
    loading.value = false
  }
}

function exportErrorMessage(error, fallback) {
  if (typeof error === 'string' && error.trim()) return error
  return error?.message || error?.error || fallback
}

async function generatePdf() {
  exporting.value = 'pdf'
  startExportProgress()
  try {
    await exportGrossProfitPdf(activeFilters())
    await finishExportProgress()
    toast.success('PDF generated.')
  } catch (error) {
    stopExportProgress()
    toast.error(exportErrorMessage(error, 'Failed to generate PDF.'))
  } finally {
    exporting.value = ''
    exportProgress.value = 0
  }
}

async function generateCsv() {
  exporting.value = 'csv'
  startExportProgress()
  try {
    await exportGrossProfitCsv(activeFilters())
    await finishExportProgress()
    toast.success('CSV generated.')
  } catch (error) {
    stopExportProgress()
    toast.error(exportErrorMessage(error, 'Failed to generate CSV.'))
  } finally {
    exporting.value = ''
    exportProgress.value = 0
  }
}

function onResetFilters() {
  resetFilters()
  loadSummary()
}

function filterByName(option, query) {
  return (option.name ?? '').toLowerCase().includes(query)
}

function filterJobList(option, query) {
  const jobName = (option.job_name ?? '').toLowerCase()
  const jobCode = (option.job_code ?? '').toLowerCase()
  const displayName = (option.name ?? '').toLowerCase()

  return jobName.includes(query) || jobCode.includes(query) || displayName.includes(query)
}

onMounted(loadSummary)

onUnmounted(() => {
  stopExportProgress()
})
</script>

<style scoped>
.export-fade-enter-active,
.export-fade-leave-active {
  transition: opacity 0.2s ease;
}

.export-fade-enter-from,
.export-fade-leave-to {
  opacity: 0;
}
</style>
