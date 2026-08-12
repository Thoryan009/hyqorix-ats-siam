<template>
  <div class="space-y-5">
    <div v-if="!transactionOnly" class="flex flex-wrap gap-2">
      <button
        v-for="type in paymentModes"
        :key="type.id"
        type="button"
        class="rounded-lg border px-3 py-2 text-sm font-semibold transition-all"
        :class="
          activePaymentMode === type.id
            ? 'border-primary bg-primary-light! text-primary ring-1 ring-primary'
            : 'border-gray-200 bg-white text-gray-700 hover:border-primary hover:bg-primary-light!'
        "
        @click="setPaymentMode(type.id)"
      >
        <i :class="[type.icon, 'mr-1.5']"></i>{{ type.label }}
        <span
          v-if="type.id === BILLS_TO_PAY_MODE && pendingBillCount"
          class="ml-1.5 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700"
        >
          {{ pendingBillCount }}
        </span>
        <span
          v-if="type.id === BILLS_PAYABLE_MODE && payableBillCount"
          class="ml-1.5 rounded-full bg-violet-100 px-2 py-0.5 text-xs font-semibold text-violet-700"
        >
          {{ payableBillCount }}
        </span>
      </button>
    </div>

    <div
      v-if="!transactionOnly && isBillsToPayMode"
      class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"
    >
      <div class="mb-4 border-b border-gray-100 pb-3">
        <h3 class="text-base font-semibold text-gray-800">Bills To Pay</h3>
        <p class="mt-1 text-sm text-gray-500">
          Manager-approved bills awaiting accountant payment review
        </p>
      </div>
      <BillEntriesPanel status-scope="pending" />
    </div>

    <div
      v-else-if="!transactionOnly && isBillsPayableMode"
      class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"
    >
      <div class="mb-4 border-b border-gray-100 pb-3">
        <h3 class="text-base font-semibold text-gray-800">Bills Payable</h3>
        <p class="mt-1 text-sm text-gray-500">
          Approved due bills posted to expense accounts across direct, client recruitment, and
          operating expense ledgers
        </p>
      </div>
      <BillEntriesPanel status-scope="payable" />
    </div>

    <div
      v-else
      class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
    >
      <div class="border-b border-gray-100 bg-gradient-to-r from-slate-50 to-white px-6 py-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h3 class="text-lg font-semibold tracking-tight text-gray-900">Other Transaction</h3>
            <p class="mt-1 text-sm text-gray-500">
              Move funds between accounts — select source, destination, then enter the amount.
            </p>
          </div>
          <span
            class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"
          >
            <i class="fa fa-exchange mr-1.5"></i>{{ transferLabel }}
          </span>
        </div>
      </div>

      <BaseForm :onSubmit="handleSubmit" class-name="space-y-0 p-6">
        <div class="space-y-6">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="space-y-2">
              <BaseLabel for="txn_date">Transaction Date</BaseLabel>
              <BaseInput id="txn_date" v-model="form.date" type="date" :required="true" />
            </div>

            <div v-if="!isAdjustmentType" class="space-y-2">
              <BaseLabel for="txn_owners_equity_account_id">
                Owner's Equity Account
                <span class="text-red-500">*</span>
              </BaseLabel>
              <BaseSearchSelect
                id="txn_owners_equity_account_id"
                v-model="form.owners_equity_account_id"
                :options="ownersEquityAccountOptions"
                placeholder="Search owner's equity account"
                :disabled="isAssetSelected || isLiabilitiesSelected"
                :filter-fn="filterAccountOption"
              />
            </div>

            <div v-if="!isAdjustmentType" class="space-y-2">
              <BaseLabel for="txn_asset_account_id">
                Asset Account
                <span class="text-red-500">*</span>
              </BaseLabel>
              <BaseSearchSelect
                id="txn_asset_account_id"
                v-model="form.asset_account_id"
                :options="assetAccountOptions"
                placeholder="Search asset account"
                :disabled="isOwnersEquitySelected || isLiabilitiesSelected"
                :filter-fn="filterAccountOption"
              />
            </div>

            <div v-if="!isAdjustmentType" class="space-y-2">
              <BaseLabel for="txn_liabilities_account_id">
                Liabilities Account
                <span class="text-red-500">*</span>
              </BaseLabel>
              <BaseSearchSelect
                id="txn_liabilities_account_id"
                v-model="form.liabilities_account_id"
                :options="liabilitiesAccountOptions"
                placeholder="Search liabilities account"
                :disabled="isOwnersEquitySelected || isAssetSelected"
                :filter-fn="filterAccountOption"
              />
            </div>
          </div>

          <p v-if="!isAdjustmentType" class="text-xs text-gray-500">
            Select exactly one of Owner's Equity, Asset, or Liabilities account (required).
          </p>

          <div v-if="!isAdjustmentType && hasExtraAccountSelected" class="space-y-2">
            <BaseLabel>Cash Direction</BaseLabel>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="option in directionOptions"
                :key="option.id"
                type="button"
                class="rounded-lg border px-4 py-2 text-sm font-semibold transition-all"
                :class="
                  form.transaction_direction === option.id
                    ? option.activeClass
                    : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300 hover:bg-gray-50'
                "
                @click="form.transaction_direction = option.id"
              >
                <i :class="[option.icon, 'mr-1.5']"></i>{{ option.label }}
              </button>
            </div>
            <p class="text-xs text-gray-500">
              {{ directionHint }}
            </p>
          </div>

          <template v-if="isAdjustmentType">
            <section class="rounded-xl border border-slate-200 bg-slate-50/70 p-5">
              <div class="mb-4 flex items-center gap-2">
                <span
                  class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-800 text-white"
                >
                  <i class="fa fa-university text-sm"></i>
                </span>
                <div>
                  <h4 class="text-sm font-semibold text-slate-900">Account</h4>
                  <p class="text-xs text-slate-500">Select the account to adjust</p>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="space-y-2">
                  <BaseLabel for="txn_account_category">Account Category</BaseLabel>
                  <BaseSelect
                    id="txn_account_category"
                    v-model="form.account_category"
                    :options="accountCategoryOptions"
                    placeholder="Select account category"
                    :required="true"
                  />
                </div>

                <div v-if="form.account_category === 'main'" class="space-y-2">
                  <BaseLabel for="txn_main_account_type">Account Type</BaseLabel>
                  <BaseSelect
                    id="txn_main_account_type"
                    v-model="form.main_account_type"
                    :options="mainAccountTypeOptions"
                    placeholder="Select account type"
                    :required="true"
                  />
                </div>

                <div class="space-y-2 md:col-span-2">
                  <BaseLabel for="txn_account_id">Account</BaseLabel>
                  <BaseSearchSelect
                    id="txn_account_id"
                    v-model="form.account_id"
                    :options="singleAccountOptions"
                    placeholder="Search and select account"
                    :required="true"
                    :disabled="!canSelectSingleAccount"
                    :filter-fn="filterAccountOption"
                  />
                </div>
              </div>
            </section>
          </template>

          <template v-else>
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_auto_1fr] lg:items-stretch">
              <section
                class="rounded-xl border border-amber-200/80 bg-gradient-to-b from-amber-50/80 to-white p-5"
              >
                <div class="mb-4 flex items-center gap-2">
                  <span
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-600 text-white"
                  >
                    <i class="fa fa-arrow-up text-sm"></i>
                  </span>
                  <div>
                    <h4 class="text-sm font-semibold text-amber-950">{{ fromSectionLabel }}</h4>
                    <p class="text-xs text-amber-800/70">Source account</p>
                  </div>
                </div>

                <div class="space-y-4">
                  <div class="space-y-2">
                    <BaseLabel for="txn_from_category">Account Category</BaseLabel>
                    <BaseSelect
                      id="txn_from_category"
                      v-model="form.from_account_category"
                      :options="fromAccountCategoryOptions"
                      placeholder="Select category"
                      :required="true"
                    />
                  </div>

                  <div v-if="form.from_account_category === 'main'" class="space-y-2">
                    <BaseLabel for="txn_from_main_type">Account Type</BaseLabel>
                    <BaseSelect
                      id="txn_from_main_type"
                      v-model="form.from_main_account_type"
                      :options="mainAccountTypeOptions"
                      placeholder="Select account type"
                      :required="true"
                    />
                  </div>

                  <div class="space-y-2">
                    <BaseLabel for="txn_from_account_id">Account</BaseLabel>
                    <BaseSearchSelect
                      id="txn_from_account_id"
                      v-model="form.from_account_id"
                      :options="fromAccountOptions"
                      placeholder="Search and select from account"
                      :required="true"
                      :disabled="!canSelectFromAccount"
                      :filter-fn="filterAccountOption"
                    />
                  </div>
                </div>
              </section>

              <div class="hidden items-center justify-center lg:flex">
                <div
                  class="flex h-12 w-12 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm"
                  aria-hidden="true"
                >
                  <i class="fa fa-long-arrow-right text-lg"></i>
                </div>
              </div>

              <div class="flex items-center justify-center lg:hidden" aria-hidden="true">
                <div
                  class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm"
                >
                  <i class="fa fa-long-arrow-down"></i>
                </div>
              </div>

              <section
                class="rounded-xl border border-emerald-200/80 bg-gradient-to-b from-emerald-50/80 to-white p-5"
              >
                <div class="mb-4 flex items-center gap-2">
                  <span
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white"
                  >
                    <i class="fa fa-arrow-down text-sm"></i>
                  </span>
                  <div>
                    <h4 class="text-sm font-semibold text-emerald-950">{{ toSectionLabel }}</h4>
                    <p class="text-xs text-emerald-800/70">Destination account</p>
                  </div>
                </div>

                <div class="space-y-4">
                  <div class="space-y-2">
                    <BaseLabel for="txn_to_category">Account Category</BaseLabel>
                    <BaseSelect
                      id="txn_to_category"
                      v-model="form.to_account_category"
                      :options="toAccountCategoryOptions"
                      placeholder="Select category"
                      :required="true"
                    />
                  </div>

                  <div v-if="form.to_account_category === 'main'" class="space-y-2">
                    <BaseLabel for="txn_to_main_type">Account Type</BaseLabel>
                    <BaseSelect
                      id="txn_to_main_type"
                      v-model="form.to_main_account_type"
                      :options="mainAccountTypeOptions"
                      placeholder="Select account type"
                      :required="true"
                    />
                  </div>

                  <div class="space-y-2">
                    <BaseLabel for="txn_to_account_id">Account</BaseLabel>
                    <BaseSearchSelect
                      id="txn_to_account_id"
                      v-model="form.to_account_id"
                      :options="toAccountOptions"
                      placeholder="Search and select to account"
                      :required="true"
                      :disabled="!canSelectToAccount"
                      :filter-fn="filterAccountOption"
                    />
                  </div>
                </div>
              </section>
            </div>
          </template>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="space-y-2 md:col-span-1">
              <BaseLabel for="txn_particular">Particular</BaseLabel>
              <BaseInput
                id="txn_particular"
                v-model="form.particular"
                :placeholder="particularPlaceholder"
              />
            </div>

            <div class="space-y-2">
              <BaseLabel for="txn_reference_no">Reference No</BaseLabel>
              <BaseInput
                id="txn_reference_no"
                v-model="form.reference_no"
                placeholder="Optional — e.g. TXN-001/26"
              />
            </div>

            <div class="space-y-2">
              <BaseLabel for="txn_remarks">Remarks</BaseLabel>
              <BaseInput
                id="txn_remarks"
                v-model="form.remarks"
                placeholder="Optional notes"
              />
            </div>
          </div>

          <section
            class="rounded-xl border border-slate-800/10 bg-slate-900 px-5 py-6 text-white shadow-inner sm:px-8"
          >
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
              <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-300">
                  Transaction Amount
                </p>
                <p class="mt-1 text-sm text-slate-400">
                  Enter the full amount to post for this {{ transferLabel.toLowerCase() }}.
                </p>

                <div class="mt-4 flex items-center gap-3">
                  <span
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-white/10 text-2xl font-semibold text-emerald-300"
                  >
                    ৳
                  </span>
                  <BaseInput
                    id="txn_amount"
                    v-model="form.amount"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="0.00"
                    :required="true"
                    class-name="h-14 w-full rounded-xl border-0 bg-white px-4 text-3xl font-semibold tracking-tight text-slate-900 shadow-sm outline-none ring-2 ring-transparent placeholder:text-slate-300 focus:ring-emerald-400"
                  />
                </div>
              </div>

              <div class="flex shrink-0 flex-wrap gap-3">
                <BaseButton
                  type="button"
                  :className="'border border-white/20 bg-white/5 px-5 py-3 text-white hover:bg-white/10'"
                  @click="resetForm"
                >
                  Reset
                </BaseButton>
                <BaseButton
                  type="submit"
                  class="bg-emerald-500 px-6 py-3 text-base font-semibold text-white hover:bg-emerald-400"
                  :disabled="submitLoading"
                  v-can="'receive_payment.create'"
                >
                  {{ submitLoading ? 'Submitting...' : submitButtonLabel }}
                </BaseButton>
              </div>
            </div>
          </section>
        </div>
      </BaseForm>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import BaseForm from '@/shared/components/base/BaseForm.vue'
