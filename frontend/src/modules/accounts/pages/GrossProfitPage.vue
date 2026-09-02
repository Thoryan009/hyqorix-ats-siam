<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ t('accounts.gross_profit') }}</PageTitle>
        <p class="mt-1 text-sm text-gray-500">{{ t('accounts.gross_profit_note') }}</p>
      </div>
    </PageHeader>

    <div
      class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
      v-if="!loading && !loadError"
    >
      <div
        v-for="card in summaryCards"
        :key="card.key"
        class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
      >
        <p class="text-sm text-slate-500">{{ card.title }}</p>
        <p class="mt-1 text-2xl font-bold tabular-nums" :class="card.valueClass">
          {{ formatAmount(card.value) }}
        </p>
      </div>
    </div>

    <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        <div class="flex flex-col">
          <label class="mb-1 text-sm font-medium text-gray-700">{{ t('accounts.from_date') }}</label>
          <input
            v-model="fromDate"
            type="date"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
          />
        </div>
        <div class="flex flex-col">
          <label class="mb-1 text-sm font-medium text-gray-700">{{ t('accounts.to_date') }}</label>
          <input
            v-model="toDate"
            type="date"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
          />
        </div>
        <div class="flex flex-col">
          <label class="mb-1 text-sm font-medium text-gray-700">{{ t('accounts.filter_job') }}</label>
          <BaseSearchSelect
            v-model="filters.job_list_id"
            :options="jobOptions"
            :placeholder="t('accounts.search')"
            :filter-fn="filterByName"
            teleport-dropdown
          />
        </div>
        <div class="flex flex-col">
          <label class="mb-1 text-sm font-medium text-gray-700">{{ t('accounts.filter_demand_letter') }}</label>
          <BaseSearchSelect
            v-model="filters.work_order_id"
            :options="workOrderOptions"
            :placeholder="t('accounts.search')"
            :filter-fn="filterByName"
            teleport-dropdown
          />
        </div>
        <div class="flex flex-col">
          <label class="mb-1 text-sm font-medium text-gray-700">{{ t('accounts.filter_client') }}</label>
          <BaseSearchSelect
            v-model="filters.client_id"
            :options="clientOptions"
            :placeholder="t('accounts.search')"
            :filter-fn="filterByName"
            teleport-dropdown
          />
        </div>
        <div class="flex flex-col">
          <label class="mb-1 text-sm font-medium text-gray-700">{{ t('accounts.filter_agent') }}</label>
          <BaseSearchSelect
            v-model="filters.agent_id"
            :options="agentOptions"
            :placeholder="t('accounts.search')"
            :filter-fn="filterByName"
            teleport-dropdown
          />
        </div>
        <div class="flex flex-col">
          <label class="mb-1 text-sm font-medium text-gray-700">{{ t('accounts.filter_principal') }}</label>
          <BaseSearchSelect
            v-model="filters.principal_id"
            :options="principalOptions"
            :placeholder="t('accounts.search')"
            :filter-fn="filterByName"
            teleport-dropdown
          />
        </div>
      </div>

      <div class="mt-4 flex flex-wrap gap-2">
        <BaseButton class="bg-indigo-600 text-white hover:bg-indigo-700" :disabled="isBusy" @click="loadReport">
          <i class="fa fa-filter mr-1"></i>
          {{ loading ? t('accounts.loading_gross_profit') : t('accounts.filter') }}
        </BaseButton>
        <BaseButton
          v-if="hasActiveFilters"
          class="bg-slate-600 text-white hover:bg-slate-700"
          :disabled="isBusy"
          @click="resetFilters"
        >
          {{ t('accounts.reset_filters') }}
        </BaseButton>
        <BaseButton
          class="bg-slate-900 text-white hover:bg-slate-800"
          :disabled="isBusy"
          @click="viewBreakdownReport"
        >
          <i class="fa fa-eye mr-1"></i>
          {{ previewing ? t('accounts.opening_breakdown') : t('accounts.view_breakdown_report') }}
        </BaseButton>
        <BaseButton
          class="bg-red-600 text-white hover:bg-red-700"
          :disabled="isBusy"
          @click="downloadBreakdownReport"
        >
          <i class="fa fa-file-pdf-o mr-1"></i>
          {{ exporting ? t('accounts.generating_pdf') : t('accounts.download_breakdown_report') }}
        </BaseButton>
      </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-center">
        <h3 class="text-lg font-bold text-slate-900">{{ t('accounts.gross_profit') }}</h3>
        <p class="mt-0.5 text-sm text-slate-600">{{ periodLabel }}</p>
      </div>

      <div v-if="loading" class="px-4 py-12 text-center text-slate-500">
        {{ t('accounts.loading_gross_profit') }}
      </div>
      <div v-else-if="loadError" class="px-4 py-12 text-center text-sm text-rose-600">
        {{ loadError }}
      </div>
      <div v-else-if="!tableRows.length" class="px-4 py-12 text-center text-slate-500">
        {{ t('accounts.gross_profit_empty') }}
      </div>
      <div v-else class="overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
          <thead>
            <tr class="bg-[#1e4b8c] text-left text-white">
              <th class="border border-[#163a6d] px-4 py-3 font-semibold">{{ t('accounts.code') }}</th>
              <th class="border border-[#163a6d] px-4 py-3 font-semibold">{{ t('accounts.account_name') }}</th>
              <th class="border border-[#163a6d] px-4 py-3 text-right font-semibold">{{ t('accounts.amount') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="entry in tableRows"
              :key="entry.key"
              :class="entryRowClass(entry)"
            >
              <template v-if="entry.kind === 'header'">
                <td colspan="2" class="border border-slate-200 px-4 py-2 text-xs font-bold uppercase tracking-wide text-slate-600">
                  {{ entry.label }}
                </td>
                <td class="border border-slate-200 px-4 py-2 text-right text-xs font-bold tabular-nums text-slate-700">
                  {{ entry.sectionTotal != null ? formatAmount(entry.sectionTotal) : '' }}
                </td>
              </template>
              <template v-else>
                <td class="border border-slate-200 px-4 py-2.5 font-medium text-slate-800">
                  {{ entry.account_code }}
                </td>
                <td class="border border-slate-200 px-4 py-2.5 text-slate-900">
                  {{ entry.account_name }}
                </td>
                <td class="border border-slate-200 px-4 py-2.5 text-right tabular-nums text-slate-900">
                  {{ formatAmount(entry.amount) }}
                </td>
              </template>
            </tr>
            <tr class="bg-emerald-50 font-bold text-emerald-900">
              <td colspan="2" class="border border-emerald-200 px-4 py-3">
                {{ t('accounts.gross_profit') }}
              </td>
              <td class="border border-emerald-200 px-4 py-3 text-right tabular-nums">
                {{ formatAmount(summary.gross_profit) }}
              </td>
            </tr>
          </tbody>
        </table>
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
              <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-600 text-white shadow-md">
                <i class="fa fa-file-pdf-o text-2xl"></i>
              </div>
              <div>
                <p class="text-lg font-semibold text-gray-900">{{ t('accounts.generating_pdf') }}</p>
                <p class="mt-0.5 text-sm text-gray-500">{{ exportStatusText }}</p>
              </div>
            </div>

            <div class="mb-2 flex items-center justify-between text-sm font-medium text-gray-700">
              <span>{{ t('accounts.progress') }}</span>
              <span class="tabular-nums text-primary">{{ exportProgress }}%</span>
            </div>
            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
              <div
                class="h-full rounded-full bg-primary transition-all duration-300 ease-out"
                :style="{ width: `${exportProgress}%` }"
              ></div>
            </div>
            <p class="mt-3 text-center text-xs text-gray-400">{{ t('accounts.please_wait_preview') }}</p>
          </div>
        </div>
      </Transition>
    </Teleport>

    <GrossProfitBreakdownPdfPreviewModal
      :is-visible="previewOpen"
      :loading="previewing"
      :error="previewError"
      :pdf-url="previewUrl"
      :filename="previewFilename"
      :progress="exportProgress"
      :status-text="previewStatusText"
      @close="closePreview"
      @download="downloadPreviewPdf"
      @print="printPreviewPdf"
      @retry="viewBreakdownReport"
      @ready="onPreviewReady"
    />
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import BaseSearchSelect from '@/shared/components/base/BaseSearchSelect.vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { removeEmptyKeys } from '@/shared/helpers/objectHelper'
import {
  useReportAgentsQuery,
  useReportClientsQuery,
  useReportJobsQuery,
  useReportPrincipalsQuery,
  useReportWorkOrdersQuery,
} from '@/modules/reports/queries/useReportsQuery'
import {
  exportGrossProfitBreakdownPdf,
  fetchGrossProfit,
  fetchGrossProfitBreakdownPdf,
} from '../services/grossProfitService'
import GrossProfitBreakdownPdfPreviewModal from './components/GrossProfitBreakdownPdfPreviewModal.vue'
import { toast } from '@/shared/config/toastConfig'

const { t } = useTranslate()

const currentYear = new Date().getFullYear()
const fromDate = ref(`${currentYear}-01-01`)
const toDate = ref(`${currentYear}-12-31`)
const loading = ref(true)
const loadError = ref('')
const exporting = ref(false)
const previewing = ref(false)
const previewOpen = ref(false)
const previewUrl = ref('')
const previewFilename = ref('')
const previewError = ref('')
const exportProgress = ref(0)
const groups = ref([])
const summary = ref({
  total_revenue: 0,
  total_contra_revenue: 0,
  total_direct_cost_a: 0,
  total_direct_cost_b: 0,
  total_direct_cost: 0,
  gross_profit: 0,
})

const isBusy = computed(() => loading.value || exporting.value || previewing.value)

const exportStatusText = computed(() => {
  if (exportProgress.value >= 100) return t('accounts.download_ready')
  if (exportProgress.value >= 70) return t('accounts.preparing_file')
  if (exportProgress.value >= 35) return t('accounts.building_report')
  return t('accounts.collecting_report_data')
})

const previewStatusText = computed(() => {
  if (previewError.value) return t('accounts.preview_load_failed')
  if (exportProgress.value >= 100) return t('accounts.opening_pdf_preview')
  if (exportProgress.value >= 70) return t('accounts.preparing_preview')
  if (exportProgress.value >= 35) return t('accounts.building_report')
  return t('accounts.collecting_report_data')
})

let progressTimer = null
let previewReadyTimer = null

const { filters, hasActiveFilters, resetFilters: resetDimensionFilters } = useTableFilters({
  job_list_id: '',
  work_order_id: '',
  client_id: '',
  agent_id: '',
  principal_id: '',
})

const { data: workOrdersData } = useReportWorkOrdersQuery()
const { data: jobsData } = useReportJobsQuery()
const { data: clientsData } = useReportClientsQuery()
const { data: agentsData } = useReportAgentsQuery()
const { data: principalsData } = useReportPrincipalsQuery()

const toSelectOptions = (queryResult) => {
  const payload = queryResult?.data
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  return []
}

const workOrderOptions = computed(() => toSelectOptions(workOrdersData.value))
const jobOptions = computed(() => toSelectOptions(jobsData.value))
const clientOptions = computed(() => toSelectOptions(clientsData.value))
const agentOptions = computed(() => toSelectOptions(agentsData.value))
const principalOptions = computed(() => toSelectOptions(principalsData.value))

const summaryCards = computed(() => [
  {
    key: 'revenue',
    title: t('accounts.total_revenue'),
    value: summary.value.total_revenue,
    valueClass: 'text-slate-900',
  },
  {
    key: 'direct-cost',
    title: t('accounts.total_direct_cost'),
    value: summary.value.total_direct_cost,
    valueClass: 'text-rose-700',
  },
  {
    key: 'contra',
    title: t('accounts.total_contra_revenue'),
    value: summary.value.total_contra_revenue,
    valueClass: 'text-amber-700',
  },
  {
    key: 'gross-profit',
    title: t('accounts.gross_profit'),
    value: summary.value.gross_profit,
    valueClass: summary.value.gross_profit >= 0 ? 'text-emerald-700' : 'text-rose-700',
  },
])

const tableRows = computed(() =>
  groups.value.flatMap((group) => {
    const rows = [
      {
        kind: 'header',
        key: `header-${group.type}`,
        label: group.type_label,
        sectionTotal: group.section_total,
      },
      ...group.rows.map((row) => ({
        kind: 'row',
        key: `row-${row.account_id}`,
        ...row,
      })),
    ]

    return rows
  }),
)

const periodLabel = computed(() => {
  if (fromDate.value && toDate.value) {
    return t('accounts.period_range', { from: fromDate.value, to: toDate.value })
  }
  if (toDate.value) {
    return t('accounts.period_as_of', { date: toDate.value })
  }
  return ''
})

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

function entryRowClass(entry) {
  if (entry.kind === 'header') return 'bg-slate-100'
  return 'hover:bg-slate-50'
}

function filterByName(option, query) {
  const q = String(query || '').toLowerCase()
  return String(option?.name ?? '').toLowerCase().includes(q)
}

function formatAmount(amount) {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number(amount) || 0)
}

function resolveErrorMessage(error) {
  return (
    error?.message
    || error?.errors?.to_date?.[0]
    || error?.errors?.from_date?.[0]
    || Object.values(error?.errors ?? {})[0]?.[0]
    || t('accounts.gross_profit_load_error')
  )
}

function exportErrorMessage(error, fallback) {
  if (typeof error === 'string' && error.trim()) return error
  return error?.message || error?.error || fallback
}

function activeFilters() {
  return removeEmptyKeys({
    from_date: fromDate.value || undefined,
    to_date: toDate.value || undefined,
    ...filters.value,
  })
}

function resetFilters() {
  resetDimensionFilters()
  loadReport()
}

function revokePreviewUrl() {
  if (previewUrl.value) {
    window.URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = ''
  }
}

function closePreview() {
  stopExportProgress()
  clearTimeout(previewReadyTimer)
  previewReadyTimer = null
  previewOpen.value = false
  previewing.value = false
  previewError.value = ''
  exportProgress.value = 0
  revokePreviewUrl()
  previewFilename.value = ''
}

function onPreviewReady() {
  if (!previewUrl.value || previewError.value) return
  clearTimeout(previewReadyTimer)
  previewReadyTimer = null
  stopExportProgress()
  exportProgress.value = 100
  previewing.value = false
}

async function viewBreakdownReport() {
  clearTimeout(previewReadyTimer)
  previewReadyTimer = null
  previewOpen.value = true
  previewing.value = true
  previewError.value = ''
  revokePreviewUrl()
  startExportProgress()

  try {
    const { blob, filename } = await fetchGrossProfitBreakdownPdf(activeFilters())
    previewFilename.value = filename
    previewUrl.value = window.URL.createObjectURL(blob)
    await finishExportProgress()
    previewReadyTimer = window.setTimeout(() => {
      onPreviewReady()
    }, 1200)
  } catch (error) {
    stopExportProgress()
    exportProgress.value = 0
    previewError.value = exportErrorMessage(error, t('accounts.breakdown_pdf_load_error'))
    previewing.value = false
  }
}

function downloadPreviewPdf() {
  if (!previewUrl.value) return
  const link = document.createElement('a')
  link.href = previewUrl.value
  link.setAttribute('download', previewFilename.value || 'gross-profit-breakdown.pdf')
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

function printPreviewPdf() {
  if (!previewUrl.value) return
  const printWindow = window.open(previewUrl.value, '_blank')
  if (!printWindow) {
    toast.error(t('accounts.allow_popups_to_print'))
    return
  }
  printWindow.addEventListener('load', () => {
    printWindow.focus()
    printWindow.print()
  })
}

async function downloadBreakdownReport() {
  exporting.value = true
  startExportProgress()
  try {
    await exportGrossProfitBreakdownPdf(activeFilters())
    await finishExportProgress()
    toast.success(t('accounts.breakdown_pdf_generated'))
  } catch (error) {
    stopExportProgress()
    toast.error(exportErrorMessage(error, t('accounts.breakdown_pdf_load_error')))
  } finally {
    exporting.value = false
    exportProgress.value = 0
  }
}

async function loadReport() {
  loading.value = true
  loadError.value = ''
  try {
    const payload = await fetchGrossProfit(activeFilters())
    const data = payload?.data ?? payload
    groups.value = Array.isArray(data?.groups) ? data.groups : []
    summary.value = {
      total_revenue: Number(data?.summary?.total_revenue || 0),
      total_contra_revenue: Number(data?.summary?.total_contra_revenue || 0),
      total_direct_cost_a: Number(data?.summary?.total_direct_cost_a || 0),
      total_direct_cost_b: Number(data?.summary?.total_direct_cost_b || 0),
      total_direct_cost: Number(data?.summary?.total_direct_cost || 0),
      gross_profit: Number(data?.summary?.gross_profit || 0),
    }
  } catch (error) {
    const message = resolveErrorMessage(error)
    loadError.value = message
    groups.value = []
    toast.error(message)
  } finally {
    loading.value = false
  }
}

onMounted(loadReport)

onUnmounted(() => {
  stopExportProgress()
  clearTimeout(previewReadyTimer)
  previewReadyTimer = null
  revokePreviewUrl()
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
