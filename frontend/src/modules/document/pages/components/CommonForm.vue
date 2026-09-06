<template>
  <BaseForm :onSubmit="onSubmit">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <div class="space-y-2">
        <BaseLabel for="name">{{ t('documents.document_name') }}</BaseLabel>
        <BaseInput
          id="name"
          v-model="localForm.name"
          :placeholder="t('documents.placeholder_name')"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="category">{{ t('documents.category') }}</BaseLabel>
        <select
          id="category"
          v-model="localForm.category"
          class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
          required
        >
          <option value="" disabled>{{ t('documents.select_category') }}</option>
          <option v-for="category in documentCategories" :key="category" :value="category">
            {{ categoryLabel(category) }}
          </option>
        </select>
      </div>
    </div>

    <div class="col-span-full mt-4">
      <div class="mt-2">
        <div v-if="localForm.document_preview" class="overflow-hidden">
          <BaseImagePreview
            v-if="fileType?.document_preview === 'image'"
            :src="localForm.document_preview"
            width="250px"
            height="200px"
            cancelKey="document"
            @cancelImage="cancelImage('document')"
          />
          <div
            v-else-if="fileType?.document_preview === 'pdf'"
            class="relative h-64 w-80 overflow-hidden rounded-lg border bg-gray-50 shadow"
          >
            <iframe :src="localForm.document_preview" class="h-full w-full"></iframe>
          </div>
        </div>

        <template v-else-if="store?.item?.path_url">
          <div
            v-if="(store?.item?.path_url || '').split('?')[0].toLowerCase().endsWith('.pdf')"
            class="relative h-64 w-80 overflow-hidden rounded-lg border bg-gray-50 shadow"
          >
            <iframe :src="store?.item.path_url" class="h-full w-full"></iframe>
          </div>
          <BaseImagePreview
            v-else-if="isImageUrl(store.item.path_url)"
            :src="store.item.path_url"
            width="320px"
            height="200px"
            cancelKey="document"
            @cancelImage="cancelImage('document')"
          />
          <div v-else class="rounded-lg border bg-gray-50 px-4 py-3 text-sm text-gray-600">
            {{ t('documents.current_file', { name: store.item.file_name }) }}
          </div>
        </template>
      </div>

      <div class="mt-4">
        <BaseLabel for="document_file">
          {{ t('documents.upload_document') }} {{ isEdit ? t('documents.keep_current_file') : '' }}
        </BaseLabel>
        <BaseFileInput
          accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx"
          @change="handleFileChange($event, 'document_file', 'document_preview', maxFileSize)"
          :fileName="fileName.document_file"
        />
        <p class="mt-1 text-xs text-gray-500">{{ t('documents.max_file_size') }}</p>
        <div v-if="fileError.document_file" class="mt-1 text-sm text-red-600">
          {{ fileError.document_file }}
        </div>
      </div>
    </div>

    <div class="flex justify-end gap-2 pt-4">
      <BaseButton class="bg-yellow-600 hover:bg-yellow-700" type="button" @click="onCancel">
        {{ t('shared.actions.cancel') }}
      </BaseButton>
      <BaseButton type="submit" :disabled="loading">
        <span v-if="loading">{{ t('shared.messages.saving') }}</span>
        <span v-else>{{ t('shared.actions.save') }}</span>
      </BaseButton>
    </div>
  </BaseForm>
</template>

<script setup>
import BaseForm from '@/shared/components/base/BaseForm.vue'
import BaseButton from '@/shared/components/base/BaseButton.vue'
import BaseInput from '@/shared/components/base/BaseInput.vue'
import BaseLabel from '@/shared/components/base/BaseLabel.vue'
import BaseFileInput from '@/shared/components/base/BaseFileInput.vue'
import BaseImagePreview from '@/shared/components/base/BaseImagePreview.vue'
import { ref, watch } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import { useFileHandler } from '@/shared/composables/useFileHandler'
import { useDocumentStore } from '../../store/documentStore'
import { documentCategories } from '../../data/documentCategories'

const { t } = useTranslate()

const props = defineProps({
  formData: { type: Object, required: true },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, default: false },
  isEdit: { type: Boolean, default: false },
})

const emit = defineEmits(['update:formData'])

const store = useDocumentStore()
const localForm = ref({ ...props.formData })
const maxFileSize = 2 * 1024 * 1024

const { handleFileChange, fileName, cancelImage, fileError, fileType } = useFileHandler(localForm.value)

watch(
  localForm,
  (newVal) => {
    emit('update:formData', newVal)
  },
  { deep: true },
)

function categoryLabel(category) {
  if (!category) return '—'
  const key = `documents.categories.${category}`
  const translated = t(key)
  return translated === key ? category : translated
}

function isImageUrl(url) {
  return /\.(jpg|jpeg|png|webp)$/i.test((url || '').split('?')[0])
}
</script>
