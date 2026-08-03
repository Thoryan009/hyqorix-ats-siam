<template>
  <BaseModal
    :isVisible="isVisible"
    :title="modalTitle"
    :className="'max-w-[1400px] max-h-[92vh] overflow-hidden'"
    @close="closeModal"
  >
    <BaseForm class="flex flex-col" :onSubmit="handleApprove">
      <div class="grid grid-cols-1 gap-3 xl:grid-cols-12 xl:items-start">
        <!-- Bill Details -->
        <section class="rounded-xl border border-gray-200 bg-white p-3 xl:col-span-5">
          <h4 class="mb-2.5 text-sm font-semibold text-gray-900">Bill Details</h4>

          <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
            <div class="space-y-1">
              <BaseLabel for="approve_payment_date">Bill Date</BaseLabel>
              <BaseInput
                id="approve_payment_date"
                :model-value="form.payment_date"
                type="date"
                disabled
              />
            </div>

            <div class="space-y-1">
              <BaseLabel for="approve_payment_method">Payment Method</BaseLabel>
              <BaseInput id="approve_payment_method" :model-value="paymentMethodLabel" disabled />
            </div>

            <div class="space-y-1">
              <BaseLabel for="approve_category_name">Expense Category</BaseLabel>
              <BaseInput id="approve_category_name" :model-value="form.category_name" disabled />
            </div>

            <div class="space-y-1">
              <BaseLabel for="approve_head_name">Expense Head</BaseLabel>
              <BaseInput id="approve_head_name" :model-value="form.head_name" disabled />
            </div>

            <template v-if="hasLinkedBillAccountDisplay">
              <div class="space-y-1">
                <BaseLabel for="approve_linked_account_type">Linked Account Type</BaseLabel>
                <BaseInput
                  id="approve_linked_account_type"
                  :model-value="linkedBillAccountTypeLabel"
                  disabled
                />
              </div>

              <div class="space-y-1">
                <BaseLabel for="approve_linked_account_name">Linked Account</BaseLabel>
                <BaseInput
                  id="approve_linked_account_name"
                  :model-value="linkedBillAccountName"
                  disabled
                />
              </div>
            </template>

            <div class="space-y-1">
              <BaseLabel for="approve_amount">Bill Amount (৳)</BaseLabel>
              <BaseInput
                id="approve_amount"
                v-model="form.amount"
                type="number"
                min="0"
                step="0.01"
                :required="true"
                :disabled="isReadonly"
              />
            </div>

            <div class="space-y-1">
              <BaseLabel for="approve_voucher_no">Bill No</BaseLabel>
              <BaseInput
                id="approve_voucher_no"
                v-model="form.voucher_no"
                placeholder="Bill number"
                :disabled="isReadonly"
              />
            </div>

            <div class="space-y-1 sm:col-span-2">
              <BaseLabel for="approve_particular">Particular</BaseLabel>
              <BaseInput
                id="approve_particular"
                v-model="form.particular"
                placeholder="Bill particular"
                :disabled="isReadonly"
              />
            </div>

            <div class="space-y-1">
              <BaseLabel for="approve_reference_no">Reference No</BaseLabel>
              <BaseInput
                id="approve_reference_no"
                v-model="form.reference_no"
                placeholder="Reference number"
                :disabled="isReadonly"
              />
            </div>

            <div class="space-y-1">
              <BaseLabel for="approve_remarks">Entry Remarks</BaseLabel>
              <BaseInput
                id="approve_remarks"
                v-model="form.remarks"
                placeholder="Original entry remarks"
                :disabled="isReadonly"
              />
            </div>

            <div v-if="receiptUrls.length" class="space-y-1 sm:col-span-2">
              <BaseLabel>Bill Receipt{{ receiptUrls.length > 1 ? 's' : '' }}</BaseLabel>
              <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <a
                  v-for="(url, index) in receiptUrls"
                  :key="`${url}-${index}`"
                  :href="url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="block overflow-hidden rounded-lg border border-gray-200"
                >
                  <img
                    :src="url"
                    :alt="`Bill receipt ${index + 1}`"
                    class="max-h-56 w-full object-contain bg-gray-50"
                  />
                </a>
              </div>
            </div>
          </div>
        </section>

        <!-- Payment & Verification -->
        <section class="rounded-xl border border-blue-100 bg-blue-50/40 p-3 xl:col-span-4">
          <h4 class="mb-2.5 text-sm font-semibold text-gray-900">Payment & Verification</h4>

          <p
            v-if="isDuePayment"
            class="mb-2.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800"
          >
            This bill uses the Due payment method. No payment account is required during approval.
          </p>

          <div v-if="!isDuePayment" class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
            <div class="space-y-1">
              <BaseLabel for="approve_account_category">Account Category</BaseLabel>
              <BaseSelect
                v-if="!isReadonly"
                id="approve_account_category"
                v-model="form.payment_account_category"
                :options="accountCategoryOptions"
                placeholder="Select account category"
                :required="true"
              />
              <BaseInput
                v-else
                id="approve_account_category"
                :model-value="accountCategoryLabel"
                disabled
              />
            </div>

            <div class="space-y-1">
              <BaseLabel for="approve_account_type">Account Type</BaseLabel>
              <BaseSelect
                v-if="!isReadonly && form.payment_account_category === 'main'"
                id="approve_account_type"
                v-model="form.main_account_type"
                :options="mainAccountTypeSelectOptions"
                placeholder="Select account type"
                :required="true"
              />
              <BaseInput v-else id="approve_account_type" :model-value="accountTypeLabel" disabled />
            </div>

            <div class="space-y-1 sm:col-span-2">
              <BaseLabel for="approve_payment_account_id">Payment Account</BaseLabel>
              <BaseSelect
                v-if="!isReadonly"
                id="approve_payment_account_id"
                v-model="form.payment_account_id"
                :options="paymentAccountOptions"
                placeholder="Select account"
                :required="true"
                :disabled="!canSelectPaymentAccount"
              />
              <BaseInput
                v-else
                id="approve_payment_account_id"
                :model-value="selectedPaymentAccountLabel"
                disabled
              />
            </div>
          </div>

          <div class="mt-2.5 space-y-1">
            <BaseLabel for="approve_approval_remarks">Accountant Remarks</BaseLabel>
            <textarea
              id="approve_approval_remarks"
              v-model="form.approval_remarks"
              rows="2"
              class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
              placeholder="Verification notes or approval comments"
              :disabled="isReadonly"
            ></textarea>
          </div>
        </section>

        <!-- Summary Sidebar -->
        <aside class="flex flex-col gap-2.5 xl:col-span-3">
          <div
            v-if="hasBillEntryAccount || showPaymentSummary"
            class="rounded-xl border border-gray-200 bg-white p-3"
          >
            <h4 class="mb-2 text-sm font-semibold text-gray-900">Account Summary</h4>

            <div v-if="hasBillEntryAccount" class="space-y-1.5 text-xs">
              <p
                class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700"
              >Bill Entry Account</p>
              <div class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1">
                <span class="text-gray-500">Type</span>
                <span class="text-right font-medium text-gray-900">{{ billEntryAccountTypeLabel }}</span>
                <span class="text-gray-500">Account</span>
                <span
                  class="text-right font-medium text-gray-900"
                >{{ linkedBillAccountName || billEntryAccount?.head_name || form.expense_cost_account_name || '—' }}</span>
                <span class="text-gray-500">Balance</span>
                <span
                  class="text-right font-semibold"
                  :class="Number(billEntryAccountBalance) >= 0 ? 'text-green-700' : 'text-red-700'"
                >{{ formatCurrency(billEntryAccountBalance) }}</span>
                <span class="text-gray-500">Bill Amount</span>
                <span class="text-right font-bold text-emerald-800">{{ formatCurrency(billAmount) }}</span>
              </div>
            </div>

            <div
              v-if="hasBillEntryAccount && showPaymentSummary"
              class="my-2 border-t border-gray-100"
            ></div>

            <div v-if="showPaymentSummary" class="space-y-1.5 text-xs">
              <p
                class="text-[11px] font-semibold uppercase tracking-wide text-blue-700"
              >Payment Summary</p>
              <div class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1">
                <span class="text-gray-500">Category</span>
                <span class="text-right font-medium text-gray-900">{{ accountCategoryLabel }}</span>
                <span class="text-gray-500">Account</span>
                <span class="text-right font-medium text-gray-900">{{ selectedPaymentAccountLabel }}</span>
                <span class="text-gray-500">Balance</span>
                <span
                  class="text-right font-medium text-gray-900"
                >{{ formatCurrency(selectedPaymentAccountBalance) }}</span>
                <span class="text-gray-500">After Payment</span>
                <span
                  class="text-right font-bold"
                  :class="insufficientPaymentBalance ? 'text-red-700' : 'text-blue-800'"
                >{{ formatCurrency(paymentBalanceAfter) }}</span>
              </div>
              <p
                v-if="insufficientPaymentBalance"
                class="rounded border border-red-200 bg-red-50 px-2 py-1 text-[11px] text-red-700"
              >Insufficient payment account balance.</p>
            </div>
          </div>

          <div
            v-if="hasDemandLetterDetails"
            class="rounded-xl border border-violet-100 bg-violet-50/60 p-3"
          >
            <h4 class="mb-2 text-sm font-semibold text-gray-900">Demand Letter</h4>
            <div class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 text-xs">
              <span class="text-gray-500">DL Number</span>
              <span class="text-right font-medium text-gray-900">{{ form.demand_letter || '—' }}</span>
              <span class="text-gray-500">Client</span>
              <span class="text-right font-medium text-gray-900">{{ form.client_name || '—' }}</span>
              <span class="text-gray-500">Country</span>
              <span class="text-right text-gray-800">{{ form.demand_letter_country || '—' }}</span>
            </div>
          </div>

          <div
            v-if="hasCandidateDetails"
            class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-3"
          >
            <h4 class="mb-2 text-sm font-semibold text-gray-900">Candidate / Job</h4>
            <div class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 text-xs">
              <span class="text-gray-500">Candidate</span>
              <span class="text-right font-medium text-gray-900">{{ form.candidate_name || '—' }}</span>
              <span class="text-gray-500">Passport</span>
              <span class="text-right font-medium text-gray-900">{{ form.passport_no || '—' }}</span>
              <span class="text-gray-500">Status</span>
              <span class="text-right text-gray-800">{{ form.application_status || '—' }}</span>
              <span class="text-gray-500">Job</span>
              <span class="text-right font-medium text-gray-900">{{ form.job_name || '—' }}</span>
              <span class="text-gray-500">Job Code</span>
              <span class="text-right text-gray-800">{{ form.job_code || '—' }}</span>
            </div>
          </div>

          <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
            <h4 class="mb-1.5 text-sm font-semibold text-gray-900">Requested By</h4>
            <p class="text-xs leading-relaxed text-slate-700">{{ requestedByParagraph }}</p>
          </div>
        </aside>
      </div>

      <p v-if="errorMessage" class="mt-2 text-sm text-red-600">{{ errorMessage }}</p>

      <div class="mt-3 shrink-0 border-t border-gray-100 pt-3">
        <div class="flex flex-wrap gap-2">
          <template v-if="!isReadonly">
            <BaseButton
              type="submit"
              class="bg-green-600 text-white hover:bg-green-700"
              :disabled="loading"
            >{{ loading ? 'Approving...' : 'Approve Bill' }}</BaseButton>
            <BaseButton
              type="button"
              class="bg-red-600 text-white hover:bg-red-700"
              :disabled="loading"
              @click="handleReject"
            >Reject Bill</BaseButton>
          </template>
          <BaseButton
            type="button"
            :className="'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50'"
            @click="closeModal"
          >{{ isReadonly ? 'Close' : 'Cancel' }}</BaseButton>
        </div>
      </div>
    </BaseForm>
  </BaseModal>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import BaseForm from '@/shared/components/base/BaseForm.vue'
