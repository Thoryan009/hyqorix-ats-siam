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
      <!-- Content Card -->
      <div class="rounded-lg bg-white shadow-sm">
        <div>
          <!-- <BaseTable :columns="columns" :rows="rows" :scrollable="false" /> -->
          <BasePrintTable :columns="columns" :rows="rows" />
        </div>
      </div>
    </div>

    <p class="text-xs text-gray-600 mt-1 text-center">
      This ATS report is generated based on system data available as of
      <span
        class="font-semibold"
      >09 Feb 2026</span>.
    </p>
  </div>
</template>

<script setup>
import { onMounted, computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useApplicationReportStore } from '../store/applicationReportStore'
import { useApplicationReportQuery } from '../queries/useApplicationReportsQuery'
import { formatDate } from '@/shared/helpers/formatDateTime'
import LeftHeader from '../../shared/LeftHeader.vue'
import applicationReportTableData from '../data/applicationReportTableData'
import { buildReportTitle, filterColumns } from '../composables/useReportUtils'
import { printWithOrientation } from '@/shared/utils/printOrientation'
import { removeTrailingCount } from '@/shared/helpers/removeTrailingCount'


const route = useRoute()
const store = useApplicationReportStore()

const reportType = computed(() => route.params.type)


const tableData = computed(() => {
  return filterColumns(applicationReportTableData, reportType.value)
})
const { columns } = useCrudTable(store, tableData, { timestamps: false, trackUser: false })

const filters = computed(() => ({
  country_id: route.query.country_id || null,
  work_order_id: route.query.work_order_id || null,
  job_id: route.query.job_id || null,
   process_id: route.query.process_id || null,
  application_status: route.query.application_status || null,
  client_id: route.query.client_id || null,
  agent_id: route.query.agent_id || null,
  principal_id: route.query.principal_id || null,
  from_date: route.query.from_date || null,
  to_date: route.query.to_date || null,
  type: route.params.type,
}))

const page = ref(1)
const perPage = ref(100000)
const { data } = useApplicationReportQuery(
    page,
    perPage,
    filters
)
const rows = computed(() => data.value?.data?.data ?? [])

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
    clientName: removeTrailingCount(clientName.value).replaceAll('_', ' '),
    agentName: removeTrailingCount(agentName.value).replaceAll('_', ' '),
    principalName: principalName.value,
    fromDate: fromDate.value,
    toDate: toDate.value,
    formatDate,
  })
})

onMounted(() => {
  setTimeout(() => {
    printWithOrientation('landscape', '8mm', 'applicationSummaryReport-print')
  }, 1000)
})
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

