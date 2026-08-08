<template>
  <div class="space-y-6">
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
      <h3 class="mb-1 text-base font-semibold text-gray-800">Select Income Type</h3>
      <p class="mb-4 text-sm text-gray-500">
        Choose client commission or operating income before collecting.
      </p>

      <div class="flex flex-wrap gap-2">
        <button
          v-for="type in incomeTypeTabs"
          :key="type.id"
          type="button"
          class="rounded-lg px-4 py-2 text-sm font-medium transition"
          :class="
            activeIncomeType === type.id
              ? 'bg-primary text-white'
              : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50'
          "
          @click="setIncomeType(type.id)"
        >
          <i :class="[type.icon, 'mr-1.5']"></i>{{ type.label }}
        </button>
      </div>
    </div>

    <BaseForm :onSubmit="handleSubmit">
      <template v-if="isClientIncomeCategory">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <BaseLabel for="collection_date">Collection Date</BaseLabel>
              <BaseInput
                id="collection_date"
                v-model="form.collection_date"
                type="date"
                :required="true"
              />
            </div>

            <div class="space-y-2">
              <BaseLabel for="income_head_id">Income Head</BaseLabel>
              <BaseSelect
                id="income_head_id"
                v-model="form.head_id"
                :options="headOptions"
                placeholder="Select income head"
                :required="true"
                :disabled="!form.category_id"
              />
            </div>

            <div class="space-y-2">
              <BaseLabel for="income_payer_client_id">Select Client</BaseLabel>
              <BaseSearchSelect
                id="income_payer_client_id"
                v-model="form.payer_client_id"
                :options="clientOptions"
                :placeholder="
                  isClientSelectLoading ? 'Loading clients...' : 'Search client by code or name'
                "
                :required="true"
                :disabled="isClientSelectLoading"
              />
            </div>

            <div class="space-y-2">
              <BaseLabel for="income_job_id">Job</BaseLabel>
              <BaseSearchSelect
                id="income_job_id"
                v-model="form.job_id"
                :options="jobOptions"
                :placeholder="isJobSelectLoading ? 'Loading jobs...' : 'Search and select job'"
                :disabled="!form.payer_client_id || isJobSelectLoading"
              />
            </div>
          </div>
        </div>

        <div class="mt-4 flex justify-end">
          <p v-if="candidatesLoading" class="text-sm text-gray-500">
            <i class="fa fa-spinner fa-spin mr-1"></i>
            Loading candidates...
          </p>
        </div>

        <JobInfoCard v-if="loadedJob" class="mt-4" :job="loadedJob" />

        <div
          v-if="form.job_id && !candidatesLoading && !loadedCandidates.length"
          class="mt-4 rounded-lg border border-dashed border-gray-200 bg-gray-50 p-6 text-center text-sm text-gray-500"
        >
          {{ emptyClientCandidatesMessage }}
        </div>

        <div
          v-if="loadedCandidates.length"
          class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(340px,420px)] lg:items-start"
        >
          <SaleCandidatePaymentTable
            title="Client Due Bills"
            sale-price-label="Client Commission (৳)"
            :candidates="loadedCandidates"
            :selected-ids="selectedIds"
            :pay-amounts="payAmounts"
            :sale-prices="salePrices"
            payer-type="client"
            :editable-sale-price="true"
            :show-due-columns="true"
            :pay-amount-readonly="isDueReceiveMethod"
            @toggle-all="toggleAll"
            @toggle-row="toggleRow"
            @update-pay-amount="updatePayAmount"
            @update-sale-price="updateSalePrice"
          />

          <IncomeReceiveDetailsPanel
            :amount-label="'Total Receive Amount (BDT)'"
            :amount-value="totalSelectedAmount"
            :amount-editable="false"
            :payment-method="form.payment_method"
            :receive-method-options="receiveMethodOptions"
            :requires-main-account="requiresMainAccount"
            :main-account-options="mainAccountOptions"
            :receive-account-id="form.receive_account_id"
            :particular="form.particular"
            :particular-input-key="particularInputKey"
            :reference-no="form.reference_no"
            :remarks="form.remarks"
            :show-additional-info="showAdditionalInfo"
            :submit-loading="submitLoading"
            :submit-label="collectSubmitLabel"
            @update:payment-method="form.payment_method = $event"
            @update:receive-account-id="form.receive_account_id = $event"
            @update:particular="onParticularInput"
            @update:reference-no="form.reference_no = $event"
            @update:remarks="form.remarks = $event"
            @toggle-additional-info="showAdditionalInfo = !showAdditionalInfo"
            @reset-particular="resetParticular"
            @reset="resetForm"
          />
        </div>
      </template>

      <template v-else>
        <div
          class="grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(340px,420px)] lg:items-start"
        >
          <div class="space-y-4">
            <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
              <div
                class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 bg-gradient-to-r from-slate-50 to-white px-4 py-3.5"
              >
                <div class="flex items-center gap-3">
                  <span
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-800 text-white shadow-sm"
                  >
                    <i :class="[activeIncomeTypeMeta.icon, 'text-sm']"></i>
                  </span>
                  <div>
                    <h3 class="text-sm font-semibold tracking-tight text-slate-900">
                      {{ activeIncomeTypeMeta.label }}
                    </h3>
                    <p class="mt-0.5 text-xs text-slate-500">Configure income head and linked account</p>
                  </div>
                </div>
                <span
                  class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"
                >
                  Collect Income
                </span>
              </div>

              <div class="space-y-4 p-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <BaseLabel for="simple_collection_date">Collection Date</BaseLabel>
                    <BaseInput
                      id="simple_collection_date"
                      v-model="form.collection_date"
                      type="date"
                      :required="true"
                    />
                  </div>

                  <div class="space-y-2">
                    <BaseLabel for="simple_income_head_id">Income Head</BaseLabel>
                    <BaseSelect
                      id="simple_income_head_id"
                      v-model="form.head_id"
                      :options="headOptions"
                      placeholder="Select income head"
                      :required="true"
                      :disabled="!form.category_id"
                    />
                  </div>
                </div>

                <ExpenseHeadLinkedAccountsPanel
                  v-if="linkedAccountGroups.length"
                  :groups="linkedAccountGroups"
                  :selected-category="form.linked_account_category"
                  :selected-account-id="form.linked_account_id"
                  class="!col-span-1 !border-slate-200/80 !bg-slate-50/50"
                  @select="selectLinkedAccount"
                />

                <div
                  v-if="selectedHead"
                  class="overflow-hidden rounded-xl border border-violet-100 bg-gradient-to-br from-violet-50 to-white shadow-sm"
                >
                  <div class="border-b border-violet-100/80 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-violet-700/80">
                      Income Summary
                    </p>
                  </div>
                  <dl class="divide-y divide-violet-100/80 px-4 text-sm">
                    <div class="flex justify-between gap-3 py-3">
                      <dt class="text-slate-500">Income Head</dt>
                      <dd class="text-right font-medium text-slate-900">{{ selectedHead.name }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 py-3">
                      <dt class="text-slate-500">Base Price</dt>
                      <dd class="font-medium tabular-nums text-slate-900">
                        {{ formatCurrency(selectedHead.base_price) }}
                      </dd>
                    </div>
                    <div v-if="selectedLinkedAccount" class="flex justify-between gap-3 py-3">
                      <dt class="text-slate-500">Linked Account</dt>
                      <dd class="max-w-[60%] text-right font-medium text-slate-900">
                        {{ selectedLinkedAccount.account?.name || '—' }}
                      </dd>
                    </div>
                    <div v-if="linkedAccountGroups.length && !form.linked_account_id" class="py-3">
                      <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                        Select a linked account to post this income collection.
                      </p>
                    </div>
                  </dl>
                </div>

                <div
                  v-else
                  class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500"
                >
                  Select an income head to continue collection.
                </div>
              </div>
            </div>
          </div>

          <IncomeReceiveDetailsPanel
            v-if="selectedHead"
            :amount-label="'Receive Amount (BDT)'"
            :amount-value="Number(form.amount) || 0"
            :amount-editable="true"
            :amount-model="form.amount"
            :payment-method="form.payment_method"
            :receive-method-options="receiveMethodOptions"
            :requires-main-account="requiresMainAccount"
            :main-account-options="mainAccountOptions"
            :receive-account-id="form.receive_account_id"
            :particular="form.particular"
            :particular-input-key="particularInputKey"
            :reference-no="form.reference_no"
            :remarks="form.remarks"
            :show-additional-info="showAdditionalInfo"
            :submit-loading="submitLoading"
            :submit-label="collectSubmitLabel"
            @update:amount="form.amount = $event"
            @update:payment-method="form.payment_method = $event"
            @update:receive-account-id="form.receive_account_id = $event"
            @update:particular="onParticularInput"
            @update:reference-no="form.reference_no = $event"
            @update:remarks="form.remarks = $event"
            @toggle-additional-info="showAdditionalInfo = !showAdditionalInfo"
            @reset-particular="resetParticular"
            @reset="resetForm"
          />

          <div
            v-else
            class="overflow-hidden rounded-xl border border-dashed border-slate-200 bg-slate-50/80 p-8 text-center text-sm text-slate-500 lg:sticky lg:top-4"
          >
            <i class="fa fa-arrow-left mb-2 text-slate-400"></i>
            <p>Select an income head to open receive details.</p>
          </div>
        </div>
      </template>
    </BaseForm>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch, nextTick } from 'vue'
