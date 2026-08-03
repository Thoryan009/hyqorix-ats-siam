<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="t('embassy.edit_title')"
    @close="store.handleToggleModal"
    :className="'max-w-[55vw] xl:max-w-[55vw] h-[80vh]'"
  >
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div class="px-2 py-4 space-y-6">
        <!-- Top: Application Summary -->
        <div class="flex items-center gap-4 p-4 rounded-lg shadow-sm bg-green-200">
          <div
            class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden"
          >
            <img
              v-if="store.item.worker_image_url"
              :src="store.item.worker_image_url"
              class="w-full h-full object-cover"
            />
            <i v-else class="fa fa-user text-gray-400 text-2xl"></i>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-bold text-gray-800">{{ store.item.full_name }}</h3>
            <p class="text-sm text-gray-600">
              {{t('shared.labels.job')}}: <span class="font-medium">{{ store.item.job }}</span>
            </p>
            <p class="text-sm text-gray-600">
              {{t('shared.labels.passport_no')}}: <span class="font-medium">{{ store.item.passport_no || 'N/A' }}</span>
            </p>
            <p class="text-sm text-gray-600">
              {{t('application.nationality')}}: <span class="font-medium">{{ store.item.nationality || 'N/A' }}</span>
            </p>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-500">Applied ID</p>
            <p class="text-sm font-semibold text-gray-800">
              {{ store.item.application_id || store.item.id }}
            </p>
          </div>
        </div>

        <!-- Form fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium text-gray-700 mb-2 block"> {{t('embassy.religion')}} </label>

            <div class="grid grid-cols-2 gap-3">
              <!-- Muslim -->
              <label
                class="cursor-pointer rounded-xl border p-4 transition-all duration-200"
                :class="
                  formData.religion === 'muslim'
                    ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-200'
                    : 'border-gray-200 hover:border-indigo-300'
                "
              >
                <input v-model="formData.religion" type="radio" value="muslim" class="hidden" />

                <div class="flex items-center justify-between">
                  <div>
                    <p class="font-semibold text-gray-800">{{t('embassy.muslim')}}</p>
                    <p class="text-xs text-gray-500">{{t('embassy.islam_religion')}}</p>
                  </div>

                  <div
                    class="w-5 h-5 rounded-full border flex items-center justify-center"
                    :class="
                      formData.religion === 'muslim' ? 'border-indigo-600' : 'border-gray-300'
                    "
                  >
                    <div
                      v-if="formData.religion === 'muslim'"
                      class="w-2.5 h-2.5 rounded-full bg-indigo-600"
                    ></div>
                  </div>
                </div>
              </label>

              <!-- Non Muslim -->
              <label
                class="cursor-pointer rounded-xl border p-4 transition-all duration-200"
                :class="
                  formData.religion === 'non-muslim'
                    ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-200'
                    : 'border-gray-200 hover:border-indigo-300'
                "
              >
                <input v-model="formData.religion" type="radio" value="non-muslim" class="hidden" />

                <div class="flex items-center justify-between">
                  <div>
                    <p class="font-semibold text-gray-800">{{t('embassy.non_muslim')}}</p>
                    <p class="text-xs text-gray-500">{{t('embassy.other_religion')}}</p>
                  </div>

                  <div
                    class="w-5 h-5 rounded-full border flex items-center justify-center"
                    :class="
                      formData.religion === 'non-muslim' ? 'border-indigo-600' : 'border-gray-300'
                    "
                  >
                    <div
                      v-if="formData.religion === 'non-muslim'"
                      class="w-2.5 h-2.5 rounded-full bg-indigo-600"
                    ></div>
                  </div>
                </div>
              </label>
            </div>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">{{t('embassy.visa_profession_arabic')}}</label>
            <input
              v-model="formData.visa_profession_ar"
              type="text"
              class="mt-1 block w-full border rounded-md p-2"
              :placeholder="t('embassy.arabic_profession')"
              @blur="handleVisaProfessionArBlur"
            />
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">{{t('embassy.visa_profession_english')}}</label>
            <input
              v-model="formData.visa_profession_en"
              type="text"
              class="mt-1 block w-full border rounded-md p-2"
              :placeholder="
                isTranslatingVisaProfession
                  ? t('embassy.generating_english_profession')
                  : t('embassy.english_profession')
              "
              :disabled="isTranslatingVisaProfession"
              @input="handleVisaProfessionEnInput"
            />
            <p v-if="isTranslatingVisaProfession" class="mt-1 text-xs text-indigo-600">
              {{t('embassy.generating_english_profession')}}
            </p>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">{{t('embassy.visit_work_for_arabic')}}</label>
            <input
              v-model="formData.visit_work_for_ar"
              type="text"
              class="mt-1 block w-full border rounded-md p-2"
              :placeholder="t('embassy.arabic_text')"
            />
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">{{t('embassy.mofa_no')}}</label>
            <input
              v-model="formData.mofa_no"
              type="text"
              class="mt-1 block w-full border rounded-md p-2"
              :placeholder="t('embassy.mofa_no')"
            />
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">{{t('embassy.police_clearance_no')}}</label>
            <input
              v-model="formData.police_clearance_no"
              type="text"
              class="mt-1 block w-full border rounded-md p-2"
              :placeholder="t('embassy.police_clearance_no')"
            />
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">{{t('embassy.alwakala_no')}}</label>
            <input
              v-model="formData.alwakala_no"
              type="text"
              class="mt-1 block w-full border rounded-md p-2"
              :placeholder="t('embassy.alwakala_no')"
            />
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-2 pt-4">
          <BaseButton type="button" class="bg-gray-200" @click="store.handleToggleModal"
            >{{t('shared.actions.cancel')}}</BaseButton
          >
          <BaseButton
            type="submit"
            :disabled="updateEmbassySubmissionLoading"
            class="bg-indigo-600 text-white"
          >
            <span v-if="updateEmbassySubmissionLoading">{{t('shared.messages.saving')}}</span>
            <span v-else>{{t('shared.actions.save')}}</span>
          </BaseButton>
        </div>
      </div>
    </form>
  </BaseModal>