import BaseSearchSelect from '@/shared/components/base/BaseSearchSelect.vue'
import BillEntriesPanel from './BillEntriesPanel.vue'
import { useFinanceAccountStore } from '@/finance/store/financeAccountStore'
import { useAccountTransactionStore } from '@/finance/store/accountTransactionStore'
import { useExpensePaymentStore } from '@/finance/store/expensePaymentStore'
import { ACCOUNT_CATEGORIES } from '@/finance/data/accountCategoryCodes'
import {
  accountTransactionCategoryOptions,
  accountTransactionTypes,
  getDefaultFromCategory,
  getDefaultToCategory,
  isAdjustmentTransactionType,
  mainAccountTypeOptions,
} from '@/finance/data/accountTransactionData'
import { getPartyConfig } from '@/finance/config/partyAccountConfigs'
import Swal from 'sweetalert2'

const BILLS_TO_PAY_MODE = 'bills_to_pay'
const BILLS_PAYABLE_MODE = 'bills_payable'
const TRANSACTION_MODE = 'transaction'

const emit = defineEmits(['saved'])

const props = defineProps({
  initialMode: { type: String, default: '' },
  transactionOnly: { type: Boolean, default: false },
})

const route = useRoute()
const router = useRouter()

const financeAccountStore = useFinanceAccountStore()
const transactionStore = useAccountTransactionStore()
const paymentStore = useExpensePaymentStore()

