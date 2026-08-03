<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="`Edit ${store.moduleName}`"
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
import { ref, watch } from 'vue'
import CommonForm from './CommonForm.vue'
import { useBankStore } from '@/finance/store/bankStore'
import { toast } from '@/shared/config/toastConfig'

const store = useBankStore()
const loading = ref(false)
const errorMessage = ref('')

const formData = ref({
  id: '',
  bank_name: '',
  swift_code: '',
  address: '',
  branch_name: '',
  status: 'Active',
})

watch(
  () => store.item,
  (item) => {
    if (!item) return

    formData.value = {
      id: item.id,
      bank_name: item.bank_name ?? '',
      swift_code: item.swift_code ?? '',
      address: item.address ?? '',
      branch_name: item.branch_name ?? '',
      status: item.status ?? 'Active',
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

  const result = await store.updateBank(formData.value)

  loading.value = false

  if (!result.ok) {
    errorMessage.value = result.message
    return
  }

  toast.success(`${store.moduleName} operation successful`)
  closeModal()
}
</script>
