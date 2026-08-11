<template>
  <div id="atsReport-print" class="min-h-screen flex flex-col">
    <div class="w-[400mm] mx-auto py-4 px-0 rounded-lg flex flex-col flex-1">
      <div class="flex justify-between items-center">
        <LeftHeader />
      </div>

      <div class="mt-3 mb-2">
        <h2 class="text-lg font-semibold text-gray-800 capitalize">
          {{ reportTitle }}
        </h2>
      </div>

      <div v-if="isLoading" class="rounded-lg bg-white shadow-sm py-10 text-center text-gray-500">
        Loading ATS report...
      </div>

      <div v-else-if="isError" class="rounded-lg bg-white shadow-sm py-10 text-center text-red-600">
        Failed to load ATS report.
      </div>

      <div v-else class="rounded-lg bg-white shadow-sm">
        <BasePrintTable :columns="columns" :rows="rows" />
      </div>
    </div>

    <p class="text-xs text-gray-600 mt-1 text-center">
      This ATS report is generated based on system data available as of
      <span class="font-semibold">{{ formatDate(new Date()) }}</span>.
    </p>
  </div>
</template>

<script setup>
import { watch, computed, ref, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useAtsReportStore } from '../store/atsReportStore'
import { useAtsReportQuery } from '../queries/useAtsReportsQuery'
import { formatDate } from '@/shared/helpers/formatDateTime'
import LeftHeader from '../../shared/LeftHeader.vue'
import { printWithOrientation } from '@/shared/utils/printOrientation'
import { extractPaginatedRows } from '@/shared/utils/extractPaginatedRows'
import { removeTrailingCount } from '@/shared/helpers/removeTrailingCount'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const route = useRoute()
const store = useAtsReportStore()
const hasPrinted = ref(false)

const reportType = computed(() => route.params.type)

const toQueryList = (value) => {
  if (value == null || value === '') return null
  return Array.isArray(value) ? value : [value]
}

const { columns } = useCrudTable(
  store,
  computed(() => [
    { key: 'job_name', label: t('shared.labels.job_name') },
    { key: 'passport_no', label: t('shared.labels.passport_no') },
    { key: 'client', label: t('shared.labels.client') },
    { key: 'candidate', label: t('shared.labels.candidate') },
    { key: 'phone', label: t('shared.labels.phone') },
    { key: 'medical_test', label: t('dashboard.pipeline.medical_test') },
    { key: 'police_clearance', label: t('dashboard.pipeline.police_clearance') },
    { key: 'trade_test', label: t('dashboard.pipeline.trade_test') },
    { key: 'biometric_enrollment', label: t('dashboard.pipeline.biometric_enrollment') },
    { key: 'immigration_clearance', label: t('dashboard.pipeline.immigration_clearance') },
    { key: 'visa_endorsement', label: t('report.visa_endorsement') },
    { key: 'visa_expiry', label: t('ats.visa_expiry_date') },
    { key: 'flight_date', label: t('report.flight_date') },
    { key: 'status', label: t('shared.labels.status') },
    { key: 'days', label: t('report.days') },
    { key: 'total_process_days', label: t('report.total_process_days') },
    { key: 'remarks', label: t('application.remarks') },
  ]),
  { timestamps: false, trackUser: false },
)

const filters = computed(() => ({
  process_id: toQueryList(route.query.process_id),
  work_order_id: route.query.work_order_id || null,
  job_id: route.query.job_id || null,
  client_id: route.query.client_id || null,
  application_status: toQueryList(route.query.application_status),
  from_date: route.query.from_date || null,
  to_date: route.query.to_date || null,
  type: reportType.value,
}))

const page = ref(1)
const perPage = ref(100000)
const { data, isLoading, isFetching, isSuccess, isError } = useAtsReportQuery(
  page,
  perPage,
  filters,
)
const rows = computed(() => extractPaginatedRows(data.value?.data))

const jobName = computed(() => route.query.jobName || null)
const workOrderCode = computed(() => route.query.workOrderId || null)
const processName = computed(() => route.query.processName || null)
const clientName = computed(() => route.query.clientId || null)
const fromDate = computed(() => route.query.from_date || null)
const toDate = computed(() => route.query.to_date || null)

const reportTitle = computed(() => {
  let title = `${reportType.value} Process Report`
  const parts = []

  if (jobName.value) parts.push(jobName.value)
  if (workOrderCode.value) parts.push(`${workOrderCode.value}`)
  if (processName.value) parts.push(String(processName.value).replaceAll('_', ' '))
  if (clientName.value) parts.push(removeTrailingCount(clientName.value).replaceAll('_', ' '))

  if (fromDate.value || toDate.value) {
    parts.push(
      `${formatDate(fromDate.value) || 'Start'} → ${formatDate(toDate.value) || 'Now'}`,
    )
  }

  if (parts.length) {
    title += ` — ${parts.join(' | ')}`
  }

  return title
})

watch(
  [isSuccess, isFetching, isError],
  ([success, fetching, error]) => {
    if (hasPrinted.value || fetching || (!success && !error)) return

    hasPrinted.value = true

    if (!success) return

    nextTick(() => {
      printWithOrientation('landscape', '8mm', 'atsReport-print')
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
  #atsReport-print {
    background: #fff;
    color: #000;
  }

  #atsReport-print * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  #atsReport-print h1,
  #atsReport-print h2,
  #atsReport-print h3,
  #atsReport-print p,
  #atsReport-print span,
  #atsReport-print th,
  #atsReport-print td {
    color: #000 !important;
  }
}
</style>