const submitLoading = ref(false)
const activePaymentMode = ref(
  props.transactionOnly || props.initialMode === TRANSACTION_MODE
    ? TRANSACTION_MODE
    : props.initialMode === BILLS_PAYABLE_MODE
      ? BILLS_PAYABLE_MODE
      : BILLS_TO_PAY_MODE
)
const paymentModes = [
  { id: BILLS_TO_PAY_MODE, label: 'Bills To Pay', icon: 'fa fa-clock-o' },
  { id: BILLS_PAYABLE_MODE, label: 'Bills Payable', icon: 'fa fa-file-text-o' },
]
const accountCategoryOptions = accountTransactionCategoryOptions

const fromAccountCategoryOptions = computed(() => {
  if (hasExtraAccountSelected.value && form.transaction_direction === 'receive') {
    return accountCategoryOptions.filter((option) => option.id !== 'main')
  }
  return accountCategoryOptions
})

const toAccountCategoryOptions = computed(() => {
  if (hasExtraAccountSelected.value && form.transaction_direction === 'payment') {
    return accountCategoryOptions.filter((option) => option.id !== 'main')
  }
  return accountCategoryOptions
})

const isBillsToPayMode = computed(
  () => !props.transactionOnly && activePaymentMode.value === BILLS_TO_PAY_MODE
)
const isBillsPayableMode = computed(
  () => !props.transactionOnly && activePaymentMode.value === BILLS_PAYABLE_MODE
)
const pendingBillCount = computed(() => paymentStore.pendingBillCount)
const payableBillCount = computed(() => paymentStore.payableBillCount)