import { storeToRefs } from 'pinia'
import BaseForm from '@/shared/components/base/BaseForm.vue'
import BaseSearchSelect from '@/shared/components/base/BaseSearchSelect.vue'
import ExpenseHeadLinkedAccountsPanel from './ExpenseHeadLinkedAccountsPanel.vue'
import IncomeReceiveDetailsPanel from './IncomeReceiveDetailsPanel.vue'
import SaleCandidatePaymentTable from './SaleCandidatePaymentTable.vue'
import JobInfoCard from '../agentBillParts/JobInfoCard.vue'
import { useIncomeCategoryStore } from '@/finance/store/incomeCategoryStore'
import { useIncomeHeadStore } from '@/finance/store/incomeHeadStore'
import { useIncomeCollectionStore } from '@/finance/store/incomeCollectionStore'
import { useSaleEntryStore } from '@/finance/store/saleEntryStore'
import { useAccountStore } from '@/finance/store/accountStore'
import { useAgentAccountStore } from '@/finance/store/agentAccountStore'
import { useIncomeAccountsStore } from '@/finance/store/incomeAccountsStore'
import { usePartyAccountsStore } from '@/finance/store/partyAccountsStore'
import { useJobListStore } from '@/finance/store/jobListStore'
import { formatCurrency } from '@/finance/utils/billUtils'
import { toast } from '@/shared/config/toastConfig'
import {
  CLIENT_INCOME_CATEGORY_CODE,
  OTHER_INCOME_CATEGORY_CODE,
  findCategoryByCode,
  isCategoryCode,
} from '@/finance/data/incomeCategoryCodes'
import { paymentMethods } from '@/finance/data/paymentData'
import { formatSaleJobSelectOption } from '@/finance/utils/jobListMapper'
import { hasJobPayer } from '@/modules/job/utils/jobPayerUtils'
import {
  getAccountCategoryLabel,
  normalizeLinkedAccounts,
} from '@/finance/data/incomeHeadAccountLinkData'
import {
  findLinkedAccountSelection,
  getAccountsForLinkedCategory,
} from '@/finance/utils/linkedAccountOptions'

