<template>
  <BaseModal
    :isVisible="store.isModal"
    :title="`Add ${store.moduleName}`"
    @close="store.handleToggleModal"
  >
    <CommonForm
      v-model:formData="formData"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
    />
  </BaseModal>
</template>

<script setup>
import { ref } from 'vue'

import CommonForm from './CommonForm.vue'

// Designation Imports

import app from '@/shared/config/appConfig'
import { useDesignationStore } from '../../stores/designationStore'
import { useDesignationMutations } from '../../queries/useDesignationMutations'

// Store
const store = useDesignationStore()

// Default Form Data (Schema Based)
const defaultFormData = {
  name: 'Manager',
  description: 'Responsible for managing team',
}

// Initialize formData
const formData = ref(
  app.moduleLocal
    ? { ...defaultFormData }
    : Object.fromEntries(Object.keys(defaultFormData).map((key) => [key, defaultFormData[key]])),
)

// Mutations
const { submit } = useDesignationMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
    store.handleReset(formData.value)
  },
  onError(error) {
    console.log('Error:', error)
  },
})

// Submit Handler
const handleSubmit = async () => {
  await submit.mutateAsync(formData.value)
}
</script>