const createDefaultForm = () => ({
  transaction_type: 'loan',
  date: new Date().toISOString().slice(0, 10),
  amount: '',
  particular: '',
  reference_no: '',
  remarks: '',
  transaction_direction: 'receive',
  owners_equity_account_id: '',
  asset_account_id: '',
  liabilities_account_id: '',
  account_category: 'staff',
  main_account_type: 'Cash',
  account_id: '',
  from_account_category: 'main',
  from_main_account_type: 'Cash',
  from_account_id: '',
  to_account_category: 'staff',
  to_main_account_type: '',
  to_account_id: '',
})

const form = reactive(createDefaultForm())

const isAdjustmentType = computed(() => isAdjustmentTransactionType(form.transaction_type))
const transferLabel = 'Transfer'
const activeTypeLabel = computed(() => transferLabel)

const isOwnersEquitySelected = computed(() => Boolean(form.owners_equity_account_id))
const isAssetSelected = computed(() => Boolean(form.asset_account_id))
const isLiabilitiesSelected = computed(() => Boolean(form.liabilities_account_id))
const hasExtraAccountSelected = computed(
  () => isOwnersEquitySelected.value || isAssetSelected.value || isLiabilitiesSelected.value
)

const directionOptions = [
  {
    id: 'receive',
    label: 'Receive',
    icon: 'fa fa-arrow-down',
    activeClass: 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-1 ring-emerald-500',
  },
  {
    id: 'payment',
    label: 'Payment',
    icon: 'fa fa-arrow-up',
    activeClass: 'border-amber-500 bg-amber-50 text-amber-900 ring-1 ring-amber-500',
  },
]