const emit = defineEmits(['saved'])

const categoryStore = useIncomeCategoryStore()
const headStore = useIncomeHeadStore()
const collectionStore = useIncomeCollectionStore()
const saleEntryStore = useSaleEntryStore()
const accountStore = useAccountStore()
const agentAccountStore = useAgentAccountStore()
const incomeAccountsStore = useIncomeAccountsStore()
const partyAccountsStore = usePartyAccountsStore()
const jobListStore = useJobListStore()
const { applicationsByJob } = storeToRefs(jobListStore)

async function ensureSalePayerClientAccounts() {
  await Promise.all([
    partyAccountsStore.fetchAccounts('client'),
    jobListStore.fetchSalePayerClients(true),
  ])
  await partyAccountsStore.ensureAccountsForMasterIds(
    'client',
    jobListStore.getSalePayerClientMasterIds()
  )
}

const submitLoading = ref(false)
const candidatesLoading = ref(false)
const showAdditionalInfo = ref(false)
const particularManuallyEdited = ref(false)
const particularInputKey = ref(0)
const allMatchingCandidatesPaid = ref(false)
const allMatchingOnReceivable = ref(false)
const loadedJob = ref(null)
const loadedCandidates = ref([])
const selectedIds = ref([])
const payAmounts = reactive({})
const salePrices = reactive({})

const createDefaultForm = () => ({
  collection_date: new Date().toISOString().slice(0, 10),
  category_id: '',
  head_id: '',
  amount: '',
  payment_method: 'cash',
  particular: '',
  reference_no: '',
  remarks: '',
  receive_account_id: '',
  linked_account_category: '',
  linked_account_id: '',
  payer_client_id: '',
  job_id: '',
})

const form = reactive(createDefaultForm())

const incomeTypeTabs = [
  {
    id: 'client_income',
    label: 'Client Income',
    icon: 'fa fa-building',
    code: CLIENT_INCOME_CATEGORY_CODE,
  },
  {
    id: 'other_income',
    label: 'Operating Income',
    icon: 'fa fa-plus-circle',
    code: OTHER_INCOME_CATEGORY_CODE,
  },
]

