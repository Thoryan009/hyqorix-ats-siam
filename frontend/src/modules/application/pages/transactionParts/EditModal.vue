<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="`Edit ${store.moduleName}`"
    @close="store.handleToggleModal"
  >
    <CommonForm
      v-model:formData="formData"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="updateLoading"
    />
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import CommonForm from './CommonForm.vue'
import { useTransactionMutations } from '@/modules/application/queries/useTransactionMutation'
import { useTransactionStore } from '@/modules/application/store/transactionStore'

const store = useTransactionStore()

// Form state
const formData = ref({
  id: '',
  transaction_id: '',
  bill_no: '',
  total_amount: null,
  total_amount_usd: null,
  paid_amount: null,
  paid_amount_usd: null,
  discount_amount: null,
  payment_method: '',
  status: '',
  payment_date: '',
  payment_time: '',
  remarks: '',
  application_id: null,
})

// Populate form when store.item updates
watch(
  () => store.item,
  (item) => {
    if (item) {
      Object.assign(formData.value, item)
    }
  },
  { immediate: true }
)

// Mutation handler
const { update, updateLoading } = useTransactionMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal() // close modal
  },
  onError(error) {
    console.log('Custom error handling:', error)
  },
})

// Submit
const handleSubmit = async () => {
  await update.mutateAsync(formData.value)
}
</script>
