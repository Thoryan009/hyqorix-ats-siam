<template>
  <BaseModal
    :isVisible="manualStore.isCreateModalOpen && manualStore.activeManualType === manualType"
    :title="config.createTitle"
    className="max-w-md !overflow-visible"
    @close="closeModal"
  >
    <BaseForm :onSubmit="handleSubmit" class-name="space-y-4">
      <p class="text-sm text-gray-500">
        Enter the {{ config.typeLabel.toLowerCase() }} account name. A ledger will be available
        after creation.
      </p>

      <div class="space-y-2">
        <BaseLabel for="manual_account_name">Account Name</BaseLabel>
        <BaseInput
          id="manual_account_name"
          v-model="form.account_name"
          :placeholder="config.namePlaceholder"
          :required="true"
        />
      </div>

      <div class="space-y-2">
        <BaseLabel for="manual_account_status">Status</BaseLabel>
        <BaseSelect
          id="manual_account_status"
          v-model="form.status"
          :options="statusOptions"
          placeholder="Select status"
          :required="true"
        />
      </div>

      <div v-if="manualType === 'asset'" class="space-y-2">
        <BaseLabel for="manual_link_to_purchase">Link to Purchase</BaseLabel>
        <label
          for="manual_link_to_purchase"
          class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5"
        >
          <input
            id="manual_link_to_purchase"
            v-model="form.link_to_purchase"
            type="checkbox"
            class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
          />
          <span class="text-sm text-slate-700">
            {{ form.link_to_purchase ? 'Yes — link this asset account to purchase' : 'No' }}
          </span>
        </label>
      </div>

      <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>

      <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
        <BaseButton type="button" class="bg-gray-500 text-white hover:bg-gray-600" @click="closeModal">
          Cancel
        </BaseButton>
        <BaseButton type="submit" :disabled="loading">
          <i class="fa fa-plus mr-1"></i>
          {{ loading ? 'Creating...' : 'Create Account' }}
        </BaseButton>
      </div>
    </BaseForm>
  </BaseModal>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useCompanyManualAccountsStore } from '@/finance/store/companyManualAccountsStore'
import { getCompanyManualAccountConfig } from '@/finance/config/companyManualAccountConfigs'
import { partyAccountStatusOptions } from '@/finance/data/partyAccountConstants'
import { toast } from '@/shared/config/toastConfig'

const props = defineProps({
  manualType: {
    type: String,
    required: true,
  },
})

const manualStore = useCompanyManualAccountsStore()
const config = computed(() => getCompanyManualAccountConfig(props.manualType))

const loading = ref(false)
const errorMessage = ref('')

const form = reactive({
  account_name: '',
  status: 'Active',
  link_to_purchase: false,
})

const statusOptions = partyAccountStatusOptions.map((option) => ({
  id: option.value,
  name: option.label,
}))

const resetForm = () => {
  form.account_name = ''
  form.status = 'Active'
  form.link_to_purchase = false
  errorMessage.value = ''
}

watch(
  () => manualStore.isCreateModalOpen,
  (isOpen) => {
    if (isOpen && manualStore.activeManualType === props.manualType) {
      resetForm()
    }
  }
)

const closeModal = () => {
  manualStore.closeCreateModal()
  resetForm()
}

const handleSubmit = async () => {
  errorMessage.value = ''
  loading.value = true

  try {
    const result = await manualStore.createAccount(props.manualType, {
      account_name: form.account_name,
      status: form.status,
      ...(props.manualType === 'asset' ? { link_to_purchase: form.link_to_purchase } : {}),
    })

    if (!result.ok) {
      errorMessage.value = result.message
      toast.error(result.message)
      return
    }

    toast.success(`${config.value.typeLabel} account created successfully`)
    closeModal()
  } finally {
    loading.value = false
  }
}
</script>
