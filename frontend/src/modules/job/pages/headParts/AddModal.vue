<template>
  <BaseModal
    :isVisible="store.isModal"
    :title="t('job_price.add_fee_head')"
    @close="store.handleToggleModal"
  >
    <!-- Use CommonForm here -->
    <CommonForm
      v-model:formData="formData"
      :categories="categories"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="submitLoading"
    />
  </BaseModal>
</template>

<script setup>
import { ref, computed } from 'vue'
import app from '@/shared/config/appConfig'
import CommonForm from './CommonForm.vue'
import { useJobDetailsHeadStore } from '../../store/jobDetailsHeadStore'
import { useJobDetailsHeadCategoriesQuery } from '../../queries/useJobDetailsHeadQuery'
import { useJobDetailsHeadMutations } from '../../queries/useJobDetailsHeadMutations'
import { useTranslate } from '@/shared/composables/useTranslate'
const { t } = useTranslate()
// Store
const store = useJobDetailsHeadStore()

// Fetch Categories
const { data } = useJobDetailsHeadCategoriesQuery()
const categories = computed(() => data.value?.data?.data ?? [])

// Default Form Data
const defaultFormData = {
  name: 'Test Name',
  amount: 1000,
  amount_usd: 1200,
  order_details_category_id: 2,
}

// Initialize formData
const formData = ref(
  app.moduleLocal
    ? { ...defaultFormData }
    : Object.fromEntries(Object.keys(defaultFormData).map((key) => [key, ''])),
)

// Mutation
const { submit, submitLoading } = useJobDetailsHeadMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
    store.handleReset(formData.value)
  },
  onError: (error) => {
    console.log('Custom error handling', error)
  },
})

// Submit handler
const handleSubmit = async () => {
  await submit.mutateAsync(formData.value)
}
</script>
