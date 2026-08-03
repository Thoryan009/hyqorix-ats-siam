<template>
  <!-- Page wrapper -->
  <div   id="flightSummaryReport-print" class="min-h-screen flex flex-col">
    <div class="w-[400mm] mx-auto py-4 px-0 rounded-lg flex flex-col flex-1">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <LeftHeader />
      </div>
<!-- <pre>{{ data }}</pre> -->
 <!-- <pre>{{ route.query }}</pre> -->
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

import { formatDate } from '@/shared/helpers/formatDateTime'
import LeftHeader from '../../reports/shared/LeftHeader.vue'
import { useDashboardStore } from '../../dashboard/store/dashboardStore'
import { printWithOrientation } from '@/shared/utils/printOrientation'
import { useFlightSummaryQuery } from '../queries/useDashboardQuery.js'
const route = useRoute()
const store = useDashboardStore()

const reportType = route.params.type

const { columns } = useCrudTable(
  store,
  [
  { key: 'flight_no', label: 'Flight No' },
  { key: 'client', label: 'Client' },
  { key: 'candidate_count', label: 'Candidate Count' },
  { key: 'country', label: 'Country' },
  { key: 'flight_date', label: 'Flight Date' },
  { key: 'flight_time', label: 'Flight Time' },
  { key: 'from_city', label: 'From City' },
  { key: 'landing_airport', label: 'Landing Airport' },
  { key: 'landing_time', label: 'Landing Time' },

  ],
  { timestamps: false, trackUser: false },
)

const filters = computed(() => ({

  client_id: route.query.client_id || null,
  country_id: route.query.country_id || null,

  from_date: route.query.departure_from || null,
  to_date: route.query.departure_to || null,
  type: route.params.type,
}))

const page = ref(1)
const perPage = ref(100000)
const { data } = useFlightSummaryQuery(
    page,
    perPage,
    filters)
const rows = computed(() => data.value?.data?.data.data ?? [])


const clientName = computed(() => route.query.clientName || null)
const countryName = computed(() => route.query.countryName || null)
const fromDate = computed(() => route.query.departure_from || null)
const toDate = computed(() => route.query.departure_to || null)

const reportTitle = computed(() => {
  // base title
  let title = `Flight Summary Report`

  // collect optional parts
  const parts = []

  if (clientName.value) {
    parts.push(clientName.value.replaceAll('_', ' '))
  }

  if (countryName.value) {
    parts.push(countryName.value.replaceAll('_', ' '))
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


onMounted(() => {
  setTimeout(() => {
    printWithOrientation('landscape', '8mm', 'flightSummaryReport-print')
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
  #flightSummaryReport-print {
    background: #fff;
    color: #000;
  }

  #flightSummaryReport-print * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  #flightSummaryReport-print h1,
  #flightSummaryReport-print h2,
  #flightSummaryReport-print h3,
  #flightSummaryReport-print p,
  #flightSummaryReport-print span,
  #flightSummaryReport-print th,
  #flightSummaryReport-print td {
    color: #000 !important;
  }

  #flightSummaryReport-print .overflow-x-auto {
    overflow: visible !important;
  }
}
</style>

