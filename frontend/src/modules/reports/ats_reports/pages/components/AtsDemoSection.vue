<template>
  <div class="min-h-screen bg-gray-50 space-y-2">
    <!-- Header -->
    <PageHeader title="ATS Reports" subtitle="Applicant Tracking System overview" />

    <!-- Stats -->
    <StatsGrid :items="stats" />

    <!-- Hiring Pipeline -->
    <PipelineProgress
      title="ATS Process"
      :data="reportsData"
      totalKey="total_process"
      :items="pipelineItems"
    />

    <!-- Recent Last 10 Reports -->
    <div class="bg-white rounded-xl shadow p-6">
      <h2 class="text-lg font-semibold text-gray-700 mb-4">Recent Ats Reports</h2>

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
import { useAtsReportsQuery } from '../../queries/useAtsReportsQuery'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useAtsReportStore } from '../../store/atsReportStore'
import PageHeader from '@/modules/reports/shared/PageHeader.vue'
import PipelineProgress from '@/modules/reports/shared/PipelineProgress.vue'
import StatsGrid from '@/modules/reports/shared/StatsGrid.vue'

const store = useAtsReportStore()

const { columns } = useCrudTable(
  store,
  [
    { key: 'job_name', label: 'Job Name' },
    { key: 'work_order', label: 'Demand Letter' },
    { key: 'client', label: 'Client' },
    { key: 'candidate', label: 'Candidate Name' },
    { key: 'phone', label: 'Phone' },
    { key: 'process', label: 'Process' },
    { key: 'status', label: 'Status' },
    { key: 'age', label: 'Age' },
    { key: 'remarks', label: 'Remarks' },
    { key: 'started_at', label: 'Started At' },
    { key: 'completed_at', label: 'Completed At' },
  ],
  { timestamps: false, trackUser: false },
)

const { data } = useAtsReportsQuery()
const reportsData = computed(() => data.value?.data?.data ?? {})
const recentReports = computed(() => (reportsData.value?.recent_process_reports ?? []).slice(0, 10))

const stats = computed(() => [
  {
    key: 'expiring',
    label: 'Total Expiring Process',
    value: reportsData.value.total_expiring_process,
  },
  {
    key: 'expired',
    label: 'Total Expired Process',
    value: reportsData.value.total_expired_process,
  },
  {
    key: 'active',
    label: 'Total Active Process',
    value: reportsData.value.total_active_process,
  },
  {
    key: 'total',
    label: 'Total Process',
    value: reportsData.value.total_process,
  },
])

const pipelineItems = [
  { key: 'total_active_process', label: 'Active' },
  { key: 'total_expiring_process', label: 'Expiring' },
  { key: 'total_expired_process', label: 'Expired' },
]
</script>
