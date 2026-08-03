<template>
  <!-- Page wrapper -->
  <div   id="expiryReport-print" class="min-h-screen flex flex-col">
    <div class="w-[400mm] mx-auto py-4 px-0 rounded-lg flex flex-col flex-1">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <LeftHeader />
      </div>
      <!-- Title -->
      <div class="mt-3 mb-2">
        <h2 class="text-lg font-semibold text-gray-800 capitalize">
          {{ reportTitle }}
        </h2>
      </div>

      <!-- Content Card -->
      <div class="rounded-lg bg-white shadow-sm">
        <div>
          <!-- <BaseTable :columns="columns" :rows="rows" /> -->
          <BasePrintTable :columns="columns" :rows="rows" />
        </div>
      </div>
    </div>

    <p class="text-xs text-gray-600 mt-1 text-center">
      This ATS report is generated based on system data available as of
      <span class="font-semibold">{{ formatDate(new Date()) }}</span>.
    </p>
  </div>
</template>

<script setup>
import { onMounted, computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useCrudTable } from '@/shared/composables/useCrudTable'
// import { useAtsReportStore } from '../store/atsReportStore'
// import { useAtsSummaryReportQuery} from '../queries/useAtsReportsQuery'
import { formatDate } from '@/shared/helpers/formatDateTime'
import LeftHeader from '../../shared/LeftHeader.vue'
import { useAtsReportStore } from '../../ats_reports/store/atsReportStore'
import { printWithOrientation } from '@/shared/utils/printOrientation'
import { useExpiryReportQuery } from '../queries/useExpiryReportQuery.js'
import { removeTrailingCount } from '@/shared/helpers/removeTrailingCount.js'

const route = useRoute()
const store = useAtsReportStore()

const reportType = route.params.type

const { columns } = useCrudTable(
  store,
  [
 { key: 'passport_no', label: 'Passport No' },
  { key: 'candidate_name', label: 'Candidate Name' },
  { key: 'mobile', label: 'Mobile' },
  { key: 'agent_name', label: 'Agent Name' },
  { key: 'client_name', label: 'Client Name' },
  { key: 'job_name', label: 'Job Name' },
  { key: 'agent_mobile_no', label: 'Agent Mobile No' },
  { key: 'document', label: 'Document' },
  { key: 'expiry_date_formatted', label: 'Expiry Date' },
  { key: 'days_left', label: 'Days Left' },
  { key: 'current_process', label: 'Current Process' },
  ],
  { timestamps: false, trackUser: false },
)

const filters = computed(() => ({

  client_id: route.query.client_id || null,
  agent_id: route.query.agent_id || null,
  job_id: route.query.job_id || null,
  process: route.query.process || null,
  from_date: route.query.from_date || null,
  to_date: route.query.to_date || null,
  type: route.params.type,
}))

const page = ref(1)
const perPage = ref(100000)
const { data } = useExpiryReportQuery(
    page,
    perPage,
    filters)
const rows = computed(() => data.value?.data?.data ?? [])


const clientName = computed(() => route.query.clientName || null)
const jobName = computed(() => route.query.jobName || null)
const processName = computed(() => route.query.processName || null)
const agentName = computed(() => route.query.agentName || null)
const fromDate = computed(() => route.query.from_date || null)
const toDate = computed(() => route.query.to_date || null)

const reportTitle = computed(() => {
  // base title
  let title = `Expiry Report`

  // collect optional parts
  const parts = []

  if (clientName.value) {
    parts.push(removeTrailingCount(clientName.value).replaceAll('_', ' '))
  }

  if (jobName.value) {
    parts.push(removeTrailingCount(jobName.value).replaceAll('_', ' '))
  }

  if (agentName.value) {
    parts.push(removeTrailingCount(agentName.value).replaceAll('_', ' '))
  }

  if (processName.value) {
    parts.push(removeTrailingCount(processName.value).replaceAll('_', ' '))
  }



  // add date range
  if (fromDate.value || toDate.value) {
    const dateText = `${formatDate(fromDate.value) || 'Start'} → ${
      formatDate(toDate.value) || 'Now'
    }`
    parts.push(dateText)
  }

  // append only if something exists
  if (parts.length) {
    title += ` — ${parts.join(' | ')}`
  }

  return title
})

// onMounted(() => {
//   setTimeout(() => {
//     window.print()
//   }, 1000)
// })
onMounted(() => {
  setTimeout(() => {
    printWithOrientation('landscape', '8mm', 'expiryReport-print')
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
  #expiryReport-print {
    background: #fff;
    color: #000;
  }

  #expiryReport-print * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  #expiryReport-print h1,
  #expiryReport-print h2,
  #expiryReport-print h3,
  #expiryReport-print p,
  #expiryReport-print span,
  #expiryReport-print th,
  #expiryReport-print td {
    color: #000 !important;
  }

  #expiryReport-print .overflow-x-auto {
    overflow: visible !important;
  }
}
</style>