const activeIncomeType = computed(() => {
  if (isCategoryCode(selectedCategory.value, OTHER_INCOME_CATEGORY_CODE)) return 'other_income'
  return 'client_income'
})

const activeIncomeTypeMeta = computed(() => {
  return incomeTypeTabs.find((item) => item.id === activeIncomeType.value) ?? incomeTypeTabs[0]
})

const collectSubmitLabel = computed(() => {
  if (activeIncomeType.value === 'other_income') return 'Collect Operating Income'
  return 'Collect Client Income'
})

function isActiveStatus(status) {
  return String(status || '').toLowerCase() === 'active'
}

function setIncomeType(typeId) {
  const tab = incomeTypeTabs.find((item) => item.id === typeId)
  if (!tab) return

  const category = findCategoryByCode(categoryStore.categories, tab.code)
  if (!category || !isActiveStatus(category.status)) return

  form.category_id = String(category.id)
}

const headOptions = computed(() =>
  headStore.heads
    .filter(
      (head) =>
        isActiveStatus(head.status) &&
        (!form.category_id || Number(head.category_id) === Number(form.category_id))
    )
    .map((head) => ({
      id: head.id,
      name: `${head.name} (${formatCurrency(head.base_price)})`,
    }))
)

const clientOptions = computed(() => {
  const allowedMasterIds = new Set(
    jobListStore.getSalePayerClientMasterIds().map((id) => Number(id))
  )

  return partyAccountsStore
    .getAccounts('client')
    .filter((account) => {
      if (account.status !== 'Active') return false
      if (!allowedMasterIds.size) return true
      const masterId = Number(account.client_id ?? account.entity_id)
      return masterId && allowedMasterIds.has(masterId)
    })
    .map((account) => ({
      id: account.id,
      name: `${account.client_code} - ${account.client_name}`,
    }))
})

const isClientSelectLoading = computed(
  () =>
    jobListStore.isLoadingSalePayerClients ||
    jobListStore.isLoadingPaymentResponsibilityJobs('client')
)

const selectedClientPayer = computed(() => {
  if (!form.payer_client_id) return null
  return partyAccountsStore.getAccount('client', form.payer_client_id)
})

const jobOptions = computed(() => {
  if (!isClientIncomeCategory.value || !selectedClientPayer.value) return []

  const masterClientId = Number(
    selectedClientPayer.value.client_id ?? selectedClientPayer.value.entity_id
  )
  if (!masterClientId) return []

  return jobListStore.getSaleJobOptionsForClient(masterClientId).map(formatSaleJobSelectOption)
})

const isJobSelectLoading = computed(
  () =>
    jobListStore.isLoadingJobs || jobListStore.isLoadingPaymentResponsibilityJobs('client')
)

const receiveMethodOptions = paymentMethods.map((method) => ({
  id: method.id,
  name: method.name,
}))

const isDueReceiveMethod = computed(
  () => String(form.payment_method || '').toLowerCase() === 'due'
)

const requiresMainAccount = computed(() =>
  ['cash', 'bank'].includes(String(form.payment_method || '').toLowerCase())
)

const mainAccountOptions = computed(() => {
  const method = String(form.payment_method || '').toLowerCase()
  const accountType = method === 'bank' ? 'Bank' : method === 'cash' ? 'Cash' : ''

  return accountStore
    .getActiveAccounts()
    .filter((account) => {
      if (account.status !== 'Active') return false
      if (!accountType) return true
      return account.account_type === accountType
    })
    .map((account) => {
      const balance = formatCurrency(account.balance ?? account.current_balance ?? 0)
      return {
        id: account.id,
        name: `${account.account_type} — ${account.account_name} (${account.account_label}) — ${balance}`,
      }
    })
})

function ensureDefaultReceiveAccount() {
  if (!requiresMainAccount.value) {
    form.receive_account_id = ''
    return
  }

  const options = mainAccountOptions.value
  if (!options.length) {
    form.receive_account_id = ''
    return
  }

  const stillValid = options.some(
    (option) => String(option.id) === String(form.receive_account_id)
  )
  if (!stillValid) {
    form.receive_account_id = options[0].id
  }
}

watch(
  () => form.payment_method,
  () => {
    ensureDefaultReceiveAccount()
    if (isDueReceiveMethod.value) {
      syncPayAmountsToDue()
    }
  }
)

function ensureDefaultIncomeCategory() {
  if (form.category_id) return

  const clientCategory = findCategoryByCode(
    categoryStore.categories,
    CLIENT_INCOME_CATEGORY_CODE
  )
  if (!clientCategory || !isActiveStatus(clientCategory.status)) return

  form.category_id = String(clientCategory.id)
}

