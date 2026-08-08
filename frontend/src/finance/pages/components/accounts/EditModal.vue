<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="`Edit ${store.moduleName}`"
    className="xl:max-w-lg"
    @close="closeModal"
  >
    <AccountForm
      :key="formKey"
      :form-data="formData"
      :is-edit="true"
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

const formData = ref({
  id: '',
  account_name: '',
  account_label: '',
  account_type: 'Cash',
  bank_id: '',
  status: 'Active',
})

watch(
  () => store.item,
  (item) => {
    if (item) {
      formData.value = {
        ...item,
        bank_id: item.bank_id ?? '',
      }
      formKey.value += 1
    }
  },
  { immediate: true },
)

const closeModal = () => {
  store.handleToggleModal()
}

const handleSubmit = async (data) => {
  loading.value = true
  const result = await store.updateAccount(data)
  loading.value = false

  if (!result.ok) {
    toast.error(result.message)
    return
  }

  toast.success(`${store.moduleName} operation successful`)
  closeModal()
}
</script>
