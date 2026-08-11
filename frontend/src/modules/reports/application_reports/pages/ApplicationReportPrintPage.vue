<template>
  <!-- Page wrapper -->
  <div id="applicationSummaryReport-print" class="min-h-screen flex flex-col">
    <div class="w-[400mm] mx-auto py-4 px-0 rounded-lg flex flex-col flex-1">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <LeftHeader />
      </div>

      <!-- Title -->
      <div class="mt-3 mb-2">
        <h2 class="text-lg font-semibold text-gray-800 capitalize">{{ reportTitle }}</h2>
      </div>
      <div v-if="isLoading" class="rounded-lg bg-white shadow-sm py-10 text-center text-gray-500">
        Loading application report...
      </div>

      <div v-else-if="isError" class="rounded-lg bg-white shadow-sm py-10 text-center text-red-600">
        Failed to load application report.
      </div>

      <div v-else class="rounded-lg bg-white shadow-sm">
        <BasePrintTable :columns="columns" :rows="rows" />
      </div>
    </div>

    <p class="text-xs text-gray-600 mt-1 text-center">
      This application report is generated based on system data available as of
      <span class="font-semibold">{{ formatDate(new Date()) }}</span>.
    </p>
  </div>
</template>

<script setup>
import { watch, computed, ref, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useApplicationReportStore } from '../store/applicationReportStore'
import { useApplicationReportQuery } from '../queries/useApplicationReportsQuery'
import { formatDate } from '@/shared/helpers/formatDateTime'
import LeftHeader from '../../shared/LeftHeader.vue'
import applicationReportTableData from '../data/applicationReportTableData'
import { buildReportTitle, filterColumns } from '../composables/useReportUtils'
import { printWithOrientation } from '@/shared/utils/printOrientation'
import { extractPaginatedRows } from '@/shared/utils/extractPaginatedRows'
import { removeTrailingCount } from '@/shared/helpers/removeTrailingCount'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const route = useRoute()
const store = useApplicationReportStore()
const hasPrinted = ref(false)

const reportType = computed(() => route.params.type)

const toQueryList = (value) => {
  if (value == null || value === '') return null
  return Array.isArray(value) ? value : [value]
}

const tableData = computed(() => {
  return filterColumns(applicationReportTableData, reportType.value).map((column) => ({
    ...column,
    label: t(column.label),
  }))
})
const { columns } = useCrudTable(store, tableData, { timestamps: false, trackUser: false })

const filters = computed(() => ({
  country_id: route.query.country_id || null,
  work_order_id: route.query.work_order_id || null,
  job_id: route.query.job_id || null,
  process_id: toQueryList(route.query.process_id),
  application_status: toQueryList(route.query.application_status),
  client_id: route.query.client_id || null,
  agent_id: route.query.agent_id || null,
  principal_id: route.query.principal_id || null,
  from_date: route.query.from_date || null,
  to_date: route.query.to_date || null,
  type: reportType.value,
}))

const page = ref(1)
const perPage = ref(100000)
const { data, isLoading, isFetching, isSuccess, isError } = useApplicationReportQuery(
  page,
  perPage,
  filters,
)
const rows = computed(() => extractPaginatedRows(data.value?.data))

const countryName = computed(() => route.query.countryName || null)
const jobName = computed(() => route.query.jobName || null)
const workOrderCode = computed(() => route.query.workOrderCode || null)
const clientName = computed(() => route.query.clientName || null)
const agentName = computed(() => route.query.agentName || null)
const principalName = computed(() => route.query.principalName || null)
const fromDate = computed(() => route.query.from_date || null)
const toDate = computed(() => route.query.to_date || null)

//

const reportTitle = computed(() => {
  return buildReportTitle({
    jobName: jobName.value,
    workOrderCode: workOrderCode.value,
    countryName: countryName.value,
    clientName: removeTrailingCount(clientName.value)?.replaceAll('_', ' '),
    agentName: removeTrailingCount(agentName.value)?.replaceAll('_', ' '),
    principalName: principalName.value,
    fromDate: fromDate.value,
    toDate: toDate.value,
    formatDate,
  })
})

watch(
  [isSuccess, isFetching, isError],
  ([success, fetching, error]) => {
    if (hasPrinted.value || fetching || (!success && !error)) return

    hasPrinted.value = true

    if (!success) return

    nextTick(() => {
      printWithOrientation('landscape', '8mm', 'applicationSummaryReport-print')
    })
  },
  { immediate: true },
)
</script>

<style scoped>
@media print {
  .shadow-sm,
  .rounded-lg {
    box-shadow: none !important;
    border-radius: 0 !important;
  }

  .overflow-x-auto {
    overflow: visible !important;
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  tr,
  td,
  th {
    page-break-inside: avoid;
  }
}
</style>


<style scoped>
@media print {
  #applicationSummaryReport-print {
    background: #fff;
    color: #000;
  }

  #applicationSummaryReport-print * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  #applicationSummaryReport-print h1,
  #applicationSummaryReport-print h2,
  #applicationSummaryReport-print h3,
  #applicationSummaryReport-print p,
  #applicationSummaryReport-print span,
  #applicationSummaryReport-print th,
  #applicationSummaryReport-print td {
    color: #000 !important;
  }

  #applicationSummaryReport-print .overflow-x-auto {
    overflow: visible !important;
  }
}
</style>

