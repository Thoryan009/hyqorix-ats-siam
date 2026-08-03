<template>
  <BaseModal
    :isVisible="agentStore.isCreateModalOpen"
    title="Create Agent Account"
    className="xl:max-w-lg"
    @close="closeModal"
  >
    <BaseForm :onSubmit="handleSubmit">
      <div class="space-y-2">
        <BaseLabel for="create_agent_id">Agent</BaseLabel>
        <BaseSearchSelect
          id="create_agent_id"
          v-model="form.agentId"
          :options="agentOptions"
          :placeholder="isLoadingOptions ? 'Loading agents...' : 'Search agent by ID or name'"
          :required="true"
          :disabled="isLoadingOptions || !agentOptions.length"
          :filter-fn="filterAgentOption"
        />
        <p v-if="isLoadingOptions" class="text-xs text-gray-500">Loading agents...</p>
        <p v-else-if="!agentOptions.length" class="text-xs text-amber-600">
          All agents already have accounts.
        </p>
      </div>

      <div
        v-if="selectedAgent"
        class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600"
      >
        <p>
          <span class="font-medium text-gray-800">Code:</span> {{ selectedAgent.agent_code }}
        </p>
        <p class="mt-1">
          <span class="font-medium text-gray-800">Phone:</span> {{ selectedAgent.phone }}
        </p>
      </div>

      <div class="space-y-2">
        <BaseLabel for="create_advanced">Advanced (৳)</BaseLabel>
        <BaseInput
          id="create_advanced"
          v-model="form.balance"
          type="number"
          step="0.01"
          min="0"
          placeholder="Enter advanced amount"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="create_main_account_id">Main Account</BaseLabel>
        <BaseSearchSelect
          id="create_main_account_id"
          v-model="form.mainAccountId"
          :options="mainAccountOptions"
          placeholder="Select main account"
          :required="advancedRequired"
          :disabled="!mainAccountOptions.length"
          :filter-fn="filterMainAccountOption"
        />
        <p class="text-xs text-gray-500">
          Advanced amount is received into the selected main account.
        </p>
        <p v-if="!isLoadingOptions && !mainAccountOptions.length" class="text-xs text-amber-600">
          No active main accounts found.
        </p>
      </div>

      <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>

      <div class="flex justify-end gap-2 pt-2">
        <BaseButton type="button" class="bg-gray-500 text-white hover:bg-gray-600" @click="closeModal">
          Cancel
        </BaseButton>
        <BaseButton type="submit" :disabled="loading || isLoadingOptions || !agentOptions.length">
          <i class="fa fa-plus mr-1"></i>
          {{ loading ? 'Creating...' : 'Create Account' }}
        </BaseButton>
      </div>
    </BaseForm>
  </BaseModal>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useAgentAccountStore } from '@/finance/store/agentAccountStore'
import { useAgentMasterStore } from '@/finance/store/agentMasterStore'
import { useAccountStore } from '@/finance/store/accountStore'
import { formatCurrency } from '@/finance/utils/billUtils'
import { toast } from '@/shared/config/toastConfig'

const agentStore = useAgentAccountStore()
const masterStore = useAgentMasterStore()
const accountStore = useAccountStore()

const loading = ref(false)
const optionsLoading = ref(false)
const errorMessage = ref('')

const form = reactive({
  agentId: '',
  balance: '',
  mainAccountId: '',
})

const isLoadingOptions = computed(() => optionsLoading.value || masterStore.isLoading)

const advancedRequired = computed(() => Number(form.balance || 0) > 0)

const agentOptions = computed(() =>
  agentStore.getAvailableAgents().map((agent) => ({
    id: agent.id,
    name: `${agent.agent_code} - ${agent.agent_name}`,
    agent_code: agent.agent_code,
    agent_name: agent.agent_name,
    phone: agent.phone,
  }))
)

const selectedAgent = computed(() => {
  if (!form.agentId) return null
  return masterStore.getAgent(form.agentId)
})

const mainAccountOptions = computed(() =>
  accountStore.getActiveAccounts().map((account) => ({
    id: account.id,
    name: `${account.account_name}${account.account_type ? ` (${account.account_type})` : ''}${account.account_label ? ` — ${account.account_label}` : ''} — ${formatCurrency(account.current_balance ?? account.balance)}`,
    account_name: account.account_name,
    account_type: account.account_type,
  }))
)

function selectFirstMainAccount() {
  const first = mainAccountOptions.value[0]
  form.mainAccountId = first ? first.id : ''
}

const resetForm = () => {
  form.agentId = ''
  form.balance = ''
  form.mainAccountId = ''
  errorMessage.value = ''
}

function filterAgentOption(option, query) {
  const haystack = [option?.name, option?.agent_code, option?.agent_name, option?.phone, option?.id]
    .filter(Boolean)
    .join(' ')
    .toLowerCase()

  return haystack.includes(query)
}

function filterMainAccountOption(option, query) {
  const haystack = [option?.name, option?.account_name, option?.account_type, option?.id]
    .filter(Boolean)
    .join(' ')
    .toLowerCase()

  return haystack.includes(query)
}

watch(
  () => agentStore.isCreateModalOpen,
  async (isOpen) => {
    if (!isOpen) return

    resetForm()
    optionsLoading.value = true
    try {
      await Promise.all([
        masterStore.fetchAgents(true),
        agentStore.fetchAccounts(true),
        accountStore.fetchActiveAccounts(true),
      ])
      selectFirstMainAccount()
    } finally {
      optionsLoading.value = false
    }
  }
)

watch(mainAccountOptions, (options) => {
  if (!agentStore.isCreateModalOpen) return
  if (form.mainAccountId) return
  if (options.length) {
    form.mainAccountId = options[0].id
  }
})

const closeModal = () => {
  agentStore.closeCreateModal()
  resetForm()
}

const handleSubmit = async () => {
  errorMessage.value = ''

  const advancedAmount = Number(form.balance)
  if (!Number.isFinite(advancedAmount) || advancedAmount < 0) {
    errorMessage.value = 'Please enter a valid advanced amount.'
    return
  }

  if (advancedAmount > 0 && !form.mainAccountId) {
    errorMessage.value = 'Please select the main account that receives this advanced amount.'
    return
  }

  loading.value = true

  try {
    const result = await agentStore.createAccount(form.agentId, form.balance, {
      mainAccountId: advancedAmount > 0 ? Number(form.mainAccountId) : null,
    })

    if (!result.ok) {
      errorMessage.value = result.message
      toast.error(result.message)
      return
    }

    toast.success('Agent account created successfully')
    closeModal()
  } finally {
    loading.value = false
  }
}
</script>
