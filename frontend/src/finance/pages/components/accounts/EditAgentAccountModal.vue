<template>
  <BaseModal
    :isVisible="agentStore.isEditModalOpen"
    title="Edit Agent Account"
    className="xl:max-w-lg"
    @close="closeModal"
  >
    <BaseForm v-if="account" :onSubmit="handleSubmit">
      <div class="space-y-2">
        <BaseLabel for="edit_agent_account_name">Account Name</BaseLabel>
        <BaseInput
          id="edit_agent_account_name"
          v-model="form.account_name"
          placeholder="Enter agent account name"
          :required="true"
        />
      </div>

      <div class="space-y-3 rounded-lg border border-gray-100 bg-gray-50 px-4 py-3 text-sm">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-400">Code</p>
            <p class="mt-1 text-gray-700">{{ account.agent_code }}</p>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wide text-gray-400">Phone</p>
            <p class="mt-1 text-gray-700">{{ account.phone }}</p>
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
        <BaseLabel for="edit_agent_status">Status</BaseLabel>
        <BaseSelect
          id="edit_agent_status"
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
import { useAgentAccountStore } from '@/finance/store/agentAccountStore'
import { partyAccountStatusOptions } from '@/finance/data/partyAccountConstants'
import { formatCurrency } from '@/finance/utils/billUtils'
import {
  formatApplicantLedgerAmount,
  isApplicantLedgerAmountDebit,
} from '@/finance/utils/partyLedgerCsvUtils'
import { toast } from '@/shared/config/toastConfig'

const agentStore = useAgentAccountStore()

const loading = ref(false)
const errorMessage = ref('')

const form = reactive({
  account_name: '',
  status: 'Active',
})

const account = computed(() => agentStore.editingAccount)

const statusOptions = partyAccountStatusOptions.map((option) => ({
  id: option.value,
  name: option.label,
}))

watch(
  () => agentStore.isEditModalOpen,
  (isOpen) => {
    if (isOpen && agentStore.editingAccount) {
      form.account_name =
        agentStore.editingAccount.account_name || agentStore.editingAccount.agent_name || ''
      form.status = agentStore.editingAccount.status ?? 'Active'
      errorMessage.value = ''
    }
  }
)

const closeModal = () => {
  agentStore.closeEditModal()
  errorMessage.value = ''
}

const handleSubmit = async () => {
  if (!account.value) return

  errorMessage.value = ''
  loading.value = true

  try {
    const result = await agentStore.updateAccount(account.value.id, {
      account_name: form.account_name,
      status: form.status,
    })

    if (!result.ok) {
      errorMessage.value = result.message
      toast.error(result.message)
      return
    }

    toast.success('Agent account updated successfully')
    closeModal()
  } finally {
    loading.value = false
  }
}
</script>
