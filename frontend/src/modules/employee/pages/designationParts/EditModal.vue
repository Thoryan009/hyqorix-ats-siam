<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="t('designations.edit')"
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
import { ref, watch } from 'vue'

import CommonForm from './CommonForm.vue'

// Designation Imports
import { useDesignationStore } from '../../stores/designationStore'
import { useDesignationMutations } from '../../queries/useDesignationMutations'

import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()

// Store
const store = useDesignationStore()

// Form State (Schema Based)
const formData = ref({
  id: '',
  name: '',
  description: '',
})

// Populate when item changes
watch(
  () => store.item,
  (item) => {
    if (item) {
      formData.value = {
        id: item.id,
        name: item.name ?? '',
        description: item.description ?? null,
      }
    }
  },
  { immediate: true },
)

// Mutations
const { update } = useDesignationMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
  },
  onError(error) {
    console.log('Update error:', error)
  },
})

// Submit
const handleSubmit = async () => {
  await update.mutateAsync(formData.value)
}
</script>
