<template>
  <BaseModal
    :isVisible="costStore.isCreateModalOpen && costStore.activeCostType === costType"
    :title="`Create ${config.costTypeLabel} Account`"
    className="xl:max-w-lg"
    @close="closeModal"
  >
    <BaseForm :onSubmit="handleSubmit">
      <div class="space-y-2">
        <BaseLabel :for="`create_${costType}_head_id`">Expense Head</BaseLabel>
        <BaseSelect
          :id="`create_${costType}_head_id`"
          v-model="form.headId"
          :options="headOptions"
          placeholder="Select expense head"
          :required="true"
          :disabled="!headOptions.length"
        />
        <p v-if="!headOptions.length" class="text-xs text-amber-600">
          All expense heads from {{ config.costTypeLabel }} already have accounts. Add new heads in
          Expense Setup first.
        </p>
      </div>

      <div
        v-if="selectedHead"
        class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-600"
      >
        <p>
          <span class="font-medium text-gray-800">Category:</span>
          {{ config.costTypeLabel }}
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
import { useExpenseCostAccountsStore } from '@/finance/store/expenseCostAccountsStore'
import { useExpenseHeadStore } from '@/finance/store/expenseHeadStore'
import { getExpenseCostConfig } from '@/finance/config/expenseCostAccountConfigs'
import { formatCurrency } from '@/finance/utils/billUtils'
import { toast } from '@/shared/config/toastConfig'

const props = defineProps({
  costType: {
    type: String,
    required: true,
  },
})

const costStore = useExpenseCostAccountsStore()
const headStore = useExpenseHeadStore()

const config = computed(() => getExpenseCostConfig(props.costType))

const loading = ref(false)
const errorMessage = ref('')

const form = reactive({
  headId: '',
})

const headOptions = computed(() =>
  costStore.getAvailableHeads(props.costType).map((head) => ({
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
  () => costStore.isCreateModalOpen,
  (isOpen) => {
    if (isOpen && costStore.activeCostType === props.costType) {
      resetForm()
    }
  }
)

const closeModal = () => {
  costStore.closeCreateModal()
  resetForm()
}

const handleSubmit = async () => {
  errorMessage.value = ''
  loading.value = true

  try {
    const result = await costStore.createAccount(props.costType, form.headId)

    if (!result.ok) {
      errorMessage.value = result.message
      toast.error(result.message)
      return
    }

    toast.success(`${config.value.costTypeLabel} account created successfully`)
    closeModal()
  } finally {
    loading.value = false
  }
}
</script>
