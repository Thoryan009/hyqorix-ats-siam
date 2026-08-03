<template>
  <div class="min-h-screen bg-linear-to-br from-gray-50 to-gray-100">
    <QuickSearchCandidatePanel
      v-if="quickSearchCandidate"
      :candidate="quickSearchCandidate"
    />

    <JobCard :jobs="jobs" :job="job" v-model="jobId" />

    <ApplicationProcess
      :processes="jobAts[0]?.all_processes"
      v-model:selectedProcessId="selectedProcessId"
      :job="job"
      @handleSelectProcess="handleSelectProcess"
      :jobAts="jobAts"
    />

    <!-- Grid Layout: Applicant List & Right Side Forms -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Left Applicant List -->
      <div class="col-span-12 md:col-span-4">
        <ApplicantList
          :applicants="jobAts[0]?.applications"
          :selectedApplicant="selectedApplicant"
          :selectedProcessId="selectedProcessId"
          @toggleApplicantSelection="toggleApplicantSelection"
          :selected-ids="selectedIds"
          @toggleAll="(checked, list) => toggleAll(list, checked)"
          @toggleRow="toggleRow"
          @clearSelection="clearSelection"
        />
      </div>
      <!-- Right Side: Process List and Forms -->
      <div class="col-span-12 md:col-span-8">
        <div v-if="selectedApplicant" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <!-- Process List -->
          <div class="lg:col-span-5">
            <ProcessList
              :processes="selectedApplicant.processes"
              :selectedProcessDetails="selectedProcessDetails"
              @selectProcess="handleSelectProcessDetails"
              v-if="selectedApplicant && selectedIds.length === 0"
            />
          </div>

          <!-- Forms -->

          <div
            class="lg:col-span-7 bg-white rounded-lg shadow-md p-6 max-h-fit overflow-y-auto pr-2 form-scroller"
            v-if="selectedApplicant && selectedIds.length === 0"
          >
            <!-- Rejected Applicant Notice -->
            <div
              v-if="selectedApplicant.current_process == 'rejected'"
              class="text-red-600 font-semibold text-lg mb-4"
            >
              <p>{{ t('ats.applicant_has_been_rejected') }}</p>
            </div>

            <!-- Document and Offer Letters in Ats -->

            <div class="flex justify-between items-center">
              <BaseButton
                v-if="
                  selectedApplicant.processes.length > 0 &&
                  latestProcess?.process_id === selectedProcessDetails?.process_id
                "
                v-can="'application_process.delete'"
                @click="handleDeleteLastProcess"
                :disabled="deleteProcessLoading"
                class="bg-red-600 text-white hover:bg-red-700"
              >{{ deleteProcessLoading ? t('shared.messages.deleting') : t('ats.delete_process') }}</BaseButton>

              <div class="flex items-center gap-2 justify-end">
                <div v-if="role === 'super_admin' || role === 'admin' || role === 'recruiter'">
                  <span
                    v-if="selectedApplicant?.latest_transaction?.status"
                    class="px-3 py-1 rounded-full text-sm font-semibold"
                    :class="getStatusClass(selectedApplicant.latest_transaction.status)"
                  >{{ formatStatus(selectedApplicant.latest_transaction.status) }}</span>
                </div>
                <!-- View Documents Button -->
                <BaseButton @click="handleClick">{{ t('ats.view_documents') }}</BaseButton>
              </div>
            </div>

            <!-- Process Forms -->
            <div v-if="!selectedProcessDetails" class="text-center py-12 text-gray-500">
              <svg
                class="mx-auto h-12 w-12 text-gray-400 mb-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5l7 7-7 7"
                />
              </svg>
              <p class="text-lg">{{ t('ats.select_a_process_to_manage') }}</p>
            </div>

            <div v-else>
              <!-- All Process Forms -->

              <OfferExtendedForm
                v-if="getProcessName(selectedProcessDetails.process_id) === 'offer_extended'"
                :selectedApplicant="selectedApplicant"
                @updateProcess="updateProcess"
                @nextProcess="nextProcess"
              />

              <VisaAuthorizationForm
                v-if="getProcessName(selectedProcessDetails.process_id) === 'visa_authorization'"
                :selectedApplicant="selectedApplicant"
                @updateProcess="updateProcess"
                @nextProcess="nextProcess"
              />

              <MedicalTestForm
                v-if="getProcessName(selectedProcessDetails.process_id) === 'medical_test'"
                :selectedApplicant="selectedApplicant"
                @updateProcess="updateProcess"
                @nextProcess="nextProcess"
              />

              <PoliceClearanceForm
                v-if="getProcessName(selectedProcessDetails.process_id) === 'police_clearance'"
                :selectedApplicant="selectedApplicant"
                @updateProcess="updateProcess"
                @nextProcess="nextProcess"
              />

              <TradeTestForm
                v-if="getProcessName(selectedProcessDetails.process_id) === 'trade_test'"
                :selectedApplicant="selectedApplicant"
                @updateProcess="updateProcess"
                @nextProcess="nextProcess"
              />

              <BiometricEnrollmentForm
                v-if="getProcessName(selectedProcessDetails.process_id) === 'biometric_enrollm'"
                :selectedApplicant="selectedApplicant"
                @updateProcess="updateProcess"
                @nextProcess="nextProcess"
              />

              <EmbassySubmissionForm
                v-if="getProcessName(selectedProcessDetails.process_id) === 'embassy_submission'"
                :selectedApplicant="selectedApplicant"
                @updateProcess="updateProcess"
                @nextProcess="nextProcess"
              />

              <BmetTrainingForm
                v-if="getProcessName(selectedProcessDetails.process_id) === 'bmet_training'"
                :selectedApplicant="selectedApplicant"
                @updateProcess="updateProcess"
                @nextProcess="nextProcess"
              />

              <BmetBiometricEnrollmentForm
                v-if="
                  getProcessName(selectedProcessDetails.process_id) === 'bmet_biometric_enrollm'
                "
                :selectedApplicant="selectedApplicant"
                @updateProcess="updateProcess"
                @nextProcess="nextProcess"
              />

              <ImmigrationClearanceForm
                v-if="getProcessName(selectedProcessDetails.process_id) === 'immigration_clearance'"
                :selectedApplicant="selectedApplicant"
                @updateProcess="updateProcess"
                @nextProcess="nextProcess"
              />

              <PtaRequestForm
                v-if="getProcessName(selectedProcessDetails.process_id) === 'pta_request'"
                :selectedApplicant="selectedApplicant"
                @updateProcess="updateProcess"
                @nextProcess="nextProcess"
              />

              <TraProcessForm
                v-if="getProcessName(selectedProcessDetails.process_id) === 'tra_process'"
                :selectedApplicant="selectedApplicant"
                @updateProcess="updateProcess"
                @nextProcess="nextProcess"
              />

              <OnBoardingForm
                v-if="getProcessName(selectedProcessDetails.process_id) === 'on_boarding'"
                :selectedApplicant="selectedApplicant"
                @updateProcess="updateProcess"
                @nextProcess="nextProcess"
              />
            </div>
          </div>
        </div>

        <div v-else class="bg-white rounded-lg shadow-md p-6">
          <div class="text-center py-12 text-gray-500">
            <svg
              class="mx-auto h-16 w-16 text-gray-400 mb-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
              />
            </svg>
            <p class="text-lg">{{ t('ats.select_applicant_to_manage') }}</p>
          </div>
        </div>
      </div>
    </div>
    <!-- Documents Right-Slide-over Modal -->
    <DocumentsModal v-model="showDocs" :documents="applicantDocuments" title="Applicant Documents" />
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'

