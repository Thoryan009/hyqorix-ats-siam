<template>
  <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
    <div class="flex items-center gap-3 pb-3 mb-2 border-b-2 border-violet-500">
      <span
        class="flex items-center justify-center w-8 h-8 rounded-full bg-violet-500 text-white text-sm font-bold shrink-0"
      >3</span>
      <i class="fa fa-graduation-cap text-violet-500 text-xl"></i>
      <span class="font-semibold text-lg text-gray-800">{{t('application.education_experience')}}</span>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-4">
      <div
        class="col-span-1 md:col-span-4 flex items-center gap-2 pt-2 pb-2 border-b border-violet-200"
      >
        <i class="fa fa-suitcase text-violet-400 text-sm"></i>
        <span class="font-medium text-gray-700">{{t('application.experience')}}</span>
      </div>

      <div>
        <BaseLabel for="bd_exp">{{t('application.bd_experience')}} ({{t('application.optional')}})</BaseLabel>
        <BaseSelect
          id="bd_exp"
          v-model="store.formData.bd_exp"
          :options="experienceOptions"
          :placeholder="t('shared.placeholders.select')"
        />
      </div>

      <div>
        <BaseLabel for="overseas_exp">{{t('application.overseas_experience')}} ({{t('application.optional')}})</BaseLabel>
        <BaseSelect
          id="overseas_exp"
          v-model="store.formData.overseas_exp"
          :options="experienceOptions"
          :placeholder="t('shared.placeholders.select')"
        />
      </div>

      <div class="col-span-1 md:col-span-3">
        <button
          type="button"
          @click="addExperience"
          class="text-sm px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark transition"
        >+ {{t('application.add_experience')}}</button>
      </div>

      <!-- Experience entries -->
      <div
        v-for="(experience, index) in store.formData.experiences"
        :key="index"
        class="col-span-1 md:col-span-3 p-4 border border-gray-200 rounded-lg bg-gray-50/50 relative"
      >
        <button
          type="button"
          @click="removeExperience(index)"
          class="absolute top-2 right-2 text-red-600 hover:text-red-800 font-bold text-xl"
          title="Remove Experience"
        >&times;</button>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <BaseLabel :for="`exp_type_${index}`">{{t('application.experience_type')}}</BaseLabel>
            <BaseSelect
              :id="`exp_type_${index}`"
              v-model="experience.types"
              :options="experienceTypeOptions"
              placeholder="Select Type"
            />
          </div>

          <div>
            <BaseLabel :for="`company_name_${index}`">{{t('application.company_name')}}</BaseLabel>
            <input
              :id="`company_name_${index}`"
              v-model="experience.company_name"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary"
              :placeholder="t('application.company_name_placeholder')"
            />
          </div>

          <div>
            <BaseLabel :for="`position_${index}`">{{t('application.position')}}</BaseLabel>
            <input
              :id="`position_${index}`"
              v-model="experience.position"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary"
              :placeholder="t('application.position_placeholder')"
            />
          </div>

          <div>
            <BaseLabel :for="`from_date_${index}`">{{t('application.from_date')}}</BaseLabel>
            <input
              :id="`from_date_${index}`"
              v-model="experience.from_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary"
            />
          </div>

          <div>
            <BaseLabel :for="`to_date_${index}`">{{t('application.to_date')}}</BaseLabel>
            <input
              :id="`to_date_${index}`"
              v-model="experience.to_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary"
              :disabled="experience.is_current"
            />
          </div>

          <div class="flex items-center pt-6">
            <input
              :id="`is_current_${index}`"
              v-model="experience.is_current"
              type="checkbox"
              class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-2 focus:ring-primary"
              @change="handleCurrentChange(index)"
            />
            <BaseLabel :for="`is_current_${index}`" class="ml-2 mb-0">{{t('application.currently_working')}}</BaseLabel>
          </div>

          <div class="md:col-span-3">
            <div class="flex mb-4 flex-col gap-1 mb-2">
              <BaseLabel>{{t('application.responsibilities')}}</BaseLabel>
              <button
                type="button"
                @click="addResponsibility(index)"
                class="text-sm px-3 py-1 w-fit bg-green-600 text-white rounded hover:bg-green-700 transition"
              >+ {{t('application.add_responsibility')}}</button>
            </div>

            <div class="space-y-2">
              <div
                v-for="(responsibility, respIndex) in experience.responsibilities"
                :key="respIndex"
                class="flex gap-2"
              >
                <input
                  :id="`responsibility_${index}_${respIndex}`"
                  v-model="experience.responsibilities[respIndex]"
                  type="text"
                  maxlength="100"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary"
                  :placeholder="t('application.responsibility_placeholder', { number: respIndex + 1 })"
                />
                <button
                  type="button"
                  @click="removeResponsibility(index, respIndex)"
                  class="px-3 py-2 text-red-600 hover:text-red-800 font-bold"
                  :title="t('application.remove_responsibility')"
                >&times;</button>
              </div>

              <div
                v-if="!experience.responsibilities || experience.responsibilities.length === 0"
                class="text-sm text-gray-500 text-center py-2"
              >{{t('application.no_responsibilities')}} </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state message -->
      <!-- <div
        v-if="!store.formData.experiences || store.formData.experiences.length === 0"
        class="col-span-1 md:col-span-3 text-center py-8 text-gray-500"
      >
        No experience added yet. Click "Add Experience" to add one.
      </div>-->
    </div>
  </div>
</template>

<script setup>
import { experienceOptions } from '@/modules/application/data/applicationData.js'
import { useApplicationStore } from '@/modules/application/store/applicationStore'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useApplicationStore()

defineProps({
  subjects: {
    type: Array,
    default: () => [],
  },
  qualifications: {
    type: Array,
    default: () => [],
  },
})

const experienceTypeOptions = [
  {
    id: 'bd_exp',
    name: 'BD Experience',
  },
  {
    id: 'overseas_exp',
    name: 'Overseas Experience',
  },
]

const addExperience = () => {
  if (!store.formData.experiences) {
    store.formData.experiences = []
  }
  store.formData.experiences.push({
    company_name: '',
    position: '',
    from_date: '',
    to_date: '',
    is_current: false,
    responsibilities: [''], // Initialize with one empty responsibility field
    types: '',
  })
}

const removeExperience = (index) => {
  store.formData.experiences.splice(index, 1)
}

const addResponsibility = (expIndex) => {
  if (!store.formData.experiences[expIndex].responsibilities) {
    store.formData.experiences[expIndex].responsibilities = []
  }
  store.formData.experiences[expIndex].responsibilities.push('')
}

const removeResponsibility = (expIndex, respIndex) => {
  store.formData.experiences[expIndex].responsibilities.splice(respIndex, 1)
}

const handleCurrentChange = (index) => {
  if (store.formData.experiences[index].is_current) {
    store.formData.experiences[index].to_date = ''
  }
}
</script>