watch(
  mainAccountOptions,
  () => {
    ensureDefaultReceiveAccount()
  },
  { immediate: true }
)

const selectedHead = computed(
  () => headStore.heads.find((head) => Number(head.id) === Number(form.head_id)) ?? null
)

const selectedCategory = computed(
  () =>
    categoryStore.categories.find((category) => Number(category.id) === Number(form.category_id)) ??
    null
)

const isClientIncomeCategory = computed(() =>
  isCategoryCode(selectedCategory.value, CLIENT_INCOME_CATEGORY_CODE)
)

const emptyClientCandidatesMessage = computed(() => {
  if (allMatchingOnReceivable.value) {
    return 'Outstanding due bills for this job are listed under Bills Receivable. Settle them there with cash or bank.'
  }

  if (allMatchingCandidatesPaid.value) {
    return 'All matching candidates for this job are already fully paid for client commission.'
  }
  return 'No candidates found for this job with client payment responsibility.'
})

const totalSelectedAmount = computed(() =>
  selectedIds.value.reduce((sum, id) => sum + (Number(payAmounts[id]) || 0), 0)
)

const linkedAccountStores = {
  expenseCostAccountsStore: {
    getAccountByHeadId: () => null,
    getAccount: () => null,
  },
  incomeAccountsStore,
  accountStore,
  partyAccountsStore,
  agentAccountStore,
}

const linkedAccountGroups = computed(() => {
  if (!selectedHead.value || isClientIncomeCategory.value) return []

  return normalizeLinkedAccounts(selectedHead.value.linked_accounts).map((link) => {
    const accounts = getAccountsForLinkedCategory(
      link.account_category,
      selectedHead.value.id,
      linkedAccountStores,
      formatCurrency,
      selectedHead.value
    )

    return {
      category: link.account_category,
      label: getAccountCategoryLabel(link.account_category),
      accounts,
    }
  })
})

const selectedLinkedAccount = computed(() =>
  findLinkedAccountSelection(
    linkedAccountGroups.value,
    form.linked_account_category,
    form.linked_account_id
  )
)

function selectLinkedAccount(category, accountId) {
  form.linked_account_category = category
  form.linked_account_id = accountId ? String(accountId) : ''
}

function applyClientAsLinkedAccount() {
  if (!isClientIncomeCategory.value || !selectedClientPayer.value) return

  form.linked_account_category = 'client'
  form.linked_account_id = String(selectedClientPayer.value.id)
}

function clearJobCandidates() {
  form.job_id = ''
  loadedJob.value = null
  loadedCandidates.value = []
  selectedIds.value = []
  allMatchingCandidatesPaid.value = false
  allMatchingOnReceivable.value = false
  Object.keys(payAmounts).forEach((key) => delete payAmounts[key])
  Object.keys(salePrices).forEach((key) => delete salePrices[key])
}

function syncAmountFromCandidates() {
  if (!isClientIncomeCategory.value) return
  form.amount = selectedIds.value.length ? String(totalSelectedAmount.value) : ''
}

const generatedParticular = computed(() => {
  if (!selectedHead.value) return ''

  const categoryName = selectedCategory.value?.name || ''
  const headName = selectedHead.value.name
  const base = categoryName ? `${categoryName} - ${headName}` : headName

  if (isClientIncomeCategory.value && loadedJob.value && selectedIds.value.length) {
    return `${base} - ${loadedJob.value.job_code} (${selectedIds.value.length} candidates)`
  }

  return base
})

watch(
  generatedParticular,
  (value) => {
    if (!particularManuallyEdited.value) {
      form.particular = value
    }
  },
  { immediate: true }
)

function onParticularInput(value) {
  form.particular = value
  if (!isResettingParticular.value) {
    particularManuallyEdited.value = true
  }
}

const isResettingParticular = ref(false)

async function resetParticular() {
  isResettingParticular.value = true
  particularManuallyEdited.value = false
  form.particular = generatedParticular.value
  particularInputKey.value += 1
  await nextTick()
  isResettingParticular.value = false
}

function syncParticular() {
  if (!particularManuallyEdited.value) {
    form.particular = generatedParticular.value
  }
}

function resolveCandidateSalePrice(candidate) {
  if (!candidate) return 0
  if (salePrices[candidate.id] !== undefined && salePrices[candidate.id] !== null) {
    return Number(salePrices[candidate.id]) || 0
  }
  return Number(candidate.sale_price) || 0
}