// Newly created split components
import ApplicationProcess from './jobAtsParts/ApplicationProcess.vue'
import ApplicantList from './jobAtsParts/ApplicantList.vue'
import ProcessList from './jobAtsParts/ProcessList.vue'

// Process Form Components
import OfferExtendedForm from './jobAtsParts/OfferExtendedForm.vue'
import VisaAuthorizationForm from './jobAtsParts/VisaAuthorizationForm.vue'
import TraProcessForm from './jobAtsParts/TraProcessForm.vue'
import ImmigrationClearanceForm from './jobAtsParts/ImmigrationClearanceForm.vue'
import MedicalTestForm from './jobAtsParts/MedicalTestForm.vue'
import PoliceClearanceForm from './jobAtsParts/PoliceClearanceForm.vue'
import TradeTestForm from './jobAtsParts/TradeTestForm.vue'
import BiometricEnrollmentForm from './jobAtsParts/BiometricEnrollmentForm.vue'
import EmbassySubmissionForm from './jobAtsParts/EmbassySubmissionForm.vue'
import BmetTrainingForm from './jobAtsParts/BmetTrainingForm.vue'
import BmetBiometricEnrollmentForm from './jobAtsParts/BmetBiometricEnrollmentForm.vue'
import PtaRequestForm from './jobAtsParts/PtaRequestForm.vue'
import OnBoardingForm from './jobAtsParts/OnBoardingForm.vue'
import { useAtsDataQuery } from '../queries/useAtsQuery'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'

