<template>
  <BaseModal
    :isVisible="partyStore.isCreateModalOpen && partyStore.activePartyType === 'owners'"
    title="Create Owner Account"
    className="xl:max-w-lg"
    @close="closeModal"
  >
    <BaseForm :onSubmit="handleSubmit">
      <p class="text-sm text-gray-500">
        Enter the owner name. A ledger will be available after creation with balance 0.
      </p>

      <div class="space-y-2">
        <BaseLabel for="create_owner_name">Owner Name</BaseLabel>
        <BaseInput
          id="create_owner_name"
          v-model="form.account_name"
          placeholder="e.g. John Owner"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="create_owner_status">Status</BaseLabel>
        <BaseSelect
          id="create_owner_status"
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
          <i class="fa fa-plus mr-1"></i>
          {{ loading ? 'Creating...' : 'Create Account' }}
        </BaseButton>
      </div>
    </BaseForm>
  </BaseModal>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import { usePartyAccountsStore } from '@/finance/store/partyAccountsStore'
import { partyAccountStatusOptions } from '@/finance/data/partyAccountConstants'
import { toast } from '@/shared/config/toastConfig'

const partyStore = usePartyAccountsStore()

const loading = ref(false)
const errorMessage = ref('')

const form = reactive({
  account_name: '',
  status: 'Active',
})

const statusOptions = partyAccountStatusOptions.map((option) => ({
  id: option.value,
  name: option.label,
}))

const resetForm = () => {
  form.account_name = ''
  form.status = 'Active'
  errorMessage.value = ''
}

watch(
  () => partyStore.isCreateModalOpen,
  async (isOpen) => {
    if (isOpen && partyStore.activePartyType === 'owners') {
      resetForm()
      await partyStore.fetchAccounts('owners', true)
    }
  }
)

const closeModal = () => {
  partyStore.closeCreateModal()
  resetForm()
}

const handleSubmit = async () => {
  errorMessage.value = ''
  loading.value = true

  try {
    const result = await partyStore.createAccount('owners', null, {
      account_name: form.account_name,
      status: form.status,
    })

    if (!result.ok) {
      errorMessage.value = result.message
      toast.error(result.message)
      return
    }

    toast.success('Owner account created successfully')
    closeModal()
  } finally {
    loading.value = false
  }
}
</script>
