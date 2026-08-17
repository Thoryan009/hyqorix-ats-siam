<template>
  <BaseForm :onSubmit="onSubmit">
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
      <div class="space-y-1.5">
        <BaseLabel for="head_category_id">Expense Category</BaseLabel>
        <BaseSelect
          id="head_category_id"
          v-model="localForm.category_id"
          :options="categoryOptions"
          placeholder="Select category"
          :required="true"
          :disabled="!categoryOptions.length"
        />
      </div>

      <div class="space-y-1.5">
        <BaseLabel for="head_name">Expense Head</BaseLabel>
        <BaseInput
          id="head_name"
          v-model="localForm.name"
          placeholder="Enter expense head name"
          :required="true"
        />
      </div>

      <div class="space-y-1.5">
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

      <div class="space-y-1.5">
        <BaseLabel for="head_status">Status</BaseLabel>
        <BaseSelect
          id="head_status"
          v-model="localForm.status"
          :options="statusOptions"
          placeholder="Select status"
          :required="true"
        />
      </div>
    </div>

    <div
      v-if="isOperatingExpenseCategory"
      class="grid grid-cols-1 gap-3 lg:grid-cols-2"
    >
      <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3.5">
        <div class="mb-2.5 flex items-start gap-2.5">
          <span
            class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700"
          >
            <i class="fa fa-link text-sm"></i>
          </span>
          <div class="min-w-0">
            <h4 class="text-sm font-semibold text-slate-900">Bills Receivable Link</h4>
            <p class="mt-0.5 text-xs leading-snug text-slate-500">
              Settle bills receivable without cash. Only one operating head can be linked.
            </p>
          </div>
        </div>

        <div class="space-y-1.5">
          <BaseLabel for="head_bills_receivable_link">Link status</BaseLabel>
          <BaseSelect
            id="head_bills_receivable_link"
            v-model="localForm.is_bills_receivable_link"
            :options="billsReceivableLinkOptions"
          />
        </div>

        <p
          class="mt-2.5 rounded-lg px-2.5 py-1.5 text-xs leading-snug"
          :class="
            localForm.is_bills_receivable_link === '1' || localForm.is_bills_receivable_link === true
              ? 'bg-emerald-50 text-emerald-700'
              : 'bg-amber-50 text-amber-700'
          "
        >
          <template
            v-if="localForm.is_bills_receivable_link === '1' || localForm.is_bills_receivable_link === true"
          >
            Link exists on this expense head.
            <span v-if="otherLinkedHeadName">
              Saving will unlink “{{ otherLinkedHeadName }}”.
            </span>
          </template>
          <template v-else-if="otherLinkedHeadName">
            Currently linked: “{{ otherLinkedHeadName }}”.
          </template>
          <template v-else>
            No bills receivable link exists yet.
          </template>
        </p>
      </div>

      <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3.5">
        <div class="mb-2.5 flex items-start gap-2.5">
          <span
            class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700"
          >
            <i class="fa fa-line-chart text-sm"></i>
          </span>
          <div class="min-w-0">
            <h4 class="text-sm font-semibold text-slate-900">Depreciation Expense</h4>
            <p class="mt-0.5 text-xs leading-snug text-slate-500">
              Marks this head on Trial Balance and as Accumulated Depreciation on the Balance Sheet.
            </p>
          </div>
        </div>

        <div class="space-y-1.5">
          <BaseLabel for="head_depreciation_expense">Mark status</BaseLabel>
          <BaseSelect
            id="head_depreciation_expense"
            v-model="localForm.is_depreciation_expense"
            :options="depreciationExpenseOptions"
          />
        </div>

        <p
          class="mt-2.5 rounded-lg px-2.5 py-1.5 text-xs leading-snug"
          :class="
            localForm.is_depreciation_expense === '1' || localForm.is_depreciation_expense === true
              ? 'bg-emerald-50 text-emerald-700'
              : 'bg-amber-50 text-amber-700'
          "
        >
          <template
            v-if="localForm.is_depreciation_expense === '1' || localForm.is_depreciation_expense === true"
          >
            This head is marked as Depreciation Expense.
            <span v-if="otherDepreciationHeadName">
              Saving will unmark “{{ otherDepreciationHeadName }}”.
            </span>
          </template>
          <template v-else-if="otherDepreciationHeadName">
            Currently marked: “{{ otherDepreciationHeadName }}”.
          </template>
          <template v-else>
            No Depreciation Expense head is marked yet.
          </template>
        </p>
      </div>
    </div>

    <ExpenseHeadAccountLinks v-model="localForm.linked_accounts" />

    <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>

    <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
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
import {
  OPERATING_COST_CATEGORY_CODE,
  isCategoryCode,
} from '@/finance/data/expenseCategoryCodes'
import { useExpenseCategoryStore } from '@/finance/store/expenseCategoryStore'
import { useExpenseHeadStore } from '@/finance/store/expenseHeadStore'
import ExpenseHeadAccountLinks from './ExpenseHeadAccountLinks.vue'

const props = defineProps({
  formData: { type: Object, required: true },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, default: false },
  errorMessage: { type: String, default: '' },
})

const emit = defineEmits(['update:formData'])

const categoryStore = useExpenseCategoryStore()
const headStore = useExpenseHeadStore()

const localForm = reactive({
  linked_accounts: [],
  ...props.formData,
  is_bills_receivable_link: normalizeLinkValue(props.formData?.is_bills_receivable_link),
  is_depreciation_expense: normalizeLinkValue(props.formData?.is_depreciation_expense),
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

const isOperatingExpenseCategory = computed(() =>
  isCategoryCode(selectedCategory.value, OPERATING_COST_CATEGORY_CODE)
)

const otherLinkedHeadName = computed(() => {
  const linked = headStore.heads.find(
    (head) =>
      head.is_bills_receivable_link &&
      Number(head.id) !== Number(localForm.id || 0)
  )
  return linked?.name ?? ''
})

const otherDepreciationHeadName = computed(() => {
  const marked = headStore.heads.find(
    (head) =>
      head.is_depreciation_expense &&
      Number(head.id) !== Number(localForm.id || 0)
  )
  return marked?.name ?? ''
})

const statusOptions = [
  { id: 'Active', name: 'Active' },
  { id: 'Inactive', name: 'Inactive' },
]

const billsReceivableLinkOptions = [
  { id: '1', name: 'Link this expense head' },
  { id: '0', name: 'Not linked' },
]

const depreciationExpenseOptions = [
  { id: '1', name: 'Mark as Depreciation Expense' },
  { id: '0', name: 'Not marked' },
]

function normalizeLinkValue(value) {
  return value === true || value === 1 || value === '1' ? '1' : '0'
}

watch(
  () => props.formData,
  (value) => {
    Object.assign(localForm, {
      ...value,
      is_bills_receivable_link: normalizeLinkValue(value?.is_bills_receivable_link),
      is_depreciation_expense: normalizeLinkValue(value?.is_depreciation_expense),
    })
  },
  { deep: true }
)

watch(isOperatingExpenseCategory, (isOperatingExpense) => {
  if (!isOperatingExpense) {
    localForm.is_bills_receivable_link = '0'
    localForm.is_depreciation_expense = '0'
  }
})

watch(
  localForm,
  (value) => {
    emit('update:formData', {
      ...value,
      is_bills_receivable_link: normalizeLinkValue(value.is_bills_receivable_link),
      is_depreciation_expense: normalizeLinkValue(value.is_depreciation_expense),
    })
  },
  { deep: true }
)
</script>
