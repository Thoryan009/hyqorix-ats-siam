<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="t('application.edit')"
    @close="handleEditModal"
    :className="'max-w-[95vw] xl:max-w-[95vw] h-[95vh] !overflow-hidden flex flex-col'"
  >
    <form @submit.prevent="handleSubmit" class="flex flex-col min-h-0 h-[calc(95vh-5.5rem)]">
      <div class="flex-1 min-h-0 overflow-y-auto custom-scrollbar px-2 py-4 space-y-6">
        <PassportDetailsSection />
        <PersonalInfoSection :subjects="subjects" :qualifications="qualifications" />
        <!-- <ContactInfoSection /> -->
        <EducationExperienceSection :subjects="subjects" :qualifications="qualifications" />
        <!-- <ResumeDetailsSection /> -->
        <OtherDetailsSection />
        <MergableDocumentsSection />
        <SingleDocumentSection />
        <ApplicationDetailsSection :jobs="jobs" :agents="agents" :isEditMode="true" />
        <LinksSection />
      </div>

      <div class="shrink-0 border-t border-gray-200 bg-white px-2 pt-3 pb-1 space-y-3">
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

        <div class="flex justify-end gap-2">
          <BaseButton
            class="bg-yellow-600 hover:bg-yellow-700"
            type="button"
            @click="handleEditModal"
          >{{ t('shared.actions.cancel') }}</BaseButton>
          <BaseButton type="submit" :disabled="updateLoading">
            <span v-if="updateLoading">{{ t('shared.messages.saving') }}</span>
            <span v-else>{{ t('shared.actions.save') }}</span>
          </BaseButton>
        </div>
      </div>
    </form>
  </BaseModal>
</template>

<script setup>
import { inject, ref, watch } from 'vue'
import {
  useApplicationMutations,
  getMutationErrorMessage,
  buildApplicationPageMessage,
} from '../../queries/useApplicationMutations'
import { useApplicationStore } from '../../store/applicationStore'
import PersonalInfoSection from './formParts/PersonalInfoSection.vue'
import EducationExperienceSection from './formParts/EducationExperienceSection.vue'
import PassportDetailsSection from './formParts/PassportDetailsSection.vue'
// import ResumeDetailsSection from './formParts/ResumeDetailsSection.vue'
import ApplicationDetailsSection from './formParts/ApplicationDetailsSection.vue'
import OtherDetailsSection from './formParts/OtherDetailsSection.vue'
import MergableDocumentsSection from './formParts/MergableDocumentsSection.vue'
import SingleDocumentSection from './formParts/SingleDocumentSection.vue'
import LinksSection from './formParts/LinksSection.vue'
import { normalizeJobPayers } from '@/modules/job/utils/jobPayerUtils'
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
  subjects: {
    type: Array,
    default: () => [],
  },
  qualifications: {
    type: Array,
    default: () => [],
  },
  agents: {
    type: Array,
    default: () => [],
  },
})

watch(
  () => store.item,
  (newItem) => {
    formError.value = ''
    if (!newItem) return
    Object.keys(store.formData).forEach((key) => {
      if (key === 'experiences' && newItem[key]) {
        // Ensure responsibilities is a properly reactive array
        store.formData[key] = newItem[key].map((exp) => ({
          ...exp,
          responsibilities: Array.isArray(exp.responsibilities)
            ? [...exp.responsibilities] // Create a new array for reactivity
            : [],
        }))
      } else if (key === 'payment_responsibility') {
        const payers = normalizeJobPayers(
          newItem.payment_responsibility ?? newItem.payer ?? ['agent'],
        )
        const appliedThrough =
          newItem.applied_through || (newItem.agent_id ? 'agent' : 'direct_candidate')

        if (appliedThrough === 'direct_candidate') {
          const withoutAgent = payers.filter((item) => item !== 'agent')
          store.formData[key] = withoutAgent.length ? withoutAgent : ['candidate']
        } else {
          store.formData[key] = payers
        }
      } else if (key === 'applied_through') {
        store.formData[key] =
          newItem.applied_through ||
          (newItem.agent_id ? 'agent' : 'direct_candidate')
      } else {
        store.formData[key] = newItem[key] ?? store.formData[key]
      }
    })
  },
  { immediate: true, deep: true }
)

const { update, updateLoading } = useApplicationMutations(store.moduleName, {
  showToast: false,
  onSuccess() {
    formError.value = ''
    pageFeedback?.setPageMessage(
      'success',
      buildApplicationPageMessage('updated', store.formData),
    )
    store.handleToggleModal()
    store.handleReset(store.formData)
  },
  onError(error) {
    const message = getMutationErrorMessage(error)
    formError.value = message
    pageFeedback?.setPageMessage(
      'error',
      buildApplicationPageMessage('update', store.formData, message),
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
    if (key.endsWith('_path') && !(value instanceof File)) {
      continue
    }
    if (key === 'nid_preview' || key === 'passport_preview' || key === 'resume_preview') {
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
        if (!exp.is_current) {
          payload.append(`experiences[${expIndex}][to_date]`, exp.to_date || '')
        } else {
          payload.append(`experiences[${expIndex}][to_date]`, '')
        }
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
    await update.mutateAsync({
      id: store.item.id,
      data: payload,
    })
  } catch {
    // Error is handled in onError and shown in the modal.
  }
}

const handleEditModal = () => {
  formError.value = ''
  store.handleToggleModal()
  store.handleReset(store.formData) // reset modal
}
</script>