function resolveCandidateDue(candidate) {
  const salePrice = resolveCandidateSalePrice(candidate)
  const paid = Number(candidate?.collected_amount ?? 0) || 0
  return Math.max(0, Math.round((salePrice - paid) * 100) / 100)
}

function syncPayAmountsToDue() {
  loadedCandidates.value.forEach((candidate) => {
    payAmounts[candidate.id] = resolveCandidateDue(candidate)
  })
  syncAmountFromCandidates()
}

function toggleRow(id) {
  if (selectedIds.value.includes(id)) {
    selectedIds.value = selectedIds.value.filter((item) => item !== id)
  } else {
    selectedIds.value.push(id)
    const candidate = loadedCandidates.value.find((item) => item.id === id)
    if (candidate && salePrices[id] === undefined) {
      salePrices[id] = Number(candidate.sale_price) || 0
    }
    if (isDueReceiveMethod.value) {
      payAmounts[id] = resolveCandidateDue(candidate)
    } else if (!payAmounts[id]) {
      payAmounts[id] = candidate ? Number(candidate.remaining_amount) || 0 : 0
    }
  }
  syncAmountFromCandidates()
  syncParticular()
}

function toggleAll() {
  if (selectedIds.value.length === loadedCandidates.value.length) {
    selectedIds.value = []
  } else {
    selectedIds.value = loadedCandidates.value.map((candidate) => candidate.id)
    loadedCandidates.value.forEach((candidate) => {
      if (salePrices[candidate.id] === undefined) {
        salePrices[candidate.id] = Number(candidate.sale_price) || 0
      }
      if (isDueReceiveMethod.value) {
        payAmounts[candidate.id] = resolveCandidateDue(candidate)
      } else if (!payAmounts[candidate.id]) {
        payAmounts[candidate.id] = Number(candidate.remaining_amount) || 0
      }
    })
  }
  syncAmountFromCandidates()
  syncParticular()
}

function updateSalePrice({ candidateId, value }) {
  const candidate = loadedCandidates.value.find((item) => item.id === candidateId)
  if (!candidate) return
  // Locked after any previous payment (client commission).
  if ((Number(candidate.collected_amount) || 0) > 0) return

  const previousDue = resolveCandidateDue(candidate)
  const previousPay = Number(payAmounts[candidateId] || 0)
  const price = Math.max(0, Number(value) || 0)

  salePrices[candidateId] = price
  candidate.sale_price = price
  candidate.charge = price
  candidate.application_price = price

  const nextDue = resolveCandidateDue(candidate)
  candidate.remaining_amount = nextDue

  if (isDueReceiveMethod.value) {
    payAmounts[candidateId] = nextDue
  } else if (Math.abs(previousPay - previousDue) < 0.01 || previousPay === 0) {
    payAmounts[candidateId] = nextDue
  }

  syncAmountFromCandidates()
}

function updatePayAmount({ candidateId, value }) {
  if (isDueReceiveMethod.value) {
    const candidate = loadedCandidates.value.find((item) => item.id === candidateId)
    payAmounts[candidateId] = resolveCandidateDue(candidate)
    syncAmountFromCandidates()
    return
  }

  payAmounts[candidateId] = Math.max(0, Number(value) || 0)
  syncAmountFromCandidates()
}

