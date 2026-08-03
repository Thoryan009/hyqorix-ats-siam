<template>
  <BaseModal
    :isVisible="store.isModal"
    :title="t('application.add')"
    @close="handleAddModal"
    :className="'w-full xl:max-w-[95vw] h-[95vh] '"
  >
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- SCROLL WRAPPER -->
      <div class="h-150 px-2 py-4 space-y-6">
        <PassportDetailsSection />
        <PersonalInfoSection
          :isEditMode="false"
          :subjects="subjects"
          :qualifications="qualifications"
        />
        <!-- <ContactInfoSection /> -->
        <EducationExperienceSection :subjects="subjects" :qualifications="qualifications" />
        <!-- <ResumeDetailsSection /> -->
        <SingleDocumentSection />
        <MergableDocumentsSection />
        <OtherDetailsSection />
        <ApplicationDetailsSection :jobs="jobs" :agents="agents" />
        <LinksSection />

        <div
          v-if="formError"
          class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-2">
              <i class="fa fa-exclamation-triangle mt-0.5"></i>
              <span>{{ formError }}</span>
            </div>
            <button type="button" class="text-red-500 hover:text-red-700" @click="formError = ''">
              <i class="fa fa-times"></i>
            </button>
          </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="flex justify-end gap-2 pt-4 pb-8">
          <BaseButton
            class="bg-yellow-600 hover:bg-yellow-700"
            type="button"
            @click="handleAddModal"
          >{{ t('shared.actions.cancel') }}</BaseButton>
          <BaseButton type="submit" :disabled="submitLoading">
            <span v-if="submitLoading">{{ t('shared.messages.saving') }}</span>
            <span v-else>{{ t('shared.actions.save') }}</span>
          </BaseButton>
        </div>
      </div>
    </form>
  </BaseModal>
</template>

<script setup>
import { inject, ref } from 'vue'
import { useApplicationStore } from '@/modules/application/store/applicationStore'
import {
  useApplicationMutations,
  getMutationErrorMessage,
  buildApplicationPageMessage,
} from '../../queries/useApplicationMutations'

// Import reusable sections
import PersonalInfoSection from './formParts/PersonalInfoSection.vue'
import EducationExperienceSection from './formParts/EducationExperienceSection.vue'
import PassportDetailsSection from './formParts/PassportDetailsSection.vue'
import ResumeDetailsSection from './formParts/ResumeDetailsSection.vue'
import ApplicationDetailsSection from './formParts/ApplicationDetailsSection.vue'
import OtherDetailsSection from './formParts/OtherDetailsSection.vue'
import LinksSection from './formParts/LinksSection.vue'
import SingleDocumentSection from './formParts/SingleDocumentSection.vue'
import MergableDocumentsSection from './formParts/MergableDocumentsSection.vue'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useApplicationStore()
const pageFeedback = inject('applicationPageFeedback', null)
const formError = ref('')
defineProps({
  jobs: {
    type: Array,
    default: () => [],
  },
  agents: {
    type: Array,
    default: () => [],
  },
  subjects: {
    type: Array,
    default: () => [],
  },
  qualifications: {
    type: Array,
    default: () => [],
  },
})

const { submit, submitLoading } = useApplicationMutations(store.moduleName, {
  showToast: false,
  onSuccess() {
    formError.value = ''
    pageFeedback?.setPageMessage(
      'success',
      buildApplicationPageMessage('created', store.formData),
    )
    store.handleToggleModal()
    store.handleReset(store.formData)
  },
  onError(error) {
    const message = getMutationErrorMessage(error)
    formError.value = message
    pageFeedback?.setPageMessage(
      'error',
      buildApplicationPageMessage('create', store.formData, message),
    )
  },
})

const handleSubmit = async () => {
  formError.value = ''
  const payload = new FormData()

  for (const key in store.formData) {
    const value = store.formData[key]
    if (value === null || value === undefined || value === '') {
      continue
    }
    if (
      value.nid_preview ||
      value.passport_preview ||
      value.resume_preview ||
      value.documents_preview ||
      value.offer_letter_preview ||
      value.acknowledgment_preview ||
      value.education_preview ||
      value.training_preview ||
      value.experience_preview ||
      value.driving_license_preview ||
      value.passport_pdf_preview ||
      value.single_document_preview
    ) {
      continue
    }

    // Handle experiences array with FormData array notation
    if (key === 'experiences' && Array.isArray(value)) {
      if (value.length === 0) {
        // Skip empty experiences array
        continue
      }
      value.forEach((exp, expIndex) => {
        payload.append(`experiences[${expIndex}][company_name]`, exp.company_name || '')
        payload.append(`experiences[${expIndex}][position]`, exp.position || '')
        payload.append(`experiences[${expIndex}][from_date]`, exp.from_date || '')
        payload.append(`experiences[${expIndex}][to_date]`, exp.to_date || '')
        payload.append(`experiences[${expIndex}][is_current]`, exp.is_current ? 1 : 0)
        payload.append(`experiences[${expIndex}][types]`, exp.types || '')

        // Add responsibilities array
        if (Array.isArray(exp.responsibilities)) {
          exp.responsibilities.forEach((resp, respIndex) => {
            if (resp && resp.trim()) {
              payload.append(`experiences[${expIndex}][responsibilities][${respIndex}]`, resp)
            }
          })
        }
      })
      continue
    }

    if (key === 'payment_responsibility' && Array.isArray(value)) {
      payload.append(key, JSON.stringify(value))
      continue
    }

    if (key === 'applied_through' && value === 'direct_candidate') {
      payload.append('applied_through', value)
      payload.append('agent_id', '')
      continue
    }

    payload.append(key, value)
  }

  try {
    await submit.mutateAsync(payload)
  } catch {
    // Error is handled in onError and shown in the modal.
  }
}

const handleAddModal = () => {
  formError.value = ''
  store.handleToggleModal()
  store.handleReset(store.formData) // reset modal
}
</script>
