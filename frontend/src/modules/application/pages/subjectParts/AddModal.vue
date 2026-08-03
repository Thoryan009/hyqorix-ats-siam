<template>
  <BaseModal
    :isVisible="store.isModal"
    :title="t('shared.actions.add') + ' ' + t('application.subject')"
    @close="store.handleToggleModal"
  >
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
import CommonForm from './CommonForm.vue'
import app from '@/shared/config/appConfig'
import { useSubjectMutations } from '@/modules/application/queries/useSubjectMutations'
import { useSubjectStore } from '@/modules/application/store/subjectStore'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useSubjectStore()

// Default Form Data
const defaultFormData = {
  name: 'Mathematics',
}

// Initialize formData
const formData = ref(
  app.moduleLocal
    ? { ...defaultFormData }
    : Object.fromEntries(Object.keys(defaultFormData).map((key) => [key, ''])),
)

const { submit, submitLoading } = useSubjectMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
    store.handleReset(formData.value)
  },
  onError(error) {
    console.log('Error:', error)
  },
})

// Submit handler
const handleSubmit = async () => {
  await submit.mutateAsync(formData.value)
}
</script>