async function loadCandidates() {
  candidatesLoading.value = true
  loadedJob.value = null
  loadedCandidates.value = []
  selectedIds.value = []
  allMatchingCandidatesPaid.value = false
  Object.keys(payAmounts).forEach((key) => delete payAmounts[key])
  Object.keys(salePrices).forEach((key) => delete salePrices[key])

  try {
    if (!form.job_id || !selectedClientPayer.value) return

    await Promise.all([
      jobListStore.fetchJobList(true),
      jobListStore.fetchApplicationsForJob(form.job_id, true),
      collectionStore.fetchCollections({ force: true, page: 1, perPage: 100 }),
    ])

    const job = jobListStore.getJob(form.job_id)
    if (!job) return

    const masterClientId = Number(
      selectedClientPayer.value.client_id ?? selectedClientPayer.value.entity_id
    )

    const applications = [...(applicationsByJob.value[Number(form.job_id)] ?? [])].filter(
      (application) =>
        hasJobPayer(application.payment_responsibility, 'client') &&
        (!masterClientId || Number(application.client_id) === masterClientId)
    )

    const jobCommission = Number(job.client_commission_per_candidate) || 0

    const resolveCandidateFinancials = (application) => {
      const collectedAmount = collectionStore.getCollectedAmountForCandidate(application.id)
      const storedSalePrice = collectionStore.getLatestSalePriceForCandidate(application.id)
      const salePrice = storedSalePrice > 0 ? storedSalePrice : jobCommission
      const remaining = Math.max(0, Math.round((salePrice - collectedAmount) * 100) / 100)
      return { salePrice, collectedAmount, remaining }
    }

    const unpaidApplications = applications.filter((application) => {
      const { remaining } = resolveCandidateFinancials(application)
      const hasDue = collectionStore.candidateHasDueReceivable(application.id)
      return (remaining > 0 || (resolveCandidateFinancials(application).salePrice <= 0 && resolveCandidateFinancials(application).collectedAmount <= 0)) && !hasDue
    })

    const outstandingDueCount = applications.filter((application) =>
      collectionStore.candidateHasDueReceivable(application.id)
    ).length

    allMatchingOnReceivable.value =
      applications.length > 0 && unpaidApplications.length === 0 && outstandingDueCount > 0

    allMatchingCandidatesPaid.value =
      applications.length > 0 && unpaidApplications.length === 0 && outstandingDueCount === 0

    loadedJob.value = job
    loadedCandidates.value = unpaidApplications.map((application) => {
      const { salePrice, collectedAmount, remaining } = resolveCandidateFinancials(application)
      return {
        ...application,
        sale_price: salePrice,
        charge: salePrice,
        application_price: salePrice,
        collected_amount: collectedAmount,
        remaining_amount: remaining,
      }
    })
    selectedIds.value = loadedCandidates.value.map((candidate) => candidate.id)
    loadedCandidates.value.forEach((candidate) => {
      salePrices[candidate.id] = Number(candidate.sale_price) || 0
      payAmounts[candidate.id] = Number(candidate.remaining_amount) || 0
    })

    syncAmountFromCandidates()
    syncParticular()
  } finally {
    candidatesLoading.value = false
  }
}

function resetForm() {
  Object.assign(form, createDefaultForm())
  clearJobCandidates()
  showAdditionalInfo.value = false
  particularManuallyEdited.value = false
  particularInputKey.value += 1
  ensureDefaultReceiveAccount()
  ensureDefaultIncomeCategory()
}

watch(
  () => form.category_id,
  async (categoryId, previousCategoryId) => {
    if (categoryId !== previousCategoryId) {
      form.head_id = ''
      form.linked_account_category = ''
      form.linked_account_id = ''
      form.payer_client_id = ''
      form.amount = ''
      form.particular = ''
      particularManuallyEdited.value = false
      clearJobCandidates()
    }

    if (!categoryId) return

    await incomeAccountsStore.ensureAccountsForCategoryId(categoryId)

    const category = categoryStore.categories.find(
      (item) => Number(item.id) === Number(categoryId)
    )
    if (isCategoryCode(category, CLIENT_INCOME_CATEGORY_CODE)) {
      await Promise.all([
        ensureSalePayerClientAccounts(),
        jobListStore.fetchJobList(true),
      ])
    }

    const headsForCategory = headStore.heads.filter(
      (head) =>
        isActiveStatus(head.status) && Number(head.category_id) === Number(categoryId)
    )

    if (headsForCategory.length === 1) {
      form.head_id = String(headsForCategory[0].id)
    }
  }
)

watch(
  () => form.payer_client_id,
  () => {
    applyClientAsLinkedAccount()
    clearJobCandidates()
  }
)

watch(
  () => form.job_id,
  async (jobId) => {
    if (!isClientIncomeCategory.value) return
    if (!jobId) {
      loadedJob.value = null
      loadedCandidates.value = []
      selectedIds.value = []
      allMatchingCandidatesPaid.value = false
      Object.keys(payAmounts).forEach((key) => delete payAmounts[key])
      Object.keys(salePrices).forEach((key) => delete salePrices[key])
      syncAmountFromCandidates()
      return
    }
    await loadCandidates()
  }
)

watch(
  () => form.head_id,
  (headId) => {
    if (!isClientIncomeCategory.value) {
      form.linked_account_category = ''
      form.linked_account_id = ''
    }

    const head = headStore.heads.find((item) => Number(item.id) === Number(headId))
    if (!head) return

    if (!isClientIncomeCategory.value) {
      form.amount = head.base_price ? String(head.base_price) : ''
    }

    syncParticular()

    if (isClientIncomeCategory.value) {
      applyClientAsLinkedAccount()
      return
    }

    const groups = linkedAccountGroups.value
    const single = groups.find((group) => group.accounts.length === 1)
    if (groups.length === 1 && single) {
      selectLinkedAccount(single.category, single.accounts[0].id)
      return
    }

    const incomeAccount = incomeAccountsStore.getAccountByHeadId(form.category_id, headId)
    if (incomeAccount && !groups.length) {
      const incomeType = incomeAccountsStore.getIncomeTypeByCategoryId(form.category_id)
      if (incomeType) {
        selectLinkedAccount(incomeType, incomeAccount.id)
      }
    }
  }
)