import JobCard from './jobAtsParts/JobCard.vue'
import QuickSearchCandidatePanel from './jobAtsParts/QuickSearchCandidatePanel.vue'

import { useSingleAtsQuery } from '../queries/useAtsQuery'
import { useAtsMutations } from '../queries/useAtsMutations'
import { useAtsStore } from '../store/atsStore'
import BaseButton from '@/shared/components/base/BaseButton.vue'
import DocumentsModal from './jobAtsParts/DocumentsModal.vue'
import { useTranslate } from '@/shared/composables/useTranslate.js'

const { t } = useTranslate()
const { selectedIds, toggleAll, toggleRow, clearSelection } = useBulkDelete()

// Document Modal State
const showDocs = ref(false)
const handleClick = () => {
  console.log('clicked')
  showDocs.value = true
}

const applicantDocuments = computed(() => {
  if (!selectedApplicant.value) return []
  console.log('Selected Applicant for Documents:', selectedApplicant.value) // Debug log to check selected applicant data

  const docs = selectedApplicant.value

  console.log('Applicant Documents:', docs) // Debug log to check document data

  return [
    { label: 'NID', link: docs.nid_link || docs.nid_url },
    { label: 'Passport', link: docs.passport_url || docs.passport_link },
    { label: 'Documents', link: docs.documents_link || docs.documents_url },
    { label: 'Offer Letter', link: docs.offer_letter_link || docs.offer_letter_url },
    { label: 'SVP', link: docs.svp_link || docs.svp_url },
    { label: 'QVP', link: docs.qvp_link || docs.qvp_url },
    { label: 'Acknowledgment', link: docs.acknowledgment_link || docs.acknowledgment_url },
    {
      label: 'Immigration Clearance',
      link: docs.immigration_clearance_link || docs.immigration_clearance_url,
    },
    { label: 'Visa Copy', link: docs.visa_copy_link || docs.visa_copy_url },
    { label: 'Ticket', link: docs.ticket_link || docs.ticket_url },
  ].filter((doc) => doc.link)
})

// Route
const route = useRoute()

const jobId = ref(route.params.id || null)
const store = useAtsStore()
const { data: atsData } = useAtsDataQuery()
const jobs = computed(() => atsData.value?.data?.data.job_lists ?? [])

// Check if current process is the one with highest process_id (last added)
const latestProcess = computed(() => {
  const processes = selectedApplicant.value?.processes || []
  if (processes.length === 0) return null

  return processes.reduce((max, current) => (current.process_id > max.process_id ? current : max))
})

// Selected process state
const selectedProcessId = ref(Number(route.query.process_id) || 1)

watch(
  () => route.params.id,
  (id) => {
    jobId.value = id || null
  },
)

watch(
  () => route.query.process_id,
  (processId) => {
    if (processId) {
      selectedProcessId.value = Number(processId)
    }
  },
)

const handleSelectProcess = (processId) => {
  selectedProcessId.value = processId
  selectedApplicant.value = null
  clearSelection()
}

// Load job ATS data
const pendingApplicationId = computed(() => route.query.application_id || null)
const quickSearchCandidate = ref(null)

const loadQuickSearchCandidate = () => {
  if (route.query.from_quick_search !== '1' || !route.query.application_id) {
    quickSearchCandidate.value = null
    return
  }

  try {
    const stored = JSON.parse(sessionStorage.getItem('ats_quick_search_candidate') || 'null')

    if (stored && String(stored.application_id) === String(route.query.application_id)) {
      quickSearchCandidate.value = stored
      return
    }
  } catch {
    quickSearchCandidate.value = null
    return
  }

  quickSearchCandidate.value = null
}

watch(() => route.fullPath, loadQuickSearchCandidate, { immediate: true })

