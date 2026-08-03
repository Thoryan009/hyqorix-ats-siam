<template>
  <div class="min-h-screen bg-gray-50 space-y-2">
    <!-- Header -->
    <PageHeader title="Transaction Reports" subtitle="Financial transactions overview" />

    <!-- Summary Stats -->
    <StatsGrid :candidateStats="candidateStats" :clientStats="clientStats" />

    <!-- Transaction Flow -->
    <PipelineProgress
      title="Transaction Flow"
      :data="pipelineData"
      totalKey="total_transactions_count"
      :items="pipelineItems"
    />

    <!-- Recent Last 10 Reports -->
    <div class="bg-white rounded-xl shadow p-6">
      <h2 class="text-lg font-semibold text-gray-700 mb-4">Recent Transaction Reports</h2>

      <!-- Content Card -->
      <div class="rounded-lg bg-white shadow-sm">
        <div>
          <BaseTable :columns="columns" :rows="recentReports" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useTransactionReportStore } from '../../store/transactionReportStore'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTransactionReportsQuery } from '../../queries/useTransactionReportsQuery'
import PageHeader from '@/modules/reports/shared/PageHeader.vue'
import PipelineProgress from '@/modules/reports/shared/PipelineProgress.vue'
import StatsGrid from './StatsGrid.vue'

const store = useTransactionReportStore()

const { columns } = useCrudTable(
  store,
  [
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
  ],
  { timestamps: false },
)

const { data } = useTransactionReportsQuery()
const reportsData = computed(() => data.value?.data?.data ?? {})
const recentReports = computed(() => (reportsData.value?.recent_transactions ?? []).slice(0, 10))

const candidateStats = computed(() => {
  const candidate = reportsData.value.candidate_transactions ?? {}
  return [
    {
      key: 'count',
      label: 'Total Transactions',
      value: candidate.count ?? 0,
    },
    {
      key: 'total_amount',
      label: 'Total Amount',
      value: candidate.total_amount ?? '৳0',
    },
    {
      key: 'total_paid_amount',
      label: 'Total Paid Amount',
      value: candidate.total_paid_amount ?? '৳0',
    },
    {
      key: 'total_due_amount',
      label: 'Total Due Amount',
      value: candidate.total_due_amount ?? '৳0',
    },
  ]
})

const clientStats = computed(() => {
  const client = reportsData.value.client_transactions ?? {}
  return [
    {
      key: 'count',
      label: 'Total Transactions',
      value: client.count ?? 0,
    },
    {
      key: 'total_amount',
      label: 'Total Amount',
      value: client.total_amount ?? '$0',
    },
    {
      key: 'total_paid_amount',
      label: 'Total Paid Amount',
      value: client.total_paid_amount ?? '$0',
    },
    {
      key: 'total_due_amount',
      label: 'Total Due Amount',
      value: client.total_due_amount ?? '$0',
    },
  ]
})

const pipelineItems = [
  { key: 'client_transactions_count', label: 'Client' },
  { key: 'candidate_transactions_count', label: 'Candidate' },
]

const pipelineData = computed(() => ({
  total_transactions_count: reportsData.value.total_transactions_count ?? 0,
  client_transactions_count: reportsData.value.client_transactions?.count ?? 0,
  candidate_transactions_count: reportsData.value.candidate_transactions?.count ?? 0,
}))
</script>
