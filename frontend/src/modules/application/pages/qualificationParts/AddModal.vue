<template>
  <BaseModal :isVisible="store.isModal" :title="`${t('shared.actions.add')} ${t('job.qualification')}`"
    @close="store.handleToggleModal">

    <CommonForm v-model:formData="formData" :onSubmit="handleSubmit" :onCancel="store.handleToggleModal" :loading="submitLoading" />
  </BaseModal>
</template>

<script setup>
import { ref } from 'vue'
import CommonForm from './CommonForm.vue'
import app from '@/shared/config/appConfig'
import { useQualificationMutations } from '@/modules/application/queries/useQualificationMutations'
import { useQualificationStore } from '@/modules/application/store/qualificationStore'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useQualificationStore()


// Default Form Data
const defaultFormData = {
  name: 'Test Name',
}

// Initialize formData
const formData = ref(
  app.moduleLocal
    ? { ...defaultFormData }
    : Object.fromEntries(Object.keys(defaultFormData).map((key) => [key, '']))
)

const { submit, submitLoading } = useQualificationMutations(store.moduleName, {
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
