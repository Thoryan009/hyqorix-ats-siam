<template>
  <SectionHeader>
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <!-- Responsive Heading -->
        <PageTitle className="capitalize">{{ reportType }} Transaction Reports</PageTitle>
      </div>
    </div>
    <!-- Report Cards -->
    <div
      class="flex flex-col md:flex-row justify-start md:justify-between items-start md:items-center mb-10 md:mb-0"
    >
      <TransactionReportNavigation />
      <div>
        <BaseButton @click="goToPrint">Print Transaction Report</BaseButton>
      </div>
    </div>

    <!-- FILTERS -->
    <div class="flex justify-end items-center my-4">
      <TableFilters
        from-date-label="Payment From"
        to-date-label="Payment To"
        :show-search="false"
        :filters="filters"
        :has-active-filters="hasActiveFilters"
        @reset="resetFilters"
      >
        <!-- Payment Status -->
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">Payment Status</label>
          <BaseSelect v-model="filters.status" :options="statusOptions" placeholder="Select" />
        </div>
        <!-- Payment Method -->
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">Payment Method</label>
          <BaseSelect
            v-model="filters.payment_method"
            :options="methodOptions"
            placeholder="Select"
          />
        </div>
        <!-- Work Order FILTER -->
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">Demand Letter</label>
          <BaseSelect
            v-model="filters.work_order_id"
            :options="workOrderOptions"
            placeholder="Select"
          />
        </div>
        <!-- Jobs FILTER -->
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">Jobs</label>
          <BaseSelect v-model="filters.job_id" :options="jobOptions" placeholder="Select" />
        </div>
        <!-- Clients -->
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">Clients</label>
          <BaseSelect v-model="filters.client_id" :options="clientOptions" placeholder="Select" />
        </div>
      </TableFilters>
    </div>

    <!-- Content Card -->
    <div class="rounded-lg bg-white shadow-sm">
      <div>
        <BaseTable :columns="columns" :rows="rows" />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTransactionReportStore } from '../store/transactionReportStore'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useTransactionReportQuery } from '../queries/useTransactionReportsQuery'
import {
  useReportClientsQuery,
  useReportJobsQuery,
  useReportMethodsQuery,
  useReportStatusQuery,
  useReportWorkOrdersQuery,
} from '../../queries/useReportsQuery'
import { removeEmptyKeys } from '@/shared/helpers/objectHelper'
import TransactionReportNavigation from './components/TransactionReportNavigation.vue'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import PageTitle from '@/shared/components/ui/PageTitle.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'

const route = useRoute()
const router = useRouter()
const reportType = computed(() => route.params.type)
const store = useTransactionReportStore()

const { filters, resetFilters } = useTableFilters({
  status: null,
  payment_method: null,
  work_order_id: null,
  job_id: null,
  client_id: null,
  type: reportType,
  from_date: null,
  to_date: null,
})

const hasActiveFilters = computed(() => {
  return Object.entries(filters.value).some(([key, value]) => key !== 'type' && value)
})

const { data } = useTransactionReportQuery(filters)
const rows = computed(() => data.value?.data?.data ?? [])

const { columns } = useCrudTable(store, [
  { key: 'transaction_id', label: 'Transaction ID' },
  { key: 'bill_no', label: 'Bill No' },
  { key: 'client', label: 'Client' },
  { key: 'work_order_id', label: 'Demand Letter Id' },
  { key: 'job', label: 'Jobs' },
  { key: 'total_amount', label: 'Total Amount' },
  { key: 'paid_amount', label: 'Paid Amount' },
  { key: 'candidate_name', label: 'Candiate' },
  { key: 'payment_method', label: 'Payment Method' },
  { key: 'payment_date', label: 'Payment Date' },
  { key: 'payment_time', label: 'Payment Time' },
  { key: 'status', label: 'Status' },
])

const goToPrint = () => {
  const filtered = removeEmptyKeys(filters.value)
  filtered.status = filtered.status ? getStatusByStatus(filtered.status) : ''

  filtered.payment_method = filtered.payment_method
    ? getPaymentMethodByMethod(filtered.payment_method)
    : ''

  filtered.jobName = filtered.job_id ? getJobNameById(filtered.job_id) : ''

  filtered.workOrderId = filtered.work_order_id ? getWorkOrderIdById(filtered.work_order_id) : ''

  filtered.clientId = filtered.client_id ? getClientNameById(filtered.client_id) : ''

  // ADD DATES
  filtered.from_date = filtered.from_date ? filtered.from_date : ''

  filtered.to_date = filtered.to_date ? filtered.to_date : ''

  const filteredFinal = removeEmptyKeys(filtered)
  // create route URL
  const routeData = router.resolve({
    name: 'Transaction Report Print',
    params: {
      type: reportType.value,
    },
    query: filteredFinal,
  })

  // open new tab
  window.open(routeData.href, '_blank')
}

const getStatusByStatus = (statusId) => {
  return statusOptions.value.find((status) => status.id == statusId).name
}

const getPaymentMethodByMethod = (methodId) => {
  return methodOptions.value.find((method) => method.id == methodId).name
}

const getJobNameById = (jobId) => {
  return jobOptions.value.find((job) => job.id == jobId).name
}

const getWorkOrderIdById = (workOrderId) => {
  return workOrderOptions.value.find((workOrder) => workOrder.id == workOrderId).name
}

const getClientNameById = (clientId) => {
  return clientOptions.value.find((client) => client.id == clientId).name
}

const { data: statusData } = useReportStatusQuery()
const { data: methodData } = useReportMethodsQuery()
const { data: workOrdersData } = useReportWorkOrdersQuery()
const { data: jobsData } = useReportJobsQuery()
const { data: clientsData } = useReportClientsQuery()

const toSelectOptions = (queryResult) => {
  const payload = queryResult?.data
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  return []
}

const statusOptions = computed(() => toSelectOptions(statusData.value))
const methodOptions = computed(() => toSelectOptions(methodData.value))
const workOrderOptions = computed(() => toSelectOptions(workOrdersData.value))
const jobOptions = computed(() => toSelectOptions(jobsData.value))
const clientOptions = computed(() => toSelectOptions(clientsData.value))
</script>
