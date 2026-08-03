<template>
  <BaseModal :isVisible="store.isModal" title="Add Income Head" className="xl:max-w-3xl" @close="closeModal">
    <IncomeHeadForm
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
import { useIncomeHeadStore } from '@/finance/store/incomeHeadStore'
import { toast } from '@/shared/config/toastConfig'
import IncomeHeadForm from './IncomeHeadForm.vue'

const props = defineProps({
  defaultCategoryId: {
    type: [String, Number],
    default: '',
  },
})

const store = useIncomeHeadStore()
const loading = ref(false)
const errorMessage = ref('')

const defaultFormData = () => ({
  category_id: props.defaultCategoryId ? String(props.defaultCategoryId) : '',
  name: '',
  base_price: 0,
  status: 'Active',
  linked_accounts: [],
  is_bills_payable_link: '0',
})

const formData = ref(defaultFormData())

watch(
  () => store.isModal,
  (isOpen) => {
    if (isOpen) {
      formData.value = defaultFormData()
      errorMessage.value = ''
    }
  }
)

const closeModal = () => {
  store.handleToggleModal()
  formData.value = defaultFormData()
  errorMessage.value = ''
}

const handleSubmit = async () => {
  errorMessage.value = ''
  loading.value = true

  const result = await store.addHead(formData.value)

  loading.value = false

  if (!result.ok) {
    errorMessage.value = result.message
    return
  }

  toast.success(`${store.moduleName} operation successful`)
  closeModal()
}
</script>
