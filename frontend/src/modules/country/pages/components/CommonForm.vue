<template>
  <BaseForm :onSubmit="onSubmit">
    <!-- Country Name -->
    <div class="space-y-2">
      <BaseLabel for="name">{{ t('shared.labels.name') }}</BaseLabel>
      <BaseInput
        id="name"
        v-model="localForm.name"
        placeholder="Eg: United States"
        :required="true"
      />
    </div>

    <!-- Country picture Upload Section -->
    <div class="col-span-full">

      <!-- IMAGE/PDF PREVIEW -->
      <div class="mt-2">
        <!-- New uploaded file preview -->
        <div class="overflow-hidden" v-if="localForm?.country_image_preview">
          <BaseImagePreview
            v-if="fileType?.country_image_preview === 'image'"
            :src="localForm?.country_image_preview"
            width="250px"
            height="200px"
            @cancelImage="cancelImage('country_image')"
            cancelKey="country_image"
          />
        </div>

        <!-- Existing file from database -->
        <template v-else-if="store?.item?.country_image_path">
          <div
            v-if="
              (store?.item?.country_image_url || '').split('?')[0].toLowerCase().endsWith('.pdf')
            "
            class="w-80 h-64 rounded-lg border shadow overflow-hidden relative bg-gray-50"
          >
            <iframe :src="store?.item.country_image_url" class="w-full h-full"></iframe>
          </div>
          <BaseImagePreview
            v-else
            :src="store?.item.country_image_url"
            width="320px"
            height="200px"
            cancelKey="country_image"
            @cancelImage="cancelImage('country_image')"
          />
        </template>
      </div>

      <!-- UPLOAD INPUT -->
      <div class="mt-4">
        <BaseLabel for="country_image_path">{{ t('country.upload_image') }}</BaseLabel>
        <BaseFileInput
          accept=".jpg, .jpeg, .png"
          @change="handleFileChange($event, 'country_image_path', 'country_image_preview')"
          :fileName="fileName.country_image_path"
        />
        <div v-if="fileError.country_image_path" class="text-red-600 text-sm mt-1">
          {{ fileError.country_image_path }}
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-2 pt-4">
      <BaseButton class="bg-yellow-600 hover:bg-yellow-700" type="button" @click="onCancel">
        {{ t('shared.actions.cancel') }}
      </BaseButton>
      <BaseButton type="submit" :disabled="loading">
        <span v-if="loading">{{ t('shared.actions.save') }}...</span>
        <span v-else>{{ t('shared.actions.save') }}</span>
      </BaseButton>
    </div>
  </BaseForm>
</template>

<script setup>
import BaseForm from '@/shared/components/base/BaseForm.vue'
import { ref, watch } from 'vue'
import { useFileHandler } from '@/shared/composables/useFileHandler'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()

// Props
const props = defineProps({
  formData: { type: Object, required: true },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, required: true },
})

// Emit event to sync formData
const emit = defineEmits(['update:formData'])

// Local reactive copy
const localForm = ref({ ...props.formData })

const { handleFileChange, fileName, cancelImage, fileError, fileType } = useFileHandler(
  localForm.value,
)

// Sync to parent when local changes
watch(
  localForm,
  (newVal) => {
    emit('update:formData', newVal)
  },
  { deep: true },
)
</script>
