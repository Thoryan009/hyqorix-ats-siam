<template>
  <BaseModal
    :isVisible="partyStore.isEditModalOpen && partyStore.activePartyType === partyType"
    :title="`Edit ${config.partyLabel} Account`"
    className="xl:max-w-lg"
    @close="closeModal"
  >
    <BaseForm v-if="account" :onSubmit="handleSubmit">
      <div class="space-y-3 rounded-lg border border-gray-100 bg-gray-50 px-4 py-3 text-sm">
        <div>
          <p class="text-xs uppercase tracking-wide text-gray-400">{{ config.partyLabel }}</p>
          <p class="mt-1 font-medium text-gray-900">{{ account[config.nameKey] }}</p>
        </div>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-400">Code</p>
            <p class="mt-1 text-gray-700">{{ account[config.codeKey] }}</p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-400">Phone</p>
            <p class="mt-1 text-gray-700">{{ account.phone }}</p>
          </div>
          <div v-if="!canEditOpeningAmount">
            <p class="text-xs uppercase tracking-wide text-gray-400">{{ amountLabel }}</p>
            <p
              class="mt-1 font-semibold"
              :class="
                isApplicantLedgerAmountDebit(account.balance) ? 'text-red-700' : 'text-green-700'
              "
            >
              {{ formatApplicantLedgerAmount(account.balance, formatCurrency) }}
            </p>
          </div>
        </div>
      </div>

      <div v-if="canEditOpeningAmount" class="space-y-3 rounded-lg border border-amber-100 bg-amber-50/60 px-4 py-3">
        <p class="text-xs text-amber-800">
          No ledger entries yet. Set an opening amount and choose whether it is receivable or
          payable (ledger only).
        </p>

        <div class="space-y-2">
          <BaseLabel :for="`edit_${partyType}_opening_amount`">{{ amountLabel }}</BaseLabel>
          <BaseInput
            :id="`edit_${partyType}_opening_amount`"
            v-model="form.opening_amount"
            type="number"
            min="0"
            step="0.01"
            placeholder="Enter amount"
          />
        </div>

        <div class="space-y-2">
          <BaseLabel :for="`edit_${partyType}_opening_type`">Amount Type</BaseLabel>
          <BaseSelect
            :id="`edit_${partyType}_opening_type`"
            v-model="form.opening_amount_type"
            :options="openingTypeOptions"
            placeholder="Select receivable or payable"
            :required="Boolean(form.opening_amount && Number(form.opening_amount) > 0)"
          />
          <p class="text-xs text-gray-500">
            Receivable posts as DR. Payable posts as CR. Not related to bills receivable/payable.
          </p>
        </div>
      </div>

      <div class="space-y-2">
        <BaseLabel :for="`edit_${partyType}_status`">Status</BaseLabel>
        <BaseSelect
          :id="`edit_${partyType}_status`"
          v-model="form.status"
          :options="statusOptions"
          placeholder="Select status"
          :required="true"
        />
      </div>

      <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>

      <div class="flex justify-end gap-2 pt-2">
        <BaseButton type="button" class="bg-gray-500 text-white hover:bg-gray-600" @click="closeModal">
          Cancel
        </BaseButton>
        <BaseButton type="submit" :disabled="loading">
          {{ loading ? 'Saving...' : 'Save' }}
        </BaseButton>
      </div>
    </BaseForm>
  </BaseModal>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { usePartyAccountsStore } from '@/finance/store/partyAccountsStore'
import { getPartyConfig } from '@/finance/config/partyAccountConfigs'
import { partyAccountStatusOptions } from '@/finance/data/partyAccountConstants'
import { formatCurrency } from '@/finance/utils/billUtils'
import {
  formatApplicantLedgerAmount,
  isApplicantLedgerAmountDebit,
} from '@/finance/utils/partyLedgerCsvUtils'
import { toast } from '@/shared/config/toastConfig'

const props = defineProps({
  partyType: {
    type: String,
    required: true,
  },
})

const partyStore = usePartyAccountsStore()
const config = computed(() => getPartyConfig(props.partyType))

const amountLabel = computed(() => (config.value?.useAmountLabel ? 'Amount' : 'Balance'))

const loading = ref(false)
const errorMessage = ref('')

const form = reactive({
  status: 'Active',
  opening_amount: '',
  opening_amount_type: '',
})

const account = computed(() => partyStore.editingAccount)

const canEditOpeningAmount = computed(() => {
  if (!account.value) return false
  if (account.value.has_ledger_entries) return false
  return Number(account.value.balance || 0) === 0
})

const statusOptions = partyAccountStatusOptions.map((option) => ({
  id: option.value,
  name: option.label,
}))

const openingTypeOptions = [
  { id: 'receivable', name: 'Receivable (DR)' },
  { id: 'payable', name: 'Payable (CR)' },
]

watch(
  () => partyStore.isEditModalOpen,
  (isOpen) => {
    if (isOpen && partyStore.activePartyType === props.partyType && partyStore.editingAccount) {
      form.status = partyStore.editingAccount.status ?? 'Active'
      form.opening_amount = ''
      form.opening_amount_type = ''
      errorMessage.value = ''
    }
  }
)

const closeModal = () => {
  partyStore.closeEditModal()
  errorMessage.value = ''
}

const handleSubmit = async () => {
  if (!account.value) return

  const openingAmount = Number(form.opening_amount || 0)
  if (canEditOpeningAmount.value && openingAmount > 0 && !form.opening_amount_type) {
    errorMessage.value = 'Please select Receivable or Payable for the amount.'
    toast.error(errorMessage.value)
    return
  }

  errorMessage.value = ''
  loading.value = true

  try {
    const options =
      canEditOpeningAmount.value && openingAmount > 0
        ? {
            opening_amount: openingAmount,
            opening_amount_type: form.opening_amount_type,
          }
        : {}

    const result = await partyStore.updateAccountStatus(
      props.partyType,
      account.value.id,
      form.status,
      options
    )

    if (!result.ok) {
      errorMessage.value = result.message
      toast.error(result.message)
      return
    }

    toast.success(`${config.value.partyLabel} account updated successfully`)
    closeModal()
  } finally {
    loading.value = false
  }
}
</script>
