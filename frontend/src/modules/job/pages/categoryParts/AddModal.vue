<template>
  <BaseModal
    :isVisible="store.isModal"
    :title="t('job_price.add_fee_category')"
    @close="store.handleToggleModal"
  >
    <CommonForm
      v-model:formData="formData"
      :clients="clients"
      :onSubmit="handleSubmit"
      @cancel="store.handleToggleModal"
      :loading="submitLoading"
    />
  </BaseModal>
</template>

<script setup>
import { ref, computed } from 'vue'
import app from '@/shared/config/appConfig'
import CommonForm from './CommonForm.vue'
import { useJobDetailsCategoryStore } from '../../store/jobDetailsCategoryStore'
import { useJobDetailsCategoriesQuery } from '../../queries/useJobDetailsCategoriesQuery'
import { useJobDetailsCategoryMutations } from '../../queries/useJobDetailsCategoryMutations'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('job')
const store = useJobDetailsCategoryStore()

// get client list
const { data } = useJobDetailsCategoriesQuery()
const clients = computed(() => data.value?.data?.data ?? [])

// default form
const defaultFormData = {
  name: 'Visa Processing Fees',
}

// init formData
const formData = ref(app.moduleLocal ? { ...defaultFormData } : { name: '' })

// mutation
const { submit, submitLoading } = useJobDetailsCategoryMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
    store.handleReset(formData.value)
  },
})

// submit handler
const handleSubmit = async () => {
  await submit.mutateAsync(formData.value)
}
</script>
