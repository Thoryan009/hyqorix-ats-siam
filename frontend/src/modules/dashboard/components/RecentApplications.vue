<template>
  <div
    class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200 hover:shadow-2xl transition-all duration-300 cursor-pointer"
    @click="$router.push('/applications')"
  >
    <!-- Header with linear -->
    <div
      class="bg-linear-to-r from-tertiary to-tertiary/70 px-4 py-2.5 flex items-center justify-between"
    >
      <div class="flex items-center gap-2">
        <div
          class="w-7 h-7 rounded-md bg-white/20 backdrop-blur-sm flex items-center justify-center text-white"
        >
          <i class="fa fa-users text-sm"></i>
        </div>
        <h2 class="text-sm font-semibold text-white">Latest Applicants</h2>
      </div>
      <span class="bg-white/20 backdrop-blur-sm text-white text-xs font-medium px-2.5 py-0.5 rounded-full"
        >{{ applications?.length || 0 }} New</span
      >
    </div>

    <!-- Content -->
    <div class="p-6">
      <BaseTable
        :columns="columns"
        :rows="applications || []"
        :scrollable="false"
        theadBgColor="bg-tertiary/20"
      />
    </div>
  </div>
</template>

<script setup>
import BaseTable from '@/shared/components/base/BaseTable.vue'
import { computed } from 'vue'

// Props
const props = defineProps({
  recent_applications: {
    type: Array,
    default: () => [],
  },
})

// Columns definition
const columns = [
  { key: 'applicationId', label: 'Applicant ID' },
  { key: 'candidateName', label: 'Candidate Name' },
  { key: 'jobName', label: 'Job Name' },
  { key: 'appliedOn', label: 'Applied On' },
]

// Map the incoming data to table format
const applications = computed(() =>
  (props.recent_applications || []).map((app) => ({
    id: app.id,
    applicationId: app.application_id,
    candidateName: app.full_name,
    jobName: app.job,
    appliedOn: new Date(app.created_at).toLocaleDateString('en-GB', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    }),
  })),
)
</script>