const { data } = useSingleAtsQuery(jobId, selectedProcessId, pendingApplicationId)
const jobAts = computed(() => data.value?.data?.data ?? [])
//Top card for job title,name,experience....etc
const job = computed(() => {
  return Array.isArray(jobAts.value) && jobAts.value.length ? jobAts.value[0] : {}
})

watch(
  job,
  (newJob) => {
    store.setAllProcesses(newJob.all_processes)
  },
  { immediate: true }
)

// Selected applicant state
const selectedApplicant = ref(null)
const selectedProcessDetails = ref(null)

const toggleApplicantSelection = (applicant) => {
  if (selectedApplicant.value && selectedApplicant.value.id == applicant.id) {
    selectedApplicant.value = null
    selectedProcessDetails.value = null
    return
  }
  selectedApplicant.value = applicant
  // Auto-select the first process (highest process_id = most recent)
  if (applicant.processes && applicant.processes.length > 0) {
    const sortedProcesses = [...applicant.processes].sort((a, b) => b.process_id - a.process_id)
    selectedProcessDetails.value = sortedProcesses[0]
  } else {
    selectedProcessDetails.value = null
  }
}

const selectApplicantFromQuery = () => {
  const applicationId = pendingApplicationId.value
  if (!applicationId || !jobAts.value?.[0]?.applications?.length) return

  const applicant = jobAts.value[0].applications.find(
    (item) => String(item.id) === String(applicationId),
  )

  if (!applicant || selectedApplicant.value?.id == applicant.id) return

  selectedApplicant.value = applicant

  if (applicant.processes?.length) {
    const sortedProcesses = [...applicant.processes].sort((a, b) => b.process_id - a.process_id)
    selectedProcessDetails.value = sortedProcesses[0]
  } else {
    selectedProcessDetails.value = null
  }
}

watch(jobAts, selectApplicantFromQuery, { immediate: true })
watch(pendingApplicationId, selectApplicantFromQuery)

const handleSelectProcessDetails = (process) => {
  selectedProcessDetails.value = process
}

const getProcessName = (processId) => {
  // return processMap[processId] || null
  return store.allProcesses.find((p) => p.id === processId)?.name || null
}

// Mutations
const { updateFormProcess, createNextProcess, deleteProcess, deleteProcessLoading } =
  useAtsMutations(store.moduleName, {
    onSuccess() {
      selectedApplicant.value = null
    },
    onError(error) {
      console.log('Custom error handling', error)
    },
  })

const handleDeleteLastProcess = async () => {
  if (confirm('Are you sure you want to delete this process?')) {
    await deleteProcess.mutateAsync(selectedProcessDetails.value.id)
  }
}

// Update existing process
const updateProcess = async (processForm) => {
  await updateFormProcess.mutateAsync(processForm)
}

// Move to next process
const nextProcess = async ({ selectedNextProcessId, processData }) => {
  await createNextProcess.mutateAsync({
    applicationId: selectedApplicant.value.id,
    nextProcessId: selectedNextProcessId,
    processData: processData,
  })
}

// for transaction status
const getStatusClass = (status) => {
  switch (status) {
    case 'draft':
      return 'bg-gray-100 text-gray-600'

    case 'due':
      return 'bg-orange-100 text-orange-700'

    case 'bill-generated':
      return 'bg-yellow-100 text-yellow-700'

    case 'invoice-generated':
      return 'bg-indigo-100 text-indigo-700'

    case 'invoice-sent':
      return 'bg-blue-100 text-blue-700'

    case 'paid':
      return 'bg-green-100 text-green-700'

    case 'cancelled':
      return 'bg-red-100 text-red-700'

    default:
      return 'bg-gray-100 text-gray-500'
  }
}

const formatStatus = (status) => {
  return status.replace('-', ' ').toUpperCase()
}
</script>

<style scoped>
.form-scroller::-webkit-scrollbar {
  width: 5px;
}

.form-scroller::-webkit-scrollbar-track {
  background: transparent;
  border-radius: 10px;
}

.form-scroller::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #e2e8f0, #cbd5e1);
  border-radius: 10px;
  transition: all 0.3s ease;
}

.form-scroller::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #cbd5e1, #94a3b8);
  box-shadow: 0 0 6px rgba(148, 163, 184, 0.4);
}

.form-scroller {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 transparent;
}
</style>
