<template>
  <div class="space-y-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <h4 class="text-sm font-semibold text-gray-900">Select Applications</h4>
        <p class="mt-1 text-xs text-gray-500">
          Choose a job, then select one or more candidates for bill entry
          <span v-if="expenseHeadName"> — expense head: {{ expenseHeadName }}</span>
        </p>
        <p v-if="excludedCount" class="mt-1 text-xs text-amber-700">
          {{ excludedCount }} candidate{{ excludedCount === 1 ? '' : 's' }} already billed for this
          expense head {{ excludedCount === 1 ? 'is' : 'are' }} hidden.
        </p>
      </div>
      <span
        v-if="selectedCount"
        class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary"
      >
        {{ selectedCount }} selected
      </span>
    </div>

    <div class="space-y-2">
      <BaseLabel for="direct_cost_job_id">Job</BaseLabel>
      <BaseSearchSelect
        id="direct_cost_job_id"
        :model-value="jobId"
        :options="jobOptions"
        :placeholder="jobListStore.isLoadingJobs ? 'Loading jobs...' : 'Search and select job'"
        :required="true"
        :disabled="jobListStore.isLoadingJobs || !jobOptions.length"
        @update:model-value="updateJob"
      />
      <p v-if="jobListStore.isLoadingJobs" class="text-xs text-gray-500">Loading jobs...</p>
      <p v-else-if="selectedJob" class="text-xs text-gray-500">
        {{ selectedJob.job_code }} · {{ selectedJob.client_name }}
      </p>
    </div>

    <div
      v-if="!jobListStore.isLoadingJobs && !jobOptions.length"
      class="rounded-lg border border-dashed border-gray-200 p-6 text-center text-sm text-gray-500"
    >
      No open jobs with ATS applications found.
    </div>

    <div
      v-else-if="!jobId"
      class="rounded-lg border border-dashed border-gray-200 p-6 text-center text-sm text-gray-500"
    >
      Select a job to view candidate applications.
    </div>

    <div v-else-if="isApplicationsLoading" class="rounded-lg border border-dashed border-gray-200 p-6 text-center text-sm text-gray-500">
      Loading applications...
    </div>

    <div v-else class="overflow-hidden rounded-lg border border-gray-200">
      <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
          <div>
            <p class="font-semibold text-gray-900">{{ selectedJob?.job_name }}</p>
            <p class="mt-0.5 text-xs text-gray-500">
              {{ selectedJob?.job_code }} · {{ selectedJob?.client_name }}
            </p>
          </div>
          <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-gray-600 ring-1 ring-gray-200">
            {{ filteredApplications.length }} applications
          </span>
        </div>
        <div v-if="applications.length" class="mt-3">
          <BaseInput
            id="direct_cost_application_search"
            v-model="applicationSearch"
            placeholder="Search by name or passport no..."
          />
        </div>
      </div>

      <div v-if="!filteredApplications.length" class="px-4 py-5 text-sm text-gray-500">
        <template v-if="applications.length && applicationSearch.trim()">
          No applications match your search.
        </template>
        <template v-else-if="applications.length && excludedCount">
          All candidates for this job already have a pending or approved bill for this expense head.
        </template>
        <template v-else>
          No ATS applications found for this job.
        </template>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
          <thead class="bg-white">
            <tr>
              <th class="border-b border-gray-100 px-3 py-2 text-left w-12">
                <input
                  type="checkbox"
                  class="h-4 w-4 cursor-pointer accent-primary"
                  :checked="allSelected"
                  :indeterminate.prop="someSelected && !allSelected"
                  @change="toggleAll"
                />
              </th>
              <th class="border-b border-gray-100 px-3 py-2 text-left">Candidate Name</th>
              <th class="border-b border-gray-100 px-3 py-2 text-left">Passport No</th>
              <th class="border-b border-gray-100 px-3 py-2 text-left">Current Process</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="application in filteredApplications"
              :key="application.id"
              class="cursor-pointer border-b border-gray-50 transition hover:bg-primary/5"
              :class="isSelected(application.id) ? 'bg-primary/5' : ''"
              @click="toggleApplication(application.id)"
            >
              <td class="px-3 py-2">
                <input
                  type="checkbox"
                  class="h-4 w-4 cursor-pointer accent-primary"
                  :checked="isSelected(application.id)"
                  @click.stop
                  @change="toggleApplication(application.id)"
                />
              </td>
              <td class="px-3 py-2 font-medium text-gray-900">{{ application.name }}</td>
              <td class="px-3 py-2 text-gray-700">{{ application.passport_no }}</td>
              <td class="px-3 py-2">
                <span
                  class="rounded-full px-2.5 py-1 text-xs font-semibold"
                  :class="statusClass(application.process_status)"
                >
                  {{ formatApplicationStatusLabel(application.process_status) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useJobListStore } from '@/finance/store/jobListStore'
import { useExpensePaymentStore } from '@/finance/store/expensePaymentStore'
import {
  formatApplicationStatusLabel,
  sortApplicationsAlphabetically,
} from '@/finance/utils/jobListMapper'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  jobId: {
    type: [String, Number],
    default: '',
  },
  expenseHeadName: {
    type: String,
    default: '',
  },
  expenseHeadId: {
    type: [String, Number],
    default: '',
  },
})

