<template>
  <BaseModal
    :isVisible="incomeStore.isCreateModalOpen && incomeStore.activeIncomeType === incomeType"
    :title="`Create ${config.incomeTypeLabel} Account`"
    className="xl:max-w-lg"
    @close="closeModal"
  >
    <BaseForm :onSubmit="handleSubmit">
      <div class="space-y-2">
        <BaseLabel :for="`create_${incomeType}_head_id`">Income Head</BaseLabel>
        <BaseSelect
          :id="`create_${incomeType}_head_id`"
          v-model="form.headId"
          :options="headOptions"
          placeholder="Select income head"
          :required="true"
          :disabled="!headOptions.length"
        />
        <p v-if="!headOptions.length" class="text-xs text-amber-600">
          All income heads from {{ config.incomeTypeLabel }} already have accounts. Add new heads in
          Income Setup first.
        </p>
      </div>

      <div
        v-if="selectedHead"
        class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600"
      >
        <p>
          <span class="font-medium text-gray-800">Category:</span>
          {{ config.incomeTypeLabel }}
        </p>
        <p class="mt-1">
          <span class="font-medium text-gray-800">Base Price:</span>
          {{ formatCurrency(selectedHead.base_price) }}
        </p>
      </div>

      <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>

      <div class="flex justify-end gap-2 pt-2">
        <BaseButton type="button" class="bg-gray-500 text-white hover:bg-gray-600" @click="closeModal">
          Cancel
        </BaseButton>
        <BaseButton type="submit" :disabled="loading || !headOptions.length">
          <i class="fa fa-plus mr-1"></i>
          {{ loading ? 'Creating...' : 'Create Account' }}
        </BaseButton>
      </div>
    </BaseForm>
  </BaseModal>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useIncomeAccountsStore } from '@/finance/store/incomeAccountsStore'
import { useIncomeHeadStore } from '@/finance/store/incomeHeadStore'
import { getIncomeAccountConfig } from '@/finance/config/incomeAccountConfigs'
import { formatCurrency } from '@/finance/utils/billUtils'
import { toast } from '@/shared/config/toastConfig'

const props = defineProps({
  incomeType: {
    type: String,
    required: true,
  },
})

const incomeStore = useIncomeAccountsStore()
const headStore = useIncomeHeadStore()

const config = computed(() => getIncomeAccountConfig(props.incomeType))

const loading = ref(false)
const errorMessage = ref('')

const form = reactive({
  headId: '',
})

const headOptions = computed(() =>
  incomeStore.getAvailableHeads(props.incomeType).map((head) => ({
    id: head.id,
    name: `${head.name} (${formatCurrency(head.base_price)})`,
  }))
)

const selectedHead = computed(() => {
  if (!form.headId) return null
  return headStore.heads.find((head) => head.id === Number(form.headId)) ?? null
})

const resetForm = () => {
  form.headId = ''
  errorMessage.value = ''
}

watch(
  () => incomeStore.isCreateModalOpen,
  (isOpen) => {
    if (isOpen && incomeStore.activeIncomeType === props.incomeType) {
      resetForm()
    }
  }
)

const closeModal = () => {
  incomeStore.closeCreateModal()
  resetForm()
}

const handleSubmit = async () => {
  errorMessage.value = ''
  loading.value = true

  try {
    const result = await incomeStore.createAccount(props.incomeType, form.headId)

    if (!result.ok) {
      errorMessage.value = result.message
      toast.error(result.message)
      return
    }

    toast.success(`${config.value.incomeTypeLabel} account created successfully`)
    closeModal()
  } finally {
    loading.value = false
  }
}
</script>
