<template>
  <BaseForm :onSubmit="onSubmit">
    <div class="space-y-2">
      <BaseLabel for="head_category_id">Income Category</BaseLabel>
      <BaseSelect
        id="head_category_id"
        v-model="localForm.category_id"
        :options="categoryOptions"
        placeholder="Select category"
        :required="true"
        :disabled="!categoryOptions.length"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="head_name">Income Head</BaseLabel>
      <BaseInput
        id="head_name"
        v-model="localForm.name"
        placeholder="Enter income head name"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="head_base_price">Base Price (৳)</BaseLabel>
      <BaseInput
        id="head_base_price"
        v-model="localForm.base_price"
        type="number"
        step="0.01"
        min="0"
        placeholder="Enter base price"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="head_status">Status</BaseLabel>
      <BaseSelect
        id="head_status"
        v-model="localForm.status"
        :options="statusOptions"
        placeholder="Select status"
        :required="true"
      />
    </div>

    <div
      v-if="isOtherIncomeCategory"
      class="space-y-3 rounded-xl border border-gray-200 bg-gray-50/80 p-4"
    >
      <div>
        <h4 class="text-sm font-semibold text-gray-900">Bills Payable Link</h4>
        <p class="mt-1 text-xs text-gray-500">
          Link this Operating Income head for bills payable settled without payment. Only one income
          head can be linked at a time.
        </p>
      </div>

      <div class="space-y-2">
        <BaseLabel for="head_bills_payable_link">Link status</BaseLabel>
        <BaseSelect
          id="head_bills_payable_link"
          v-model="localForm.is_bills_payable_link"
          :options="billsPayableLinkOptions"
        />
      </div>

      <p
        class="rounded-lg px-3 py-2 text-sm"
        :class="
          localForm.is_bills_payable_link === '1' || localForm.is_bills_payable_link === true
            ? 'bg-emerald-50 text-emerald-700'
            : 'bg-amber-50 text-amber-700'
        "
      >
        <template v-if="localForm.is_bills_payable_link === '1' || localForm.is_bills_payable_link === true">
          Link exists on this income head.
          <span v-if="otherLinkedHeadName">
            Saving will unlink “{{ otherLinkedHeadName }}”.
          </span>
        </template>
        <template v-else-if="otherLinkedHeadName">
          No link on this income head. Currently linked: “{{ otherLinkedHeadName }}”.
        </template>
        <template v-else>
          No bills payable link exists yet.
        </template>
      </p>
    </div>

    <IncomeHeadAccountLinks v-model="localForm.linked_accounts" />

    <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>

    <div class="flex justify-end gap-2 pt-4">
      <BaseButton class="bg-gray-500 text-white hover:bg-gray-600" type="button" @click="onCancel">
        Cancel
      </BaseButton>
      <BaseButton type="submit" :disabled="loading">
        <span v-if="loading">Saving...</span>
        <span v-else>Save</span>
      </BaseButton>
    </div>
  </BaseForm>
</template>

<script setup>
import { computed, reactive, watch } from 'vue'
import BaseForm from '@/shared/components/base/BaseForm.vue'
import { OTHER_INCOME_CATEGORY_CODE, isCategoryCode } from '@/finance/data/incomeCategoryCodes'
import { useIncomeCategoryStore } from '@/finance/store/incomeCategoryStore'
import { useIncomeHeadStore } from '@/finance/store/incomeHeadStore'
import IncomeHeadAccountLinks from './IncomeHeadAccountLinks.vue'

const props = defineProps({
  formData: { type: Object, required: true },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, default: false },
  errorMessage: { type: String, default: '' },
})

const emit = defineEmits(['update:formData'])

const categoryStore = useIncomeCategoryStore()
const headStore = useIncomeHeadStore()

const localForm = reactive({
  linked_accounts: [],
  ...props.formData,
  is_bills_payable_link: normalizeLinkValue(props.formData?.is_bills_payable_link),
})

const categoryOptions = computed(() =>
  categoryStore.categories
    .filter((category) => category.status === 'Active')
    .map((category) => ({
      id: category.id,
      name: category.name,
    }))
)

const selectedCategory = computed(() =>
  categoryStore.categories.find(
    (category) => Number(category.id) === Number(localForm.category_id)
  ) ?? null
)

const isOtherIncomeCategory = computed(() =>
  isCategoryCode(selectedCategory.value, OTHER_INCOME_CATEGORY_CODE)
)

const otherLinkedHeadName = computed(() => {
  const linked = headStore.heads.find(
    (head) =>
      head.is_bills_payable_link &&
      Number(head.id) !== Number(localForm.id || 0)
  )
  return linked?.name ?? ''
})

const statusOptions = [
  { id: 'Active', name: 'Active' },
  { id: 'Inactive', name: 'Inactive' },
]

const billsPayableLinkOptions = [
  { id: '1', name: 'Link this income head' },
  { id: '0', name: 'Not linked' },
]

function normalizeLinkValue(value) {
  return value === true || value === 1 || value === '1' ? '1' : '0'
}

watch(
  () => props.formData,
  (value) => {
    Object.assign(localForm, {
      ...value,
      is_bills_payable_link: normalizeLinkValue(value?.is_bills_payable_link),
    })
  },
  { deep: true }
)

watch(isOtherIncomeCategory, (isOtherIncome) => {
  if (!isOtherIncome) {
    localForm.is_bills_payable_link = '0'
  }
})

watch(
  localForm,
  (value) => {
    emit('update:formData', {
      ...value,
      is_bills_payable_link: normalizeLinkValue(value.is_bills_payable_link),
    })
  },
  { deep: true }
)
</script>
