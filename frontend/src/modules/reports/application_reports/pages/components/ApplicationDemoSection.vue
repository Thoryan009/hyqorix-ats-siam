<template>
  <div class="min-h-screen bg-gray-50 space-y-2">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Applicant Reports</h1>
        <p class="text-sm text-gray-500">Application management overview</p>
      </div>
    </div>

    <!-- Stats -->
    <StatsGrid :items="stats" />

    <!-- Recent Applicant Reports -->
    <div
      class="bg-white rounded-xl shadow p-6"
      v-if="authStore.userType != 'client' && authStore.userType != 'agent' && authStore.userType != 'principal'"
    >
      <h2 class="text-lg font-semibold text-gray-700 mb-4">Recent Applicant Reports</h2>

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
import StatsGrid from '@/modules/reports/shared/StatsGrid.vue'
import { useApplicationReportsQuery } from '../../queries/useApplicationReportsQuery'

import { useApplicationReportStore } from '../../store/applicationReportStore'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useAuthStore } from '@/modules/auth/store/authStore'

const store = useApplicationReportStore()
const authStore = useAuthStore()

const { columns } = useCrudTable(
  store,
  [
    { key: 'job_name', label: 'Job Name' },
    { key: 'name', label: 'Name' },
    { key: 'country', label: 'Country' },
    { key: 'client', label: 'Client' },
    { key: 'work_order_id', label: 'D. Letter' },
    { key: 'sex', label: 'Sex' },
    { key: 'mobile', label: 'Mobile' },
    // { key: 'email', label: 'Email' },
    { key: 'passport_no', label: 'Passport' },
    { key: 'application_id', label: 'Application ID' },
    { key: 'status', label: 'Status' },
  ],
  { timestamps: false }
)

const { data } = useApplicationReportsQuery()
const reportsData = computed(() => data.value?.data ?? {})
const recentReports = computed(() => (reportsData.value?.recent_applications ?? []).slice(0, 10))

const stats = computed(() => [
  {
    key: 'total_applications',
    label: 'Total Applicant',
    value: reportsData.value?.total_applications ?? 0,
  },
  {
    key: 'total_application_process',
    label: 'Total Applicant Process',
    value: reportsData.value?.total_application_process ?? 0,
  },
])
</script>