async function handleSubmit() {
  if (!form.category_id) {
    toast.error('Please select an income category.')
    return
  }
  if (!form.head_id) {
    toast.error('Please select an income head.')
    return
  }
  if (isClientIncomeCategory.value && !form.payer_client_id) {
    toast.error('Please select a client.')
    return
  }
  if (isClientIncomeCategory.value && !form.job_id) {
    toast.error('Please select a job.')
    return
  }
  if (isClientIncomeCategory.value && !selectedIds.value.length) {
    toast.error('Please select at least one candidate.')
    return
  }
  if (requiresMainAccount.value && !form.receive_account_id) {
    toast.error('Please select a main account to receive income.')
    return
  }
  if (!Number(form.amount) || Number(form.amount) <= 0) {
    toast.error('Please enter a valid amount.')
    return
  }
  if (linkedAccountGroups.value.length && !form.linked_account_id) {
    toast.error('Please select a linked account for this income head.')
    return
  }

  if (isClientIncomeCategory.value) {
    applyClientAsLinkedAccount()
    syncAmountFromCandidates()
  }

  const linkedName = isClientIncomeCategory.value
    ? selectedClientPayer.value
      ? `${selectedClientPayer.value.client_code} — ${selectedClientPayer.value.client_name}`
      : undefined
    : selectedLinkedAccount.value?.account?.name

  const linkedType = isClientIncomeCategory.value
    ? 'Client'
    : selectedLinkedAccount.value?.account?.accountType

  const candidates = isClientIncomeCategory.value
    ? selectedIds.value
        .map((candidateId) => {
          const candidate = loadedCandidates.value.find((item) => item.id === candidateId)
          if (!candidate) return null
          return {
            application_id: candidate.id,
            candidate_name: candidate.candidate_name,
            passport_no: candidate.passport_no,
            sale_price: Number(salePrices[candidateId] ?? candidate.sale_price) || 0,
            amount: Number(payAmounts[candidateId]) || 0,
          }
        })
        .filter(Boolean)
    : undefined

  submitLoading.value = true
  try {
    const result = await collectionStore.collectIncome({
      category_id: Number(form.category_id),
      head_id: Number(form.head_id),
      amount: Number(form.amount),
      collection_date: form.collection_date,
      payment_method: form.payment_method || 'cash',
      particular: form.particular?.trim() || undefined,
      reference_no: form.reference_no?.trim() || undefined,
      remarks: form.remarks?.trim() || undefined,
      receive_account_id: requiresMainAccount.value ? Number(form.receive_account_id) : undefined,
      linked_account_category: form.linked_account_category || undefined,
      linked_account_id: form.linked_account_id ? Number(form.linked_account_id) : undefined,
      linked_account_name: linkedName,
      linked_account_type: linkedType,
      job_id: isClientIncomeCategory.value ? Number(form.job_id) : undefined,
      job_code: loadedJob.value?.job_code,
      job_title: loadedJob.value?.job_title || loadedJob.value?.job_name,
      client_name: selectedClientPayer.value?.client_name,
      candidates,
    })

    if (!result.ok) {
      toast.error(result.message)
      return
    }

    toast.success(
      isDueReceiveMethod.value
        ? 'PL Income due recorded. Settle it from Bills Receivable with cash or bank.'
        : 'PL Income collected successfully.'
    )

    if (isDueReceiveMethod.value) {
      await saleEntryStore.fetchReceivableBills(true)
    }

    resetForm()
    emit('saved')
  } finally {
    submitLoading.value = false
  }
}

onMounted(async () => {
  await Promise.all([
    categoryStore.fetchCategories(true),
    headStore.fetchHeads({ force: true, page: 1, perPage: 300 }),
    accountStore.fetchActiveAccounts(true),
    incomeAccountsStore.fetchAccounts('client_income'),
    incomeAccountsStore.fetchAccounts('other_income'),
    agentAccountStore.fetchAccounts(),
    partyAccountsStore.fetchAccounts('vendor'),
    partyAccountsStore.fetchAccounts('principal'),
    partyAccountsStore.fetchAccounts('staff'),
    jobListStore.fetchJobList(),
    ensureSalePayerClientAccounts(),
    collectionStore.fetchCollections({ force: true, page: 1, perPage: 100 }),
  ])
  ensureDefaultReceiveAccount()
  ensureDefaultIncomeCategory()
})
</script>
