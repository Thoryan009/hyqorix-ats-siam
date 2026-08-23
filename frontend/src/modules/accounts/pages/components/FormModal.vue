<template>
  <BaseModal
    :isVisible="store.isModal || store.isEditModal"
    :title="modalTitle"
    @close="store.handleToggleModal"
  >
    <BaseForm :onSubmit="handleSubmit">
      <div class="space-y-2">
        <BaseLabel for="code">{{ t('accounts.code') }}</BaseLabel>
        <BaseInput
          id="code"
          v-model="formData.code"
          placeholder="Eg: 1000"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="name">{{ t('accounts.account_name') }}</BaseLabel>
        <BaseInput
          id="name"
          v-model="formData.name"
          placeholder="Eg: Cash in Hand"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="type">{{ t('accounts.type') }}</BaseLabel>
        <BaseSelect
          id="type"
          v-model="formData.type"
          :options="accountTypeOptions"
          placeholder="Select type"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="financial_statement">{{ t('accounts.financial_statement') }}</BaseLabel>
        <BaseSelect
          id="financial_statement"
          v-model="formData.financial_statement"
          :options="financialStatementOptions"
          placeholder="Select statement"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="normal_balance">{{ t('accounts.normal_balance') }}</BaseLabel>
        <BaseSelect
          id="normal_balance"
          v-model="formData.normal_balance"
          :options="normalBalanceOptions"
          placeholder="Select balance"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="status">{{ t('shared.labels.status') }}</BaseLabel>
        <BaseSelect
          id="status"
          v-model="formData.status"
          :options="statusOptions"
          placeholder="Select status"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="description">{{ t('accounts.description') }}</BaseLabel>
        <BaseTextArea
          id="description"
          v-model="formData.description"
          placeholder="Optional description"
        />
      </div>

      <div class="flex justify-end gap-2 pt-4">
        <BaseButton
          class="bg-yellow-600 hover:bg-yellow-700"
          type="button"
          @click="store.handleToggleModal"
        >
          {{ t('shared.actions.cancel') }}
        </BaseButton>
        <BaseButton type="submit" :disabled="isSaving">
          <span v-if="isSaving">{{ t('shared.actions.save') }}...</span>
          <span v-else>{{ t('shared.actions.save') }}</span>
        </BaseButton>
      </div>
    </BaseForm>
  </BaseModal>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import BaseForm from '@/shared/components/base/BaseForm.vue'
import app from '@/shared/config/appConfig'
import { useChartOfAccountMutations } from '@/modules/accounts/queries/useChartOfAccountMutations'
import { useChartOfAccountStore } from '@/modules/accounts/store/chartOfAccountStore'
import { useTranslate } from '@/shared/composables/useTranslate'
import {
  accountTypeOptions,
  financialStatementOptions,
  normalBalanceOptions,
  statusOptions,
} from '../../data/chartOfAccountOptions'

const { t } = useTranslate()
const store = useChartOfAccountStore()

const emptyFormData = {
  code: '',
  name: '',
  type: '',
  financial_statement: '',
  normal_balance: 'debit',
  status: 'active',
  description: '',
  sort_order: 0,
}

const sampleFormData = {
  code: '1000',
  name: 'Cash in Hand',
  type: 'Asset',
  financial_statement: 'Balance Sheet',
  normal_balance: 'debit',
  status: 'active',
  description: '',
  sort_order: 0,
}

const createDefaultForm = () =>
  app.moduleLocal ? { ...sampleFormData } : { ...emptyFormData }

const formData = ref(createDefaultForm())

const modalTitle = computed(() =>
  store.isEditModal ? t('accounts.edit') : t('accounts.add'),
)

const isSaving = computed(() => submitLoading.value || updateLoading.value)

const resetForm = () => {
  formData.value = createDefaultForm()
}

watch(
  () => [store.isModal, store.isEditModal, store.item],
  () => {
    if (store.isEditModal && store.item) {
      formData.value = {
        code: store.item.code ?? '',
        name: store.item.name ?? '',
        type: store.item.type ?? '',
        financial_statement: store.item.financial_statement ?? '',
        normal_balance: store.item.normal_balance_raw ?? 'debit',
        status: store.item.status_raw ?? 'active',
        description: store.item.description ?? '',
        sort_order: store.item.sort_order ?? 0,
      }
      return
    }

    if (store.isModal) {
      resetForm()
    }
  },
  { immediate: true },
)

const { submit, submitLoading, update, updateLoading } = useChartOfAccountMutations(
  store.moduleName,
  {
    onSuccess() {
      store.handleToggleModal()
      resetForm()
    },
  },
)

const handleSubmit = async () => {
  if (store.isEditModal) {
    await update.mutateAsync({
      id: store.item?.id,
      data: { ...formData.value },
    })
    return
  }

  await submit.mutateAsync({ ...formData.value })
}
</script>
