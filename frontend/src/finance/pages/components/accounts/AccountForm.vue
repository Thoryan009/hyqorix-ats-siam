<template>
  <BaseForm :onSubmit="handleSubmit">
    <div class="space-y-2">
      <BaseLabel for="account_type">Account Type</BaseLabel>
      <BaseSelect
        id="account_type"
        v-model="localForm.account_type"
        :options="accountTypeOptions"
        placeholder="Select account type"
        :required="true"
      />
    </div>

    <div v-if="isBankType" class="space-y-2">
      <BaseLabel for="bank_id">Bank</BaseLabel>
      <BaseSelect
        id="bank_id"
        v-model="localForm.bank_id"
        :options="bankOptions"
        placeholder="Select bank"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="account_name">Account Name</BaseLabel>
      <BaseInput
        id="account_name"
        v-model="localForm.account_name"
        placeholder="Eg: Cash, City Bank"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="account_label">Description</BaseLabel>
      <BaseInput
        id="account_label"
        v-model="localForm.account_label"
        placeholder="Eg: Cash in Hand, Main Operations Account"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="status">Status</BaseLabel>
      <BaseSelect
        id="status"
        v-model="localForm.status"
        :options="statusOptions"
        placeholder="Select status"
        :required="true"
      />
    </div>

    <div class="flex justify-end gap-2 pt-4">
      <BaseButton class="bg-yellow-600 hover:bg-yellow-700" type="button" @click="onCancel">Cancel</BaseButton>
      <BaseButton type="submit" :disabled="loading || !isFormValid">
        <span v-if="loading">Saving...</span>
        <span v-else>Save</span>
      </BaseButton>
    </div>
  </BaseForm>
</template>

<script setup>
import BaseForm from '@/shared/components/base/BaseForm.vue'
import { computed, onMounted, ref, watch } from 'vue'
import { accountStatusOptions, accountTypeOptions } from '@/finance/data/accountData'
import { useBankStore } from '@/finance/store/bankStore'

const props = defineProps({
  formData: { type: Object, required: true },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, default: false },
  isEdit: { type: Boolean, default: false },
})

const bankStore = useBankStore()
const localForm = ref({
  ...props.formData,
  bank_id: props.formData.bank_id ?? '',
})

const statusOptions = accountStatusOptions.map((option) => ({
  id: option.value,
  name: option.label,
}))

const isBankType = computed(() => localForm.value.account_type === 'Bank')

const bankOptions = computed(() =>
  bankStore.banks.map((bank) => ({
    id: bank.id,
    name: bank.bank_name,
  }))
)

const isFormValid = computed(() => {
  if (isBankType.value && !localForm.value.bank_id) {
    return false
  }

  return Boolean(
    localForm.value.account_name && localForm.value.account_type && localForm.value.status
  )
})

watch(
  () => localForm.value.account_type,
  (accountType) => {
    if (accountType !== 'Bank') {
      localForm.value.bank_id = ''
    }
  }
)

watch(
  () => localForm.value.bank_id,
  (bankId) => {
    if (!bankId || !isBankType.value) return

    const bank = bankStore.banks.find((item) => item.id === Number(bankId))
    if (!bank) return

    localForm.value.account_name = bank.bank_name
    localForm.value.account_label = bank.branch_name
  }
)

watch(
  () => props.formData,
  (newVal) => {
    localForm.value = {
      ...newVal,
      bank_id: newVal.bank_id ?? '',
    }
  },
  { deep: true }
)

const handleSubmit = () => {
  props.onSubmit({ ...localForm.value })
}

onMounted(async () => {
  await bankStore.fetchBanks()
})
</script>
