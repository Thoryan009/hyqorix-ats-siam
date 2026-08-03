<template>
  <!-- Page wrapper -->
  <div class="min-h-screen flex flex-col print-landscape">
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
      <!-- <pre>{{ data }}</pre> -->
      <!-- Content Card -->
      <div class="rounded-lg bg-white shadow-sm">
        <div>
          <!-- <BaseTable :columns="columns" :rows="rows" /> -->
          <BasePrintTable :columns="columns" :rows="rows" />
        </div>
      </div>
    </div>

    <p class="text-xs text-gray-600 mt-1 text-center">
      This Transaction report is generated based on system data available as of <span class="font-semibold">09 Feb
        2026</span>.
    </p>


  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useCrudTable } from '@/shared/composables/useCrudTable';
import { useTransactionReportStore } from '../store/transactionReportStore';
import { useTransactionReportsQuery } from '../queries/useTransactionReportsQuery';
import LeftHeader from '../../shared/LeftHeader.vue';
import { formatDate } from '@/shared/helpers/formatDateTime';


const route = useRoute()
const store = useTransactionReportStore();

console.log(route);

const reportType = route.params.type

const { columns } = useCrudTable(store,
  [
    { key: 'transaction_id', label: 'Transaction ID' },
    { key: 'bill_no', label: 'Bill No' },
    { key: 'client', label: 'Client' },
    { key: 'work_order_id', label: 'Demand Letter' },
    { key: 'job', label: 'Jobs' },
    { key: 'total_amount', label: 'Total Amount' },
    { key: 'paid_amount', label: 'Paid Amount' },
    { key: 'candidate_name', label: 'Candiate' },
    { key: 'payment_method', label: 'Payment Method' },
    { key: 'payment_date', label: 'Payment Date' },
    { key: 'payment_time', label: 'Payment Time' },
    { key: 'status', label: 'Status' },
  ],
  { timestamps: false, trackUser: false }
)


const filters = computed(() => ({
  status: route.query.status_id || null,
  method: route.query.payment_method || null,
  work_order_id: route.query.work_order_id || null,
  job_id: route.query.job_id || null,
  client_id: route.query.client_id || null,
  from_date: route.query.from_date || null,
  to_date: route.query.to_date || null,
  type: route.params.type,
}))


const { data } = useTransactionReportsQuery(filters)
const rows = computed(() => data.value?.data?.data ?? [])

const status = computed(() => route.query.status || null)
const method = computed(() => route.query.payment_method || null)
const jobName = computed(() => route.query.jobName || null)
const workOrderCode = computed(() => route.query.workOrderId || null)
const clientName = computed(() => route.query.clientId || null)
const fromDate = computed(() => route.query.from_date || null)
const toDate = computed(() => route.query.to_date || null)

const reportTitle = computed(() => {
  // base title
  let title = `${reportType} Transaction Report`

  // collect optional parts
  const parts = []

  if (status.value) {
    parts.push(status.value)
  }

  if (method.value) {
    parts.push(method.value)
  }

  if (jobName.value) {
    parts.push(jobName.value)
  }

  if (workOrderCode.value) {
    parts.push(`${workOrderCode.value}`)
  }

  if (clientName.value) {
    parts.push(`${clientName.value}`)
  }

  // add date range
  if (fromDate.value || toDate.value) {
    const dateText = `${formatDate(fromDate.value) || 'Start'} → ${formatDate(toDate.value) || 'Now'}`
    parts.push(dateText)
  }

  // append only if something exists
  if (parts.length) {
    title += ` - ${parts.join(' | ')}`
  }

  return title
})


onMounted(() => {
  setTimeout(() => {
    window.print()
  }, 1000)
})

</script>

<style scoped>

</style>