import { useExpensePaymentStore } from '@/finance/store/expensePaymentStore'
import { useFinanceAccountStore } from '@/finance/store/financeAccountStore'
import { useExpenseCostAccountsStore } from '@/finance/store/expenseCostAccountsStore'
import { ACCOUNT_CATEGORIES } from '@/finance/data/accountCategoryCodes'
import { getExpenseCostConfig } from '@/finance/config/expenseCostAccountConfigs'
import { isExpenseCostAccountCategory } from '@/finance/data/expenseHeadAccountLinkData'
import {
  getLinkedBillAccountName,
  getLinkedBillAccountTypeLabel,
  hasLinkedBillAccount,
} from '@/finance/utils/linkedAccountOptions'
import { formatRequestedByParagraph } from '@/finance/data/expensePaymentData'
import {
  billAccountCategoryOptions,
  getBillAccountCategoryLabel,
  mainAccountTypeOptions,
  mapPaymentMethodToMainAccountType,
  isDuePaymentMethod,
} from '@/finance/data/billApprovalAccountData'
import { paymentMethods } from '@/finance/data/paymentData'
import { formatCurrency } from '@/finance/utils/billUtils'
import Swal from 'sweetalert2'

const props = defineProps({
  isVisible: {
    type: Boolean,
    default: false,
  },
  entry: {
    type: Object,
    default: null,
  },
  readonly: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close', 'approved', 'rejected', 'saved'])

const paymentStore = useExpensePaymentStore()
const financeAccountStore = useFinanceAccountStore()
const expenseCostAccountsStore = useExpenseCostAccountsStore()

const loading = ref(false)
const errorMessage = ref('')

const form = reactive({
  id: '',
  payment_date: '',
  category_id: '',
  category_name: '',
  head_id: '',
  head_name: '',
  amount: '',
  payment_method: 'cash',
  particular: '',
  reference_no: '',
  voucher_no: '',
  remarks: '',
  approval_remarks: '',
  receipt_url: '',
  receipt_urls: [],
  candidate_name: '',
  passport_no: '',
  application_status: '',
  job_name: '',
  job_code: '',
  client_name: '',
  demand_letter_id: null,
  demand_letter: '',
  demand_letter_country: '',
  requested_by_id: null,
  requested_by_name: '',
  requested_by_email: '',
  requested_by_type: '',
  requested_at: '',
  created_at: '',
  payment_account_category: '',
  main_account_type: '',
  payment_account_type: '',
  payment_account_id: '',
  payment_account_name: '',
  expense_cost_type: '',
  expense_cost_account_id: '',
  expense_cost_account_name: '',
  expense_cost_category_name: '',
  linked_account_category: '',
  linked_account_id: '',
  linked_account_name: '',
  linked_account_type: '',
})

const accountCategoryOptions = billAccountCategoryOptions

const mainAccountTypeSelectOptions = mainAccountTypeOptions

const accountCategoryLabel = computed(() =>
  getBillAccountCategoryLabel(form.payment_account_category)
)

const accountTypeLabel = computed(() => {
  if (form.payment_account_category === 'staff') return 'Staff'
  return form.payment_account_type || form.main_account_type || '—'
})

const canSelectPaymentAccount = computed(() => {
  if (!form.payment_account_category) return false
  if (form.payment_account_category === 'main') return Boolean(form.main_account_type)
  return true
})

function mapPaymentAccountOption(account) {
  const balance = formatCurrency(account.balance ?? account.current_balance ?? 0)

  if (account.category === ACCOUNT_CATEGORIES.MAIN) {
    return {
      id: account.id,
      name: `${account.account_type} — ${account.account_name} (${account.account_label}) — ${balance}`,
    }
  }

  if (account.category === ACCOUNT_CATEGORIES.STAFF) {
    return {
      id: account.id,
      name: `Staff — ${account.staff_code} — ${account.staff_name} — ${balance}`,
    }
  }

  return {
    id: account.id,
    name: `${account.account_name} — ${balance}`,
  }
}

function getFinanceAccount(accountId) {
  return financeAccountStore.getAccount(accountId)
}

function getFinanceAccountBalance(accountId) {
  const account = getFinanceAccount(accountId)
  if (!account) return null
  return Number(account.balance ?? account.current_balance ?? 0)
}

const paymentAccountOptions = computed(() => {
  if (form.payment_account_category === 'main') {
    return financeAccountStore
      .getAccountsByCategory(ACCOUNT_CATEGORIES.MAIN)
      .filter(
        (account) =>
          account.status === 'Active' && account.account_type === form.main_account_type
      )
      .map(mapPaymentAccountOption)
  }

  if (form.payment_account_category === 'staff') {
    return financeAccountStore
      .getAccountsByCategory(ACCOUNT_CATEGORIES.STAFF)
      .filter((account) => account.status === 'Active')
      .map(mapPaymentAccountOption)
  }

  return []
})

const selectedPaymentAccountLabel = computed(() => {
  if (form.payment_account_name) {
    return `${form.payment_account_type ? `${form.payment_account_type} — ` : ''}${
      form.payment_account_name
    }`
  }

  const selected = paymentAccountOptions.value.find(
    (option) => Number(option.id) === Number(form.payment_account_id)
  )

  return selected?.name ?? '—'
})

const selectedPaymentAccountBalance = computed(() => {
  if (!form.payment_account_id || !form.payment_account_category) return null
  return getFinanceAccountBalance(form.payment_account_id)
})

const billAmount = computed(() => Number(form.amount) || 0)

const linkedBillAccountTypeLabel = computed(() => getLinkedBillAccountTypeLabel(form))

const linkedBillAccountName = computed(() => getLinkedBillAccountName(form))

const hasLinkedBillAccountDisplay = computed(() => hasLinkedBillAccount(form))

const billEntryAccount = computed(() => {
  const category = form.linked_account_category
  const accountId = form.linked_account_id

  if (category && accountId) {
    if (isExpenseCostAccountCategory(category)) {
      return expenseCostAccountsStore.getAccount(category, accountId)
    }

    return getFinanceAccount(accountId)
  }

  if (form.expense_cost_type && form.expense_cost_account_id) {
    return expenseCostAccountsStore.getAccount(form.expense_cost_type, form.expense_cost_account_id)
  }

  if (form.category_id && form.head_id) {
    return expenseCostAccountsStore.getAccountByHeadId(form.category_id, form.head_id)
  }

  return null
})

const hasBillEntryAccount = computed(
  () =>
    hasLinkedBillAccountDisplay.value ||
    Boolean(
      billEntryAccount.value ||
        (form.expense_cost_type && form.expense_cost_account_id) ||
        (form.expense_cost_account_name &&
          expenseCostAccountsStore.getCostTypeByCategoryId(form.category_id))
    )
)

const billEntryAccountTypeLabel = computed(() => {
  if (linkedBillAccountTypeLabel.value) {
    return linkedBillAccountTypeLabel.value
  }

  const costType =
    form.expense_cost_type || expenseCostAccountsStore.getCostTypeByCategoryId(form.category_id)

  if (!costType) return '—'
  return getExpenseCostConfig(costType)?.costTypeLabel ?? '—'
})

const billEntryAccountBalance = computed(() => {
  const accountId = Number(form.linked_account_id || form.expense_cost_account_id || 0)
  if (accountId) {
    const balance = getFinanceAccountBalance(accountId)
    if (balance !== null) return balance
  }

  if (billEntryAccount.value) {
    return Number(billEntryAccount.value.balance ?? billEntryAccount.value.current_balance) || 0
  }

  return 0
})

const showPaymentSummary = computed(() =>
  Boolean(form.payment_account_category && form.payment_account_id)
)

const paymentBalanceAfter = computed(() => {
  if (selectedPaymentAccountBalance.value === null) return null
  return selectedPaymentAccountBalance.value - billAmount.value
})

const insufficientPaymentBalance = computed(
  () =>
    showPaymentSummary.value && paymentBalanceAfter.value !== null && paymentBalanceAfter.value < 0
)

const requestedByParagraph = computed(() => formatRequestedByParagraph(form))

const paymentMethodLabel = computed(() => {
  const method = paymentMethods.find((item) => item.id === form.payment_method)
  return method?.name ?? form.payment_method
})

const isDuePayment = computed(() => isDuePaymentMethod(form.payment_method))

const receiptUrls = computed(() => {
  if (Array.isArray(form.receipt_urls) && form.receipt_urls.length) {
    return form.receipt_urls.filter(Boolean)
  }
  return form.receipt_url ? [form.receipt_url] : []
})

const isReadonly = computed(
  () => props.readonly || props.entry?.status === 'approved' || props.entry?.status === 'rejected'
)

const modalTitle = computed(() => {
  if (props.entry?.status === 'approved') return 'Approved Bill Entry'
  if (props.entry?.status === 'rejected') return 'Rejected Bill Entry'
  return 'Review & Approve Bill'
})

const hasCandidateDetails = computed(() =>
  Boolean(
    form.candidate_name ||
      form.passport_no ||
      form.job_name ||
      props.entry?.candidate_name ||
      props.entry?.job_name
  )
)

const hasDemandLetterDetails = computed(() =>
  Boolean(form.demand_letter || props.entry?.demand_letter)
)

const populateForm = (entry) => {
  if (!entry) return

  Object.assign(form, {
    id: entry.id,
    payment_date: entry.payment_date || '',
    category_id: String(entry.category_id || ''),
    category_name: entry.category_name || '',
    head_id: String(entry.head_id || ''),
    head_name: entry.head_name || '',
    amount: entry.amount ?? '',
    payment_method: entry.payment_method || 'cash',
    particular: entry.particular || '',
    reference_no: entry.reference_no || '',
    voucher_no: entry.voucher_no || '',
    remarks: entry.remarks || '',
    approval_remarks: entry.approval_remarks || '',
    receipt_url: entry.receipt_url || '',
    receipt_urls: Array.isArray(entry.receipt_urls)
      ? entry.receipt_urls
      : entry.receipt_url
        ? [entry.receipt_url]
        : [],
    candidate_name: entry.candidate_name || '',
    passport_no: entry.passport_no || '',
    application_status: entry.application_status || '',
    job_name: entry.job_name || '',
    job_code: entry.job_code || '',
    client_name: entry.client_name || '',
    demand_letter_id: entry.demand_letter_id ?? null,
    demand_letter: entry.demand_letter || '',
    demand_letter_country: entry.demand_letter_country || '',
    requested_by_id: entry.requested_by_id ?? null,
    requested_by_name: entry.requested_by_name || '',
    requested_by_email: entry.requested_by_email || '',
    requested_by_type: entry.requested_by_type || '',
    requested_at: entry.requested_at || '',
    created_at: entry.created_at || '',
    payment_account_category: entry.payment_account_category || '',
    main_account_type:
      entry.payment_account_category === 'main'
        ? entry.payment_account_type || mapPaymentMethodToMainAccountType(entry.payment_method)
        : '',
    payment_account_type: entry.payment_account_type || '',
    payment_account_id: entry.payment_account_id ? String(entry.payment_account_id) : '',
    payment_account_name: entry.payment_account_name || '',
    expense_cost_type: entry.expense_cost_type || '',
    expense_cost_account_id: entry.expense_cost_account_id ?? '',
    expense_cost_account_name: entry.expense_cost_account_name || '',
    expense_cost_category_name: entry.expense_cost_category_name || '',
    linked_account_category: entry.linked_account_category || '',
    linked_account_id: entry.linked_account_id ? String(entry.linked_account_id) : '',
    linked_account_name: entry.linked_account_name || '',
    linked_account_type: entry.linked_account_type || '',
  })

  if (
    !entry.payment_account_category &&
    entry.status === 'pending' &&
    !form.payment_account_category &&
    entry.payment_method !== 'due'
  ) {
    form.payment_account_category = 'main'
    form.main_account_type = mapPaymentMethodToMainAccountType(entry.payment_method)
  }
}

watch(
  () => props.entry,
  (entry) => {
    errorMessage.value = ''
    populateForm(entry)
  },
  { immediate: true }
)

watch(
  () => form.payment_account_category,
  (category, previousCategory) => {
    if (category === previousCategory) return

    form.main_account_type =
      category === 'main' ? mapPaymentMethodToMainAccountType(form.payment_method) : ''
    form.payment_account_type = category === 'staff' ? 'Staff' : ''
    form.payment_account_id = ''
    form.payment_account_name = ''
  }
)

watch(
  () => form.main_account_type,
  (accountType, previousAccountType) => {
    if (accountType === previousAccountType) return

    form.payment_account_type = accountType
    form.payment_account_id = ''
    form.payment_account_name = ''
  }
)

watch(
  () => form.payment_account_id,
  (accountId) => {
    if (!accountId) {
      form.payment_account_name = ''
      return
    }

    if (form.payment_account_category === 'main') {
      const account = getFinanceAccount(accountId)
      form.payment_account_type = account?.account_type ?? form.main_account_type
      form.payment_account_name = account
        ? `${account.account_name} — ${account.account_label}`
        : ''
      return
    }

    if (form.payment_account_category === 'staff') {
      const account = getFinanceAccount(accountId)
      form.payment_account_type = 'Staff'
      form.payment_account_name = account ? `${account.staff_code} — ${account.staff_name}` : ''
    }
  }
)

const closeModal = () => {
  errorMessage.value = ''
  emit('close')
}

const buildPayload = () => ({ ...form })

async function handleApprove() {
  errorMessage.value = ''
  loading.value = true

  const result = await paymentStore.approveBillEntry(buildPayload())

  loading.value = false

  if (!result.ok) {
    errorMessage.value = result.message
    return
  }

  await Swal.fire({
    icon: 'success',
    title: 'Bill Approved',
    text: 'Bill entry has been verified and approved.',
    confirmButtonColor: '#22C55E',
  })

  emit('approved', result.entry)
  closeModal()
}

async function handleReject() {
  errorMessage.value = ''
  loading.value = true

  const result = await paymentStore.rejectBillEntry(buildPayload())

  loading.value = false

  if (!result.ok) {
    errorMessage.value = result.message
    return
  }

  await Swal.fire({
    icon: 'info',
    title: 'Bill Rejected',
    text: 'Bill entry has been rejected.',
    confirmButtonColor: '#22C55E',
  })

  emit('rejected', result.entry)
  closeModal()
}

async function loadApprovalAccounts() {
  await Promise.all([
    financeAccountStore.fetchAccounts(ACCOUNT_CATEGORIES.MAIN, true),
    financeAccountStore.fetchAccounts(ACCOUNT_CATEGORIES.STAFF, true),
    expenseCostAccountsStore.fetchAccounts('direct_cost'),
    expenseCostAccountsStore.fetchAccounts('client_recruitment_cost'),
    expenseCostAccountsStore.fetchAccounts('operating_cost'),
  ])
}

watch(
  () => props.isVisible,
  (visible) => {
    if (visible) {
      loadApprovalAccounts()
    }
  },
  { immediate: true }
)

onMounted(() => {
  if (props.isVisible) {
    loadApprovalAccounts()
  }
})
</script>
