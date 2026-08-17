<template>
  <BaseModal
    :isVisible="partyStore.isEditModalOpen && partyStore.activePartyType === partyType"
    :title="`Edit ${config.partyLabel} Account`"
    className="xl:max-w-lg"
    @close="closeModal"
  >
    <BaseForm v-if="account" :onSubmit="handleSubmit">
      <div class="space-y-2">
        <BaseLabel :for="`edit_${partyType}_account_name`">Account Name</BaseLabel>
        <BaseInput
          :id="`edit_${partyType}_account_name`"
          v-model="form.account_name"
          :placeholder="`Enter ${config.partyLabel.toLowerCase()} account name`"
          :required="true"
        />
      </div>

      <div class="space-y-3 rounded-lg border border-gray-100 bg-gray-50 px-4 py-3 text-sm">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-400">Code</p>
            <p class="mt-1 text-gray-700">{{ account[config.codeKey] }}</p>
          </div>
          <div v-if="!config.hidePhone">
            <p class="text-xs uppercase tracking-wide text-gray-400">Phone</p>
            <p class="mt-1 text-gray-700">{{ account.phone }}</p>
          </div>
          <div>
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
        <BaseButton type="submit" :disabled="loading || !form.account_name.trim()">
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
  account_name: '',
  status: 'Active',
})

const account = computed(() => partyStore.editingAccount)

const statusOptions = partyAccountStatusOptions.map((option) => ({
  id: option.value,
  name: option.label,
}))

watch(
  () => partyStore.isEditModalOpen,
  (isOpen) => {
    if (isOpen && partyStore.activePartyType === props.partyType && partyStore.editingAccount) {
      form.account_name =
        partyStore.editingAccount.account_name ||
        partyStore.editingAccount[config.value.nameKey] ||
        ''
      form.status = partyStore.editingAccount.status ?? 'Active'
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

  errorMessage.value = ''
  loading.value = true

  try {
    const result = await partyStore.updateAccount(
      props.partyType,
      account.value.id,
      {
        account_name: form.account_name,
        status: form.status,
      }
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
