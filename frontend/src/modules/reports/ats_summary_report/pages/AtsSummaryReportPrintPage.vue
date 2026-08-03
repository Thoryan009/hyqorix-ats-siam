<template>
  <!-- Page wrapper -->
  <div   id="atsSummaryReport-print" class="min-h-screen flex flex-col">
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
import { useAtsSummaryReportQuery } from '../queries/useAtsSummaryReportQuery.js'
import { printWithOrientation } from '@/shared/utils/printOrientation'
import { removeTrailingCount } from '@/shared/helpers/removeTrailingCount.js'
const route = useRoute()
const store = useAtsReportStore()

const reportType = route.params.type

const { columns } = useCrudTable(
  store,
  [
  { key: 'client_name', label: 'Client Name' },
  { key: 'job_name', label: 'Job Name' },
  { key: 'hiring_list', label: 'Hiring List' },
  { key: 'offer_extended', label: 'Offer Extended' },
  { key: 'visa_authorization', label: 'Visa Authorization' },
  { key: 'medical_test', label: 'Medical Test' },
  { key: 'police_clearance', label: 'Police Clearance' },
  { key: 'trade_test', label: 'Trade Test' },
  { key: 'biometric_enrollment', label: 'Biometric Enrollment' },
  { key: 'embassy_submission', label: 'Embassy Submission' },
  { key: 'bmet_training', label: 'BMET Training' },
  { key: 'bmet_biometric_enrollment', label: 'BMET Biometric Enrollment' },
  { key: 'immigration_clearance', label: 'Immigration Clearance' },
  { key: 'pta_request', label: 'PTA Request' },
  { key: 'tra_process', label: 'TRA Process' },
  { key: 'on_boarding', label: 'On Boarding' },
  ],
  { timestamps: false, trackUser: false },
)

const filters = computed(() => ({

  client_id: route.query.client_id || null,

  from_date: route.query.from_date || null,
  to_date: route.query.to_date || null,
  type: route.params.type,
}))

const page = ref(1)
const perPage = ref(100000)
const { data } = useAtsSummaryReportQuery(
    page,
    perPage,
    filters)
const rows = computed(() => data.value?.data?.rows ?? [])


const clientName = computed(() => route.query.clientName || null)
const fromDate = computed(() => route.query.from_date || null)
const toDate = computed(() => route.query.to_date || null)

const reportTitle = computed(() => {
  // base title
  let title = `Ats Summary Report`

  // collect optional parts
  const parts = []

  if (clientName.value) {
    parts.push(removeTrailingCount(clientName.value).replaceAll('_', ' '))
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
    printWithOrientation('landscape', '8mm', 'atsSummaryReport-print')
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
  #atsSummaryReport-print {
    background: #fff;
    color: #000;
  }

  #atsSummaryReport-print * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  #atsSummaryReport-print h1,
  #atsSummaryReport-print h2,
  #atsSummaryReport-print h3,
  #atsSummaryReport-print p,
  #atsSummaryReport-print span,
  #atsSummaryReport-print th,
  #atsSummaryReport-print td {
    color: #000 !important;
  }

  #atsSummaryReport-print .overflow-x-auto {
    overflow: visible !important;
  }
}
</style>

