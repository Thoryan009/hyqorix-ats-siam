<template>
  <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
    <div class="flex items-center gap-3 pb-3 mb-2 border-b-2 border-blue-500">
      <span
        class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 text-white text-sm font-bold shrink-0"
      >2</span>
      <i class="fa fa-user-o text-blue-500 text-xl"></i>
      <span class="font-semibold text-lg text-gray-800">{{ t('application.personal_information') }}</span>
    </div>

    <div class="flex flex-col md:flex-row gap-6 py-4">
      <!-- LEFT: Image Upload Picker -->
      <div class="flex flex-col items-center gap-2 shrink-0">
        <label for="worker_image_input" class="cursor-pointer group block">
          <div
            class="relative w-48 h-48 rounded-lg border-2 border-dashed border-blue-300 bg-blue-50 hover:border-blue-500 hover:bg-blue-100 transition-all overflow-hidden flex items-center justify-center"
          >
            <!-- New upload preview -->
            <img
              v-if="store.fileType?.worker_image_preview === 'image' && store.formData?.worker_image_preview"
              :src="store.formData.worker_image_preview"
              class="w-full h-full object-cover"
            />
            <!-- Existing DB image -->
            <img
              v-else-if="store.item?.worker_image_url && !store.item.worker_image_url.split('?')[0].toLowerCase().endsWith('.pdf')"
              :src="store.item.worker_image_url"
              class="w-full h-full object-cover"
            />
            <!-- Placeholder -->
            <div v-else class="flex flex-col items-center text-blue-400 gap-1">
              <i class="fa fa-camera text-3xl"></i>
              <span class="text-xs font-medium">{{t('application.upload_photo')}}</span>
            </div>
            <!-- Hover overlay when image present -->
            <div
              v-if="store.formData?.worker_image_preview || store.item?.worker_image_url"
              class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
            >
              <i class="fa fa-pencil text-white text-2xl"></i>
            </div>
          </div>
        </label>
        <input
          id="worker_image_input"
          type="file"
          accept=".jpg,.jpeg,.png"
          class="sr-only"
          @change="store.handleFileChange($event, 'worker_image_path', 'worker_image_preview')"
        />
        <span class="text-xs text-gray-400">{{ t('application.jpg_png_max_400kb') }}</span>
        <button
          v-if="store.formData?.worker_image_preview"
          type="button"
          class="text-xs text-red-500 hover:underline"
          @click.prevent="store.cancelImage('worker_image')"
        >{{ t('application.remove') }}</button>
        <div v-if="store.fileError.worker_image_path" class="text-red-600 text-xs text-center">
          {{ store.fileError.worker_image_path }}
        </div>
      </div>

      <!-- RIGHT: Input Fields -->
      <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <BaseLabel for="email">{{t('shared.labels.email')}} ({{ t('application.optional') }})</BaseLabel>
          <BaseInput
            id="email"
            type="email"
            v-model="store.formData.email"
            placeholder="eg example@mail.com"
          />
        </div>

        <div>
          <BaseLabel for="marital_status">{{ t('application.select_marital_status') }} ({{ t('application.optional') }})</BaseLabel>
          <BaseSelect
            id="marital_status"
            :options="maritalStatusOptions"
            v-model="store.formData.marital_status"
            :placeholder="t('application.select_marital_status')"
          />
        </div>

        <div>
          <BaseLabel for="language">{{t('job.language')}} ({{ t('application.optional') }})</BaseLabel>
          <BaseInput id="language" v-model="store.formData.language" />
        </div>

        <div>
          <BaseLabel for="driving_license_no">{{ t('application.driving_license_number') }} ({{ t('application.optional') }})</BaseLabel>
          <BaseInput
            id="driving_license_no"
            v-model="store.formData.driving_license_no"
            placeholder="eg DL12345678"
          />
        </div>

        <div>
          <BaseLabel for="height">{{ t('application.height_ft') }} ({{ t('application.optional') }})</BaseLabel>
          <BaseInput id="height" v-model="store.formData.height" placeholder="eg 5.8 (ft)" />
        </div>

        <div>
          <BaseLabel for="weight">{{ t('application.weight_kg') }} ({{ t('application.optional') }})</BaseLabel>
          <BaseInput id="weight" v-model="store.formData.weight" placeholder="eg 75 (kg)" />
        </div>
      </div>
    </div>

    <div class="py-5" v-if="isEditMode">
      <BaseLabel for="summary">{{ t('application.summary') }} ({{ t('application.optional') }})</BaseLabel>
      <BaseTextArea id="summary" v-model="store.formData.summary"></BaseTextArea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <BaseLabel for="qualification_id">{{ t('job.qualification') }}</BaseLabel>
        <BaseSelect
          id="qualification_id"
          v-model="store.formData.qualification_id"
          :options="qualifications"
          placeholder="Select"
        />
      </div>
      <div>
        <BaseLabel for="subject_id">{{ t('application.subject') }}</BaseLabel>
        <BaseSelect
          id="subject_id"
          v-model="store.formData.subject_id"
          :options="subjects"
          placeholder="Select"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { maritalStatusOptions } from '@/modules/application/data/applicationData.js'
import { useApplicationStore } from '@/modules/application/store/applicationStore'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate('application')

const store = useApplicationStore()

defineProps({
  isEditMode: { type: Boolean, default: true },
  subjects: { type: Array, default: () => [] },
  qualifications: { type: Array, default: () => [] },
})
</script>
