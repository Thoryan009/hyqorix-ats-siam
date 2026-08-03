<template>
  <BaseModal
    :isVisible="store.isModal"
    title="Add Expense Category"
    @close="closeModal"
  >
    <ExpenseCategoryForm
      v-model:formData="formData"
      :onSubmit="handleSubmit"
      :onCancel="closeModal"
      :loading="loading"
      :error-message="errorMessage"
    />
  </BaseModal>
</template>

<script setup>
import { ref } from 'vue'
import { useExpenseCategoryStore } from '@/finance/store/expenseCategoryStore'
import { toast } from '@/shared/config/toastConfig'
import ExpenseCategoryForm from './ExpenseCategoryForm.vue'

const store = useExpenseCategoryStore()
const loading = ref(false)
const errorMessage = ref('')

const defaultFormData = {
  name: '',
  description: '',
  status: 'Active',
}

const formData = ref({ ...defaultFormData })

const closeModal = () => {
  store.handleToggleModal()
  formData.value = { ...defaultFormData }
  errorMessage.value = ''
}

const handleSubmit = async () => {
  errorMessage.value = ''
  loading.value = true

  const result = await store.addCategory(formData.value)

  loading.value = false

  if (!result.ok) {
    errorMessage.value = result.message
    return
  }

  toast.success(`${store.moduleName} operation successful`)
  closeModal()
}
</script>