const emit = defineEmits(['update:modelValue', 'update:jobId', 'select'])

const jobListStore = useJobListStore()
const paymentStore = useExpensePaymentStore()
const { applicationsByJob, loadingApplications } = storeToRefs(jobListStore)
const { payments } = storeToRefs(paymentStore)
const applicationSearch = ref('')

const billedApplicationIds = computed(() => {
  payments.value
  return paymentStore.getBilledApplicationIdsByHead(props.expenseHeadId)
})

onMounted(() => {
  jobListStore.fetchJobList()
})

const jobOptions = computed(() => jobListStore.getJobSelectOptions())

const selectedJob = computed(() => (props.jobId ? jobListStore.getJob(props.jobId) : null))

const allApplications = computed(() => {
  const jobId = Number(props.jobId)
  if (!jobId) return []

  return sortApplicationsAlphabetically(applicationsByJob.value[jobId] ?? [])
})

const applications = computed(() => {
  if (!props.expenseHeadId) return allApplications.value

  return allApplications.value.filter(
    (application) => !billedApplicationIds.value.has(Number(application.id))
  )
})

const excludedCount = computed(() => {
  if (!props.expenseHeadId) return 0

  return allApplications.value.length - applications.value.length
})

const isApplicationsLoading = computed(() => Boolean(loadingApplications.value[Number(props.jobId)]))

const filteredApplications = computed(() => {
  const query = applicationSearch.value.trim().toLowerCase()
  if (!query) return applications.value

  return applications.value.filter((application) => {
    const name = String(application.name || '').toLowerCase()
    const passport = String(application.passport_no || '').toLowerCase()
    return name.includes(query) || passport.includes(query)
  })
})

const selectedIds = computed(() =>
  (props.modelValue || []).map((id) => Number(id)).filter(Boolean)
)

const selectedCount = computed(() => selectedIds.value.length)

const allSelected = computed(
  () =>
    filteredApplications.value.length > 0 &&
    filteredApplications.value.every((application) =>
      selectedIds.value.includes(Number(application.id))
    )
)

const someSelected = computed(() => selectedIds.value.length > 0)

const isSelected = (applicationId) => selectedIds.value.includes(Number(applicationId))

const emitSelection = (ids) => {
  const normalized = ids.map((id) => String(id))
  emit('update:modelValue', normalized)
  emit('select', normalized)
}

const updateJob = (value) => {
  applicationSearch.value = ''
  emit('update:jobId', value ? String(value) : '')
  emitSelection([])
}

const toggleApplication = (applicationId) => {
  const id = Number(applicationId)
  const next = isSelected(id)
    ? selectedIds.value.filter((item) => item !== id)
    : [...selectedIds.value, id]

  emitSelection(next)
}

const toggleAll = () => {
  if (allSelected.value) {
    emitSelection([])
    return
  }

  emitSelection(filteredApplications.value.map((application) => application.id))
}

watch(
  () => props.jobId,
  (jobId) => {
    applicationSearch.value = ''

    if (!jobId) return

    jobListStore.fetchApplicationsForJob(jobId)
  },
  { immediate: true }
)

watch(
  () => [props.expenseHeadId, props.jobId],
  () => {
    if (!props.jobId) return

    // Only prune against the full job list — do not use the billed-filtered
    // list, or a successful submit clears selections before print snapshot use.
    const validIds = selectedIds.value.filter((id) =>
      allApplications.value.some((application) => Number(application.id) === Number(id))
    )

    if (validIds.length !== selectedIds.value.length) {
      emitSelection(validIds)
    }
  }
)

const statusClass = (status) => {
  const normalized = String(status || '').toLowerCase()

  if (normalized.includes('medical')) return 'bg-blue-100 text-blue-700'
  if (normalized.includes('police')) return 'bg-indigo-100 text-indigo-700'
  if (normalized.includes('ticket') || normalized.includes('pta')) return 'bg-sky-100 text-sky-700'
  if (normalized.includes('mofa') || normalized.includes('embassy')) return 'bg-violet-100 text-violet-700'
  if (normalized.includes('training')) return 'bg-amber-100 text-amber-700'
  if (normalized.includes('visa')) return 'bg-emerald-100 text-emerald-700'
  if (normalized.includes('manpower') || normalized.includes('bmet')) return 'bg-orange-100 text-orange-700'

  return 'bg-gray-100 text-gray-700'
}
</script>