const directionHint = computed(() => {
  if (form.transaction_direction === 'payment') {
    return 'Payment: cash goes out — party/staff posts DR, Owner Drawings / similar accounts post DR.'
  }
  return 'Receive: cash comes in — party/staff posts CR, Owner Capital / similar accounts post CR.'
})

const ownersEquityAccountOptions = computed(() => getAccountOptions('owners_equity'))
const assetAccountOptions = computed(() =>
  financeAccountStore
    .getAccountsByCategory(ACCOUNT_CATEGORIES.ASSET)
    .filter((account) => account.status === 'Active' && !account.link_to_purchase)
    .map(mapAccountOption)
)
const liabilitiesAccountOptions = computed(() => getAccountOptions('liabilities'))

watch(assetAccountOptions, (options) => {
  if (!form.asset_account_id) return

  const stillAvailable = options.some(
    (option) => Number(option.id) === Number(form.asset_account_id)
  )
  if (!stillAvailable) {
    form.asset_account_id = ''
  }
})

watch(
  () => form.owners_equity_account_id,
  (value) => {
    if (value) {
      form.asset_account_id = ''
      form.liabilities_account_id = ''
    }
  }
)

watch(
  () => form.asset_account_id,
  (value) => {
    if (value) {
      form.owners_equity_account_id = ''
      form.liabilities_account_id = ''
    }
  }
)

watch(
  () => form.liabilities_account_id,
  (value) => {
    if (value) {
      form.owners_equity_account_id = ''
      form.asset_account_id = ''
    }
  }
)

function syncAccountCategoriesForDirection() {
  if (!hasExtraAccountSelected.value) return

  if (
    form.transaction_direction === 'receive' &&
    form.from_account_category === 'main'
  ) {
    form.from_account_category = getDefaultFromCategory(form.transaction_type)
    if (form.from_account_category === 'main') {
      form.from_account_category = 'staff'
    }
    form.from_main_account_type = form.from_account_category === 'main' ? 'Cash' : ''
    form.from_account_id = ''
  }

  if (
    form.transaction_direction === 'payment' &&
    form.to_account_category === 'main'
  ) {
    form.to_account_category = getDefaultToCategory(form.transaction_type)
    if (form.to_account_category === 'main') {
      form.to_account_category = 'staff'
    }
    form.to_main_account_type = form.to_account_category === 'main' ? 'Cash' : ''
    form.to_account_id = ''
  }
}

watch(
  () => form.transaction_direction,
  () => {
    syncAccountCategoriesForDirection()
  }
)

watch(hasExtraAccountSelected, (selected) => {
  if (selected) {
    syncAccountCategoriesForDirection()
  }
})

const fromSectionLabel = computed(() => {
  if (form.transaction_type === 'loan_repay' || form.transaction_type === 'advanced_repay') {
    return 'Repay From'
  }

  return 'Pay From'
})

