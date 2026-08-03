<template>
  <BaseModal
    :isVisible="store.isEditModal"
    title="Edit Expense Category"
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
import { ref, watch } from 'vue'
import { useExpenseCategoryStore } from '@/finance/store/expenseCategoryStore'
import { toast } from '@/shared/config/toastConfig'
import ExpenseCategoryForm from './ExpenseCategoryForm.vue'

const store = useExpenseCategoryStore()
const loading = ref(false)
const errorMessage = ref('')

const formData = ref({
  id: '',
  description: '',
})

watch(
  () => store.item,
  (newItem) => {
    if (!newItem) return

    formData.value = {
      id: newItem.id,
      description: newItem.description ?? '',
    }
  },
  { immediate: true }
)

const closeModal = () => {
  store.handleToggleModal()
  errorMessage.value = ''
}

const handleSubmit = async () => {
  errorMessage.value = ''
  loading.value = true

  const result = await store.updateCategory(formData.value)

  loading.value = false

  if (!result.ok) {
    errorMessage.value = result.message
    return
  }

  toast.success(`${store.moduleName} operation successful`)
  closeModal()
}
</script>