</template>

<script setup>
import { watch, ref } from 'vue'
import { useApplicationMutations } from '../../queries/useApplicationMutations'
import { useApplicationStore } from '../../store/applicationStore'
import { generateVisaProfessionEnglishData } from '../../services/applicationService'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()

const store = useApplicationStore()

const formData = ref({
  application_id: null,
  religion: null,
  visa_profession_ar: '',
  visa_profession_en: '',
  visit_work_for_ar: '',
  mofa_no: '',
  police_clearance_no: '',
  alwakala_no: '',
})

const embasyId = ref(null)
const visaProfessionEnglishManual = ref(false)
const isTranslatingVisaProfession = ref(false)

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

const { updateEmbassySubmission, updateEmbassySubmissionLoading } = useApplicationMutations(
  store.moduleName,
  {
    onSuccess() {
      store.handleToggleModal()
      store.handleReset(store.formData) // reset modal
    },
    onError: (error) => {
      console.log('Custom error handling', error)
    },
  },
)

// Prefill when modal opens or selected row changes
watch(
  () => store.item,
  (val) => {
    if (!val) return
    formData.value.application_id = val.id
    const emb = val.embassy_submission
    if (emb) {
      embasyId.value = emb.id
      formData.value.religion = emb.religion || 'muslim'
      formData.value.visa_profession_ar = emb.visa_profession_ar || ''
      formData.value.visa_profession_en = emb.visa_profession_en || ''
      formData.value.visit_work_for_ar = emb.visit_work_for_ar || ''
      formData.value.mofa_no = emb.mofa_no || ''
      formData.value.police_clearance_no = emb.police_clearance_no || ''
      formData.value.alwakala_no = emb.alwakala_no || ''
      visaProfessionEnglishManual.value = false
    } else {
      embasyId.value = null
      formData.value.religion = ''
      formData.value.visa_profession_ar = ''
      formData.value.visa_profession_en = ''
      formData.value.visit_work_for_ar = ''
      formData.value.mofa_no = ''
      formData.value.police_clearance_no = ''
      formData.value.alwakala_no = ''
      visaProfessionEnglishManual.value = false
    }
  },
  {
    immediate: true,
  },
)

const handleVisaProfessionEnInput = () => {
  visaProfessionEnglishManual.value = Boolean(formData.value.visa_profession_en?.trim())
}

const handleVisaProfessionArBlur = async () => {
  const arabicProfession = formData.value.visa_profession_ar?.trim()

  if (!arabicProfession) {
    return
  }

  if (visaProfessionEnglishManual.value && formData.value.visa_profession_en?.trim()) {
    return
  }

  isTranslatingVisaProfession.value = true

  try {
    const response = await generateVisaProfessionEnglishData({
      visa_profession_ar: arabicProfession,
    })

    const translatedProfession = response?.data?.visa_profession_en?.trim()

    if (translatedProfession) {
      formData.value.visa_profession_en = translatedProfession
      visaProfessionEnglishManual.value = false
    }
  } catch (error) {
    console.log('Visa profession translation failed', error)
  } finally {
    isTranslatingVisaProfession.value = false
  }
}

const handleSubmit = async () => {
  await updateEmbassySubmission.mutateAsync({
    id: formData.value.application_id,
    data: formData.value,
  })
}
</script>