const toSectionLabel = computed(() => {
  if (form.transaction_type === 'loan' || form.transaction_type === 'advanced') {
    return 'Receive To'
  }

  return 'Deposit To'
})

const particularPlaceholder = computed(() => `${activeTypeLabel.value} transaction`)

const submitButtonLabel = computed(() => `Submit ${activeTypeLabel.value}`)

function mapAccountOption(account) {
  const balance = Number(account.balance ?? account.current_balance ?? 0).toLocaleString('en-BD', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
  const category = account.category

  if (category === ACCOUNT_CATEGORIES.MAIN) {
    return {
      id: account.id,
      name: `${account.account_type} — ${account.account_name} (${account.account_label}) — ৳${balance}`,
    }
  }

  if (category === ACCOUNT_CATEGORIES.AGENT) {
    return {
      id: account.id,
      name: `Agent — ${account.agent_code} — ${account.agent_name} — ৳${balance}`,
    }
  }

  if (category === ACCOUNT_CATEGORIES.BANKS) {
    return {
      id: account.id,
      name: `Bank — ${account.account_name}${account.code ? ` (${account.code})` : ''} — ৳${balance}`,
    }
  }

  if (category === ACCOUNT_CATEGORIES.OWNERS) {
    const ownerName = account.owners_name || account.account_name
    const ownerCode = account.owners_code || account.code
    return {
      id: account.id,
      name: `Owner — ${ownerCode ? `${ownerCode} — ` : ''}${ownerName} — ৳${balance}`,
    }
  }

  const config = getPartyConfig(category)
  if (config) {
    return {
      id: account.id,
      name: `${config.partyLabel} — ${account[config.codeKey]} — ${account[config.nameKey]} — ৳${balance}`,
    }
  }

  return {
    id: account.id,
    name: `${account.account_name} — ৳${balance}`,
  }
}

function getAccountOptions(category, mainAccountType) {
  if (!category) return []

  const accounts = financeAccountStore
    .getAccountsByCategory(category)
    .filter((account) => account.status === 'Active')

  if (category === ACCOUNT_CATEGORIES.MAIN) {
    return mainAccountType
      ? accounts
          .filter((account) => account.account_type === mainAccountType)
          .map(mapAccountOption)
      : []
  }

  return accounts.map(mapAccountOption)
}

const canSelectSingleAccount = computed(() => {
  if (form.account_category === 'main') return Boolean(form.main_account_type)
  return Boolean(form.account_category)
})

const canSelectFromAccount = computed(() => {
  if (form.from_account_category === 'main') return Boolean(form.from_main_account_type)
  return Boolean(form.from_account_category)
})

const canSelectToAccount = computed(() => {
  if (form.to_account_category === 'main') return Boolean(form.to_main_account_type)
  return Boolean(form.to_account_category)
})

const singleAccountOptions = computed(() =>
  getAccountOptions(form.account_category, form.main_account_type)
)

const fromAccountOptions = computed(() =>
  getAccountOptions(form.from_account_category, form.from_main_account_type).filter(
    (option) =>
      !(
        form.from_account_category === form.to_account_category &&
        Number(option.id) === Number(form.to_account_id)
      )
  )
)

const toAccountOptions = computed(() =>
  getAccountOptions(form.to_account_category, form.to_main_account_type).filter(
    (option) =>
      !(
        form.from_account_category === form.to_account_category &&
        Number(option.id) === Number(form.from_account_id)
      )
  )
)

function filterAccountOption(option, query) {
  const haystack = [option?.name, option?.id].filter(Boolean).join(' ').toLowerCase()
  return haystack.includes(query)
}

function setPaymentMode(modeId) {
  activePaymentMode.value = modeId

  const query = { ...route.query, tab: 'transaction_entry' }
  if (modeId === BILLS_PAYABLE_MODE) {
    query.payment_mode = 'bills_payable'
  } else {
    query.payment_mode = 'bills_to_pay'
  }
  router.replace({ query })
}

watch(
  () => [props.initialMode, props.transactionOnly],
  ([mode, transactionOnly]) => {
    if (transactionOnly || mode === TRANSACTION_MODE) {
      activePaymentMode.value = TRANSACTION_MODE
      setTransactionType(form.transaction_type || accountTransactionTypes[0]?.id || 'loan')
      return
    }

    if (mode === BILLS_PAYABLE_MODE) {
      activePaymentMode.value = BILLS_PAYABLE_MODE
      return
    }

    activePaymentMode.value = BILLS_TO_PAY_MODE
  }
)

function setTransactionType(type) {
  form.transaction_type = type

  if (isAdjustmentTransactionType(type)) {
    form.account_category = 'staff'
    form.main_account_type = 'Cash'
    form.account_id = ''
    return
  }

  form.from_account_category = getDefaultFromCategory(type)
  form.from_main_account_type = form.from_account_category === 'main' ? 'Cash' : ''
  form.from_account_id = ''
  form.to_account_category = getDefaultToCategory(type)
  form.to_main_account_type = form.to_account_category === 'main' ? 'Cash' : ''
  form.to_account_id = ''
}

function resetForm() {
  Object.assign(form, createDefaultForm())
}

watch(
  () => form.account_category,
  (category) => {
    form.main_account_type = category === 'main' ? 'Cash' : ''
    form.account_id = ''
  }
)

watch(
  () => form.main_account_type,
  () => {
    form.account_id = ''
  }
)

watch(
  () => form.from_account_category,
  (category) => {
    form.from_main_account_type = category === 'main' ? 'Cash' : ''
    form.from_account_id = ''
  }
)

watch(
  () => form.from_main_account_type,
  () => {
    form.from_account_id = ''
  }
)

watch(
  () => form.to_account_category,
  (category) => {
    form.to_main_account_type = category === 'main' ? 'Cash' : ''
    form.to_account_id = ''
  }
)

watch(
  () => form.to_main_account_type,
  () => {
    form.to_account_id = ''
  }
)

async function handleSubmit() {
  if (!isAdjustmentType.value && !hasExtraAccountSelected.value) {
    await Swal.fire({
      icon: 'warning',
      title: 'Account Required',
      text: "Please select one of Owner's Equity, Asset, or Liabilities account.",
      confirmButtonColor: '#22C55E',
    })
    return
  }

  if (hasExtraAccountSelected.value && !['payment', 'receive'].includes(form.transaction_direction)) {
    await Swal.fire({
      icon: 'warning',
      title: 'Direction Required',
      text: 'Please select Payment or Receive.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  submitLoading.value = true

  const result = await transactionStore.submitTransaction({
    transactionType: form.transaction_type,
    date: form.date,
    amount: form.amount,
    particular: form.particular,
    referenceNo: form.reference_no,
    remarks: form.remarks,
    accountCategory: form.account_category,
    mainAccountType: form.main_account_type,
    accountId: form.account_id,
    fromAccountCategory: form.from_account_category,
    fromMainAccountType: form.from_main_account_type,
    fromAccountId: form.from_account_id,
    toAccountCategory: form.to_account_category,
    toMainAccountType: form.to_main_account_type,
    toAccountId: form.to_account_id,
    assetAccountId: form.asset_account_id,
    liabilitiesAccountId: form.liabilities_account_id,
    ownersEquityAccountId: form.owners_equity_account_id,
    transactionDirection: hasExtraAccountSelected.value
      ? form.transaction_direction
      : undefined,
  })

  submitLoading.value = false

  if (!result.ok) {
    await Swal.fire({
      icon: 'error',
      title: 'Transaction Failed',
      text: result.message,
      confirmButtonColor: '#22C55E',
    })
    return
  }

  await Swal.fire({
    icon: 'success',
    title: 'Transaction Saved',
    text: `${transferLabel} has been recorded successfully.`,
    confirmButtonColor: '#22C55E',
  })

  resetForm()
  emit('saved')
}

onMounted(async () => {
  if (props.transactionOnly) {
    activePaymentMode.value = TRANSACTION_MODE
    setTransactionType(form.transaction_type || accountTransactionTypes[0]?.id || 'loan')
    await financeAccountStore.fetchAllCategories()
    return
  }

  if (!route.query.payment_mode || route.query.payment_mode === 'transaction') {
    setPaymentMode(BILLS_TO_PAY_MODE)
  }

  // Bills modes: parent loads bill summary; BillEntriesPanel loads list data.
})
</script>
