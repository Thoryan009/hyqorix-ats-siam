<template>
  <BaseModal
    :isVisible="partyStore.isCreateModalOpen && partyStore.activePartyType === partyType"
    :title="`Create ${config.partyLabel} Account`"
    className="xl:max-w-lg"
    @close="closeModal"
  >
    <BaseForm :onSubmit="handleSubmit">
      <div class="space-y-2">
        <BaseLabel :for="`create_${partyType}_id`">{{ config.partyLabel }}</BaseLabel>
        <BaseSelect
          :id="`create_${partyType}_id`"
          v-model="form.partyId"
          :options="partyOptions"
          :placeholder="isListLoading ? `Loading ${config.partyLabelPlural.toLowerCase()}...` : `Select ${config.partyLabel.toLowerCase()}`"
          :required="true"
          :disabled="isListLoading || !partyOptions.length"
        />
        <p v-if="isListLoading" class="text-xs text-gray-500">
          Loading {{ config.partyLabelPlural.toLowerCase() }}...
        </p>
        <p v-else-if="!partyOptions.length" class="text-xs text-amber-600">
          All {{ config.partyLabelPlural.toLowerCase() }} already have accounts.
        </p>
      </div>

      <div
        v-if="selectedParty"
        class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600"
      >
        <p>
          <span class="font-medium text-gray-800">Code:</span>
          {{ selectedParty[config.codeKey] }}
        </p>
        <p class="mt-1">
          <span class="font-medium text-gray-800">Phone:</span>
          {{ selectedParty.phone }}
        </p>
      </div>

      <div class="space-y-3 rounded-lg border border-amber-100 bg-amber-50/60 px-4 py-3">
        <div class="space-y-2">
          <BaseLabel :for="`create_${partyType}_opening_amount`">Opening Amount (৳)</BaseLabel>
          <BaseInput
            :id="`create_${partyType}_opening_amount`"
            v-model="form.opening_amount"
            type="number"
            min="0"
            step="0.01"
            placeholder="Optional opening amount"
          />
        </div>

        <div class="space-y-2">
          <BaseLabel :for="`create_${partyType}_opening_type`">Amount Type</BaseLabel>
          <BaseSelect
            :id="`create_${partyType}_opening_type`"
            v-model="form.opening_amount_type"
            :options="openingTypeOptions"
            placeholder="Select receivable or payable"
            :required="Boolean(form.opening_amount && Number(form.opening_amount) > 0)"
          />
          <p class="text-xs text-gray-500">
            Required when opening amount is greater than 0. Receivable = DR, Payable = CR.
          </p>
        </div>
      </div>

      <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>

      <div class="flex justify-end gap-2 pt-2">
        <BaseButton type="button" class="bg-gray-500 text-white hover:bg-gray-600" @click="closeModal">
          Cancel
        </BaseButton>
        <BaseButton type="submit" :disabled="loading || isListLoading || !partyOptions.length">
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
import { usePartyMasterStore } from '@/finance/store/partyMasterStore'
import { getPartyConfig } from '@/finance/config/partyAccountConfigs'
import { toast } from '@/shared/config/toastConfig'

const props = defineProps({
  partyType: {
    type: String,
    required: true,
  },
})

const partyStore = usePartyAccountsStore()
const masterStore = usePartyMasterStore()

const config = computed(() => getPartyConfig(props.partyType))

const isListLoading = computed(() => masterStore.isLoading(props.partyType))

const loading = ref(false)
const errorMessage = ref('')

const openingTypeOptions = [
  { id: 'receivable', name: 'Receivable' },
  { id: 'payable', name: 'Payable' },
]

const form = reactive({
  partyId: '',
  opening_amount: '',
  opening_amount_type: '',
})

const partyOptions = computed(() =>
  partyStore.getAvailableParties(props.partyType).map((party) => ({
    id: party.id,
    name: `${party[config.value.codeKey]} - ${party[config.value.nameKey]}`,
  }))
)

const selectedParty = computed(() => {
  if (!form.partyId) return null
  return masterStore.getParty(props.partyType, form.partyId)
})

const resetForm = () => {
  form.partyId = ''
  form.opening_amount = ''
  form.opening_amount_type = ''
  errorMessage.value = ''
}

watch(
  () => partyStore.isCreateModalOpen,
  async (isOpen) => {
    if (isOpen && partyStore.activePartyType === props.partyType) {
      resetForm()
      await masterStore.fetchParties(props.partyType, true)
    }
  }
)

const closeModal = () => {
  partyStore.closeCreateModal()
  resetForm()
}

const handleSubmit = async () => {
  errorMessage.value = ''

  const openingAmount = Number(form.opening_amount || 0)
  if (openingAmount > 0 && !form.opening_amount_type) {
    errorMessage.value = 'Please choose Receivable or Payable for the opening amount.'
    return
  }

  loading.value = true

  try {
    const options =
      openingAmount > 0
        ? {
            opening_amount: openingAmount,
            opening_amount_type: form.opening_amount_type,
          }
        : {}

    const result = await partyStore.createAccount(props.partyType, form.partyId, options)

    if (!result.ok) {
      errorMessage.value = result.message
      toast.error(result.message)
      return
    }

    toast.success(`${config.value.partyLabel} account created successfully`)
    closeModal()
  } finally {
    loading.value = false
  }
}
</script>
