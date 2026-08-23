<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="t('accounts.edit')"
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
import { useChartOfAccountMutations } from '@/modules/accounts/queries/useChartOfAccountMutations'
import { useChartOfAccountStore } from '@/modules/accounts/store/chartOfAccountStore'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useChartOfAccountStore()

const formData = ref({
  id: '',
  code: '',
  name: '',
  type: '',
  financial_statement: '',
  normal_balance: 'debit',
  status: 'active',
  description: '',
  sort_order: 0,
})

watch(
  () => store.item,
  (item) => {
    if (!item) return

    formData.value = {
      id: item.id,
      code: item.code ?? '',
      name: item.name ?? '',
      type: item.type ?? '',
      financial_statement: item.financial_statement ?? '',
      normal_balance: item.normal_balance_raw ?? 'debit',
      status: item.status_raw ?? 'active',
      description: item.description ?? '',
      sort_order: item.sort_order ?? 0,
    }
  },
  { immediate: true },
)

const { update, updateLoading } = useChartOfAccountMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
  },
})

const handleSubmit = async () => {
  const { id, ...data } = formData.value
  await update.mutateAsync({ id, data })
}
</script>
