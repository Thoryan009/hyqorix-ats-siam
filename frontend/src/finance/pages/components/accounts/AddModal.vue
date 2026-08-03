<template>
  <BaseModal
    :isVisible="store.isModal"
    :title="`Add ${store.moduleName}`"
    className="xl:max-w-lg"
    @close="closeModal"
  >
    <AccountForm
      :key="formKey"
      :form-data="formData"
      :onSubmit="handleSubmit"
      :onCancel="closeModal"
      :loading="loading"
    />
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import AccountForm from './AccountForm.vue'
import { useAccountStore } from '@/finance/store/accountStore'
import { toast } from '@/shared/config/toastConfig'

const store = useAccountStore()
const loading = ref(false)
const formKey = ref(0)

const defaultFormData = {
  account_name: '',
  account_label: '',
  account_type: 'Cash',
  bank_id: '',
  current_balance: '',
  status: 'Active',
}

const formData = ref({ ...defaultFormData })

watch(
  () => store.isModal,
  (isOpen) => {
    if (isOpen) {
      formData.value = { ...defaultFormData }
      formKey.value += 1
    }
  },
)

const closeModal = () => {
  store.handleToggleModal()
  formData.value = { ...defaultFormData }
}

const handleSubmit = async (data) => {
  loading.value = true
  const result = await store.addAccount(data)
  loading.value = false

  if (!result.ok) {
    toast.error(result.message)
    return
  }

  toast.success(`${store.moduleName} operation successful`)
  closeModal()
}
</script>
