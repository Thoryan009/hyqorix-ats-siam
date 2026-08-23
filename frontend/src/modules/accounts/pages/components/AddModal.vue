<template>
  <BaseModal
    :isVisible="store.isModal"
    :title="t('accounts.add')"
    @close="store.handleToggleModal"
  >
    <CommonForm
      v-model:formData="formData"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="submitLoading"
    />
  </BaseModal>
</template>

<script setup>
import { ref } from 'vue'
import CommonForm from './CommonForm.vue'
import app from '@/shared/config/appConfig'
import { useChartOfAccountMutations } from '@/modules/accounts/queries/useChartOfAccountMutations'
import { useChartOfAccountStore } from '@/modules/accounts/store/chartOfAccountStore'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useChartOfAccountStore()

const defaultFormData = {
  code: '1000',
  name: 'Cash in Hand',
  type: 'Asset',
  financial_statement: 'Balance Sheet',
  normal_balance: 'debit',
  status: 'active',
  description: '',
  sort_order: 0,
}

const formData = ref(
  app.moduleLocal
    ? { ...defaultFormData }
    : Object.fromEntries(
        Object.keys(defaultFormData).map((key) => [
          key,
          key === 'status' ? 'active' : key === 'normal_balance' ? 'debit' : '',
        ]),
      ),
)

const { submit, submitLoading } = useChartOfAccountMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
    store.handleReset(formData.value)
    formData.value.status = 'active'
    formData.value.normal_balance = 'debit'
  },
})

const handleSubmit = async () => {
  await submit.mutateAsync({ ...formData.value })
}
</script>
