<template>
  <BaseModal
    :isVisible="costStore.isEditModalOpen && costStore.activeCostType === costType"
    :title="`Edit ${config.costTypeLabel} Account`"
    className="xl:max-w-lg"
    @close="closeModal"
  >
    <BaseForm v-if="account" :onSubmit="handleSubmit">
      <div class="space-y-3 rounded-lg border border-gray-100 bg-gray-50 px-4 py-3 text-sm">
        <div>
          <p class="text-xs uppercase tracking-wide text-gray-400">Expense Head</p>
          <p class="mt-1 font-medium text-gray-900">{{ account.head_name }}</p>
        </div>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-400">Category</p>
            <p class="mt-1 text-gray-700">{{ account.category_name }}</p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-400">Base Price</p>
            <p class="mt-1 text-gray-700">{{ formatCurrency(account.base_price) }}</p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-400">Balance</p>
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

      <div class="space-y-2">
        <BaseLabel :for="`edit_${costType}_status`">Status</BaseLabel>
        <BaseSelect
          :id="`edit_${costType}_status`"
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
          {{ loading ? 'Saving...' : 'Save Status' }}
        </BaseButton>
      </div>
    </BaseForm>
  </BaseModal>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useExpenseCostAccountsStore } from '@/finance/store/expenseCostAccountsStore'
import { getExpenseCostConfig } from '@/finance/config/expenseCostAccountConfigs'
import { partyAccountStatusOptions } from '@/finance/data/partyAccountConstants'
import { formatCurrency } from '@/finance/utils/billUtils'
import {
  formatApplicantLedgerAmount,
  isApplicantLedgerAmountDebit,
} from '@/finance/utils/partyLedgerCsvUtils'
import { toast } from '@/shared/config/toastConfig'

const props = defineProps({
  costType: {
    type: String,
    required: true,
  },
})

const costStore = useExpenseCostAccountsStore()
const config = computed(() => getExpenseCostConfig(props.costType))

const loading = ref(false)
const errorMessage = ref('')

const form = reactive({
  status: 'Active',
})

const account = computed(() => costStore.editingAccount)

const statusOptions = partyAccountStatusOptions.map((option) => ({
  id: option.value,
  name: option.label,
}))

watch(
  () => costStore.isEditModalOpen,
  (isOpen) => {
    if (isOpen && costStore.activeCostType === props.costType && costStore.editingAccount) {
      form.status = costStore.editingAccount.status ?? 'Active'
      errorMessage.value = ''
    }
  }
)

const closeModal = () => {
  costStore.closeEditModal()
  errorMessage.value = ''
}

const handleSubmit = async () => {
  if (!account.value) return

  errorMessage.value = ''
  loading.value = true

  try {
    const result = await costStore.updateAccountStatus(
      props.costType,
      account.value.id,
      form.status
    )

    if (!result.ok) {
      errorMessage.value = result.message
      toast.error(result.message)
      return
    }

    toast.success(`${config.value.costTypeLabel} account status updated successfully`)
    closeModal()
  } finally {
    loading.value = false
  }
}
</script>
