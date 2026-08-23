<template>
  <BaseForm :onSubmit="onSubmit">
    <div class="space-y-2">
      <BaseLabel for="code">{{ t('accounts.code') }}</BaseLabel>
      <BaseInput
        id="code"
        v-model="localForm.code"
        placeholder="Eg: 1000"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="name">{{ t('accounts.account_name') }}</BaseLabel>
      <BaseInput
        id="name"
        v-model="localForm.name"
        placeholder="Eg: Cash in Hand"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="type">{{ t('accounts.type') }}</BaseLabel>
      <BaseSelect
        id="type"
        v-model="localForm.type"
        :options="accountTypeOptions"
        placeholder="Select type"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="financial_statement">{{ t('accounts.financial_statement') }}</BaseLabel>
      <BaseSelect
        id="financial_statement"
        v-model="localForm.financial_statement"
        :options="financialStatementOptions"
        placeholder="Select statement"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="normal_balance">{{ t('accounts.normal_balance') }}</BaseLabel>
      <BaseSelect
        id="normal_balance"
        v-model="localForm.normal_balance"
        :options="normalBalanceOptions"
        placeholder="Select balance"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="status">{{ t('shared.labels.status') }}</BaseLabel>
      <BaseSelect
        id="status"
        v-model="localForm.status"
        :options="statusOptions"
        placeholder="Select status"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="description">{{ t('accounts.description') }}</BaseLabel>
      <BaseTextArea
        id="description"
        v-model="localForm.description"
        placeholder="Optional description"
      />
    </div>

    <div class="flex justify-end gap-2 pt-4">
      <BaseButton class="bg-yellow-600 hover:bg-yellow-700" type="button" @click="onCancel">
        {{ t('shared.actions.cancel') }}
      </BaseButton>
      <BaseButton type="submit" :disabled="loading">
        <span v-if="loading">{{ t('shared.actions.save') }}...</span>
        <span v-else>{{ t('shared.actions.save') }}</span>
      </BaseButton>
    </div>
  </BaseForm>
</template>

<script setup>
import { ref, watch } from 'vue'
import BaseForm from '@/shared/components/base/BaseForm.vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import {
  accountTypeOptions,
  financialStatementOptions,
  normalBalanceOptions,
  statusOptions,
} from '../../data/chartOfAccountOptions'

const { t } = useTranslate()

const props = defineProps({
  formData: { type: Object, required: true },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, required: true },
})

const emit = defineEmits(['update:formData'])

const localForm = ref({ ...props.formData })

watch(
  () => props.formData,
  (value) => {
    localForm.value = { ...value }
  },
  { deep: true },
)

watch(
  localForm,
  (value) => {
    emit('update:formData', value)
  },
  { deep: true },
)
</script>
