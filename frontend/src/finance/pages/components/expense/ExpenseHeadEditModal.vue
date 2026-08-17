<template>
  <BaseModal :isVisible="store.isEditModal" title="Edit Expense Head" className="xl:max-w-3xl" @close="closeModal">
    <ExpenseHeadForm
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
import { useExpenseHeadStore } from '@/finance/store/expenseHeadStore'
import { toast } from '@/shared/config/toastConfig'
import ExpenseHeadForm from './ExpenseHeadForm.vue'

const store = useExpenseHeadStore()
const loading = ref(false)
const errorMessage = ref('')

const formData = ref({
  id: '',
  category_id: '',
  name: '',
  base_price: 0,
  status: 'Active',
  linked_accounts: [],
  is_bills_receivable_link: '0',
  is_depreciation_expense: '0',
})

watch(
  () => store.item,
  (item) => {
    if (!item) return

    formData.value = {
      id: item.id,
      category_id: String(item.category_id),
      name: item.name ?? '',
      base_price: Number(item.base_price ?? 0),
      status: item.status ?? 'Active',
      linked_accounts: Array.isArray(item.linked_accounts)
        ? item.linked_accounts.map((link) => ({
            account_category: link.account_category ?? '',
          }))
        : [],
      is_bills_receivable_link: item.is_bills_receivable_link ? '1' : '0',
      is_depreciation_expense: item.is_depreciation_expense ? '1' : '0',
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

  const result = await store.updateHead(formData.value)

  loading.value = false

  if (!result.ok) {
    errorMessage.value = result.message
    return
  }

  toast.success(`${store.moduleName} operation successful`)
  closeModal()
}
</script>
