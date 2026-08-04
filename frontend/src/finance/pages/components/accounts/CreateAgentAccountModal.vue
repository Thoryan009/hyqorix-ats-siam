<template>
  <BaseModal
    :isVisible="agentStore.isCreateModalOpen"
    title="Create Agent Account"
    className="max-w-md !overflow-visible"
    @close="closeModal"
  >
    <BaseForm :onSubmit="handleSubmit" class-name="space-y-5">
      <p class="text-sm text-gray-500">
        Choose an agent to open a finance account. Search by code, name, or phone.
      </p>

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
          list-class-name="max-h-64"
        />
        <p v-if="isLoadingOptions" class="text-xs text-gray-500">Loading agents...</p>
        <p v-else-if="!agentOptions.length" class="text-xs text-amber-600">
          All agents already have accounts.
        </p>
        <p v-else class="text-xs text-gray-400">
          {{ agentOptions.length }} agent{{ agentOptions.length === 1 ? '' : 's' }} available
        </p>
      </div>

      <div
        v-if="selectedAgent"
        class="rounded-lg border border-emerald-100 bg-emerald-50/70 px-4 py-3 text-sm"
      >
        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Selected agent</p>
        <p class="mt-1 font-medium text-gray-900">{{ selectedAgent.agent_name }}</p>
        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-gray-600">
          <span>
            <span class="text-gray-400">Code:</span>
            {{ selectedAgent.agent_code }}
          </span>
          <span>
            <span class="text-gray-400">Phone:</span>
            {{ selectedAgent.phone || '—' }}
          </span>
        </div>
      </div>

      <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>

      <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
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
import { toast } from '@/shared/config/toastConfig'

const agentStore = useAgentAccountStore()
const masterStore = useAgentMasterStore()

const loading = ref(false)
const optionsLoading = ref(false)
const errorMessage = ref('')

const form = reactive({
  agentId: '',
})

const isLoadingOptions = computed(() => optionsLoading.value || masterStore.isLoading)

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

const resetForm = () => {
  form.agentId = ''
  errorMessage.value = ''
}

function filterAgentOption(option, query) {
  const haystack = [option?.name, option?.agent_code, option?.agent_name, option?.phone, option?.id]
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
      await Promise.all([masterStore.fetchAgents(true), agentStore.fetchAccounts(true)])
    } finally {
      optionsLoading.value = false
    }
  }
)

const closeModal = () => {
  agentStore.closeCreateModal()
  resetForm()
}

const handleSubmit = async () => {
  errorMessage.value = ''
  loading.value = true

  try {
    const result = await agentStore.createAccount(form.agentId)

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
