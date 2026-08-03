<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="t('job_price.edit_fee_category')"
    @close="store.handleToggleModal"
  >
    <CommonForm
      v-model:formData="formData"
      :onSubmit="handleSubmit"
      @cancel="store.handleToggleModal"
      :loading="updateLoading"
    />
  </BaseModal>
</template>

<script setup>
import { useJobDetailsCategoryMutations } from '../../queries/useJobDetailsCategoryMutations'
import { useJobDetailsCategoryStore } from '../../store/jobDetailsCategoryStore'
import CommonForm from './CommonForm.vue'
import { ref, watch } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('job')
const store = useJobDetailsCategoryStore()

const formData = ref({
  name: '',
  id: '',
})

watch(
  () => store.item,
  (newItem) => {
    if (!newItem) return

    Object.keys(formData.value).forEach((key) => {
      formData.value[key] = newItem[key] ?? formData.value[key]
    })
  },
  { immediate: true },
)

const { update, updateLoading } = useJobDetailsCategoryMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal() // ✅ close modal
  },
  onError: (error) => {
    console.log('Custom error handling', error)
  },
})

const handleSubmit = async () => {
  await update.mutateAsync(formData.value)
}
</script>
