<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="t('shared.actions.edit') + ' ' + t('application.subject')"
    @close="store.handleToggleModal"
  >
    <CommonForm
      v-model:formData="formData"
      :onSubmit="handleSubmit"
      :onCancel="updateLoading"
      :loading="store.isLoading"
    />
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import CommonForm from './CommonForm.vue'
import { useSubjectMutations } from '@/modules/application/queries/useSubjectMutations'
import { useSubjectStore } from '@/modules/application/store/subjectStore'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useSubjectStore()

// Form state
const formData = ref({
  id: '',
  name: '',
})

// Populate form when store.item updates
watch(
  () => store.item,
  (item) => {
    if (item) {
      Object.assign(formData.value, item)
    }
  },
  { immediate: true }
)

// Mutation handler
const { update, updateLoading } = useSubjectMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal() // close modal
  },
  onError(error) {
    console.log('Custom error handling:', error)
  },
})

// Submit
const handleSubmit = async () => {
  await update.mutateAsync(formData.value)
}
</script>
