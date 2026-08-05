<template>
  <BaseModal
    :isVisible="partyStore.isCreateModalOpen && partyStore.activePartyType === 'banks'"
    title="Create Bank Account"
    className="xl:max-w-lg"
    @close="closeModal"
  >
    <BaseForm :onSubmit="handleSubmit">
      <div class="space-y-2">
        <BaseLabel for="create_bank_id">Bank</BaseLabel>
        <BaseSelect
          id="create_bank_id"
          v-model="form.bankId"
          :options="bankOptions"
          :placeholder="
            isListLoading ? 'Loading banks...' : 'Select bank from master list'
          "
          :required="true"
          :disabled="isListLoading || !bankOptions.length"
        />
        <p v-if="isListLoading" class="text-xs text-gray-500">Loading banks...</p>
        <p v-else-if="!bankOptions.length" class="text-xs text-amber-600">
          All active banks already have accounts.
          <router-link to="/finance/accounts?tab=banks" class="font-medium text-primary hover:underline">
            Manage bank master
          </router-link>
        </p>
      </div>

      <div
        v-if="selectedBank"
        class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600"
      >
        <p>
          <span class="font-medium text-gray-800">SWIFT:</span>
          {{ selectedBank.swift_code || '—' }}
        </p>
        <p class="mt-1">
          <span class="font-medium text-gray-800">Branch:</span>
          {{ selectedBank.branch_name || '—' }}
        </p>
      </div>

      <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>

      <div class="flex justify-end gap-2 pt-2">
        <BaseButton type="button" class="bg-gray-500 text-white hover:bg-gray-600" @click="closeModal">
          Cancel
        </BaseButton>
        <BaseButton type="submit" :disabled="loading || isListLoading || !bankOptions.length">
          <i class="fa fa-plus mr-1"></i>
          {{ loading ? 'Creating...' : 'Create Account' }}
        </BaseButton>
      </div>
    </BaseForm>
  </BaseModal>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { usePartyAccountsStore } from '@/finance/store/partyAccountsStore'
import { useBankStore } from '@/finance/store/bankStore'
import { toast } from '@/shared/config/toastConfig'

const partyStore = usePartyAccountsStore()
const bankStore = useBankStore()

const isListLoading = computed(() => bankStore.isLoading)
const loading = ref(false)
const errorMessage = ref('')

const form = reactive({
  bankId: '',
})

const bankOptions = computed(() =>
  partyStore.getAvailableParties('banks').map((bank) => ({
    id: bank.id,
    name: bank.bank_name,
  }))
)

const selectedBank = computed(() => {
  if (!form.bankId) return null
  return bankStore.banks.find((bank) => Number(bank.id) === Number(form.bankId)) ?? null
})

const resetForm = () => {
  form.bankId = ''
  errorMessage.value = ''
}

watch(
  () => partyStore.isCreateModalOpen,
  async (isOpen) => {
    if (isOpen && partyStore.activePartyType === 'banks') {
      resetForm()
      await partyStore.fetchAccounts('banks', true)
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
    const result = await partyStore.createAccount('banks', form.bankId)

    if (!result.ok) {
      errorMessage.value = result.message
      toast.error(result.message)
      return
    }

    toast.success('Bank account created successfully')
    closeModal()
  } finally {
    loading.value = false
  }
}
</script>
