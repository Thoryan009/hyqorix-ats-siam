<template>
  <BaseModal :isVisible="store.isModal" :title="t('documents.add')" @close="store.handleToggleModal">
    <CommonForm
      v-model:formData="formData"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="submitLoading"
    />
  </BaseModal>
</template>

<script setup>
import { ref } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import BaseModal from '@/shared/components/base/BaseModal.vue'
import CommonForm from './CommonForm.vue'
import { useDocumentMutations } from '../../queries/useDocumentMutations'
import { useDocumentStore } from '../../store/documentStore'

const { t } = useTranslate()
const store = useDocumentStore()

const formData = ref({
  name: '',
  category: '',
  document_file: null,
  document_preview: null,
})

const { submit, submitLoading } = useDocumentMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
    store.handleReset(formData.value)
    formData.value = {
      name: '',
      category: '',
      document_file: null,
      document_preview: null,
    }
  },
})

const handleSubmit = async () => {
  const payload = new FormData()

  for (const key in formData.value) {
    const value = formData.value[key]
    if (value === null || value === undefined || value === '') continue
    if (key.includes('_preview')) continue
    payload.append(key, value)
  }

  await submit.mutateAsync(payload)
}
</script>
