<template>
  <BaseModal
    :isVisible="store.isEditModal"
    title="Edit Income Category"
    @close="closeModal"
  >
    <IncomeCategoryForm
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
import { useIncomeCategoryStore } from '@/finance/store/incomeCategoryStore'
import { toast } from '@/shared/config/toastConfig'
import IncomeCategoryForm from './IncomeCategoryForm.vue'

const store = useIncomeCategoryStore()
const loading = ref(false)
const errorMessage = ref('')

const formData = ref({
  id: '',
  name: '',
  description: '',
  status: 'Active',
})

watch(
  () => store.item,
  (newItem) => {
    if (!newItem) return

    formData.value = {
      id: newItem.id,
      name: newItem.name ?? '',
      description: newItem.description ?? '',
      status: newItem.status ?? 'Active',
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
