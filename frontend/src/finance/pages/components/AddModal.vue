<template>
  <BaseModal
    :isVisible="store.isModal"
    :title="`Add ${store.moduleName}`"
    @close="closeModal"
  >
    <CommonForm
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
import CommonForm from './CommonForm.vue'
import { useBankStore } from '@/finance/store/bankStore'
import { toast } from '@/shared/config/toastConfig'

const store = useBankStore()
const loading = ref(false)
const errorMessage = ref('')

const defaultFormData = {
  bank_name: '',
  swift_code: '',
  address: '',
  branch_name: '',
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

  const result = await store.addBank(formData.value)

  loading.value = false

  if (!result.ok) {
    errorMessage.value = result.message
    return
  }

  toast.success(`${store.moduleName} operation successful`)
  closeModal()
}
</script>
