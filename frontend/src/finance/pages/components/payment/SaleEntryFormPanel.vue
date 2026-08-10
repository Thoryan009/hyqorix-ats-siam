<template>
  <div class="space-y-6">
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
      <h3 class="mb-1 text-base font-semibold text-gray-800">Select Payer</h3>
      <p class="mb-4 text-sm text-gray-500">Choose who is paying before entering the sale details.</p>

      <div class="mb-4 flex flex-wrap gap-2">
        <button
          v-for="type in payerTypes"
          :key="type.id"
          type="button"
          class="rounded-lg px-4 py-2 text-sm font-medium transition"
          :class="
            form.payer_type === type.id
              ? 'bg-primary text-white'
              : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50'
          "
          @click="setPayerType(type.id)"
        >
          <i :class="[type.icon, 'mr-1.5']"></i>{{ type.label }}
        </button>
      </div>

      <div v-if="form.payer_type === 'agent'" class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="space-y-2">
          <BaseLabel for="sale_agent_id">Payer Agent</BaseLabel>
          <BaseSearchSelect
            id="sale_agent_id"
            v-model="form.agent_id"
            :options="agentOptions"
            :placeholder="isAgentSelectLoading ? 'Loading agents...' : 'Search agent by ID or name'"
            :disabled="isAgentSelectLoading"
            :filter-fn="filterAgentOption"
          />
        </div>
      </div>

      <div v-else-if="form.payer_type === 'client'" class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="space-y-2">
          <BaseLabel for="sale_client_id">Payer Client</BaseLabel>
          <BaseSelect
            id="sale_client_id"
            v-model="form.payer_client_id"
            :options="clientOptions"
            :placeholder="isClientSelectLoading ? 'Loading clients...' : 'Select client'"
            :disabled="isClientSelectLoading"
          />
        </div>
        <div v-if="selectedClientPayer" class="flex items-end">
          <p class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-900">
            Client Balance: {{ formatCurrency(selectedClientPayer.balance) }}
          </p>
        </div>
      </div>

      <div
        v-else-if="form.payer_type === 'candidate'"
        class="rounded-lg border border-violet-200 bg-violet-50 px-3 py-2 text-sm text-violet-900"
      >
        <i class="fa fa-info-circle mr-1"></i>
        Each selected candidate will be recorded as the payer for their own payment row.
      </div>
    </div>

    <template v-if="canProceedWithSale">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="space-y-2">
          <BaseLabel for="sale_job_id">Job</BaseLabel>
          <BaseSearchSelect
            id="sale_job_id"
            v-model="form.job_id"
            :options="jobOptions"
            :placeholder="isJobSelectLoading ? 'Loading jobs...' : 'Search and select job'"
            :disabled="!canSelectJob || isJobSelectLoading"
          />
        </div>
        <div class="space-y-2">
          <BaseLabel for="sale_entry_date">Entry Date</BaseLabel>
          <BaseInput id="sale_entry_date" v-model="form.entry_date" type="date" />
        </div>
        <div class="space-y-2">
          <BaseLabel>Entry No (Preview)</BaseLabel>
          <BaseInput :model-value="previewEntryNo" disabled />
        </div>
      </div>

      <div class="flex justify-end">
        <p
          v-if="candidatesLoading"
          class="text-sm text-gray-500"
        >
          <i class="fa fa-spinner fa-spin mr-1"></i>
          Loading candidates...
        </p>
      </div>

      <JobInfoCard v-if="loadedJob" :job="loadedJob" />

      <!-- Agent Balance (temporarily hidden)
      <div
        v-if="selectedAgent && form.payer_type === 'agent'"
        class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900"
      >
        <span class="font-semibold">Agent Balance:</span>
        {{ formatCurrency(selectedAgent.balance) }}
      </div>
      -->

      <div
        v-if="form.payer_type === 'agent' && form.agent_id && !selectedAgent"
        class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
      >
        <i class="fa fa-info-circle mr-1"></i>
        No finance agent account found for this agent. Cash payment can still be recorded after creating
        an agent account.
      </div>

      <div
        v-if="form.job_id && !candidatesLoading && !loadedCandidates.length"
        class="rounded-lg border border-dashed border-gray-200 p-6 text-center text-sm text-gray-500"
      >
        {{ emptyCandidatesMessage }}
      </div>

      <div
        v-if="loadedCandidates.length"
        class="grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(340px,420px)] lg:items-start"
      >
        <SaleCandidatePaymentTable
          :title="candidateTableTitle"
          :candidates="loadedCandidates"
          :selected-ids="selectedIds"
          :pay-amounts="payAmounts"
          :sale-prices="salePrices"
          :payer-type="form.payer_type"
          :editable-sale-price="supportsDueBillAdjustments"
          :show-due-columns="supportsDueBillAdjustments"
          :pay-amount-readonly="isDueReceiveMethod"
          @toggle-all="toggleAll"
          @toggle-row="toggleRow"
          @update-pay-amount="updatePayAmount"
          @update-sale-price="updateSalePrice"
        />

        <div
          class="overflow-hidden rounded-xl border border-emerald-200/80 bg-white shadow-sm lg:sticky lg:top-4"
        >
          <div
            class="border-b border-emerald-200/70 bg-gradient-to-r from-emerald-600 to-emerald-700 px-4 py-3.5 text-white"
          >
            <div class="flex items-center gap-3">
              <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 ring-1 ring-white/25"
              >
                <i class="fa fa-money text-sm"></i>
              </span>
              <h3 class="text-sm font-semibold tracking-tight">Receive Details</h3>
            </div>
          </div>

          <div class="space-y-4 p-4">
            <div
              class="rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white px-4 py-3.5 shadow-sm"
            >
              <BaseLabel
                class-name="!mb-1.5 !text-xs !font-medium !uppercase !tracking-wide !text-emerald-700/80"
              >
                Total Receive Amount (BDT)
              </BaseLabel>
              <p class="text-2xl font-bold tabular-nums tracking-tight text-emerald-800">
                {{ formatCurrency(totalPayAmount) }}
              </p>
            </div>

            <div class="space-y-2">
              <BaseLabel for="sale_receive_method">Receive Method</BaseLabel>
              <BaseSelect
                id="sale_receive_method"
                v-model="form.payment_method"
                :options="receiveMethodOptions"
                placeholder="Select receive method"
                :required="true"
              />
            </div>

            <div v-if="requiresMainAccount" class="space-y-2">
              <BaseLabel for="sale_main_account_id">Receive In Main Account</BaseLabel>
              <BaseSelect
                id="sale_main_account_id"
                v-model="form.main_account_id"
                :options="mainAccountOptions"
                placeholder="Select main account"
                :required="true"
              />
            </div>

            <p
              v-else
              class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800"
            >
              Due receive method — no cash/bank account required. Amount is recorded as receivable.
            </p>

            <div class="space-y-2">
              <div class="flex items-center justify-between gap-2">
                <BaseLabel for="sale_particular">Particular</BaseLabel>
                <button
                  type="button"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                  title="Reset particular"
                  @click.stop="resetParticular"
                >
                  <i class="fa fa-refresh"></i>
                </button>
              </div>
              <BaseInput
                id="sale_particular"
                :key="`sale-particular-${particularInputKey}`"
                :model-value="form.particular"
                placeholder="Payment collection description"
                @update:model-value="onParticularInput"
              />
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-slate-50/50">
              <button
                type="button"
                class="flex w-full items-center justify-between px-3.5 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-100/80"
                @click="showAdditionalInfo = !showAdditionalInfo"
              >
                <span class="inline-flex items-center gap-2">
                  <i class="fa fa-ellipsis-h text-slate-400"></i>
                  Additional Info
                </span>
                <i
                  class="fa text-slate-400"
                  :class="showAdditionalInfo ? 'fa-chevron-up' : 'fa-chevron-down'"
                ></i>
              </button>

              <div
                v-if="showAdditionalInfo"
                class="space-y-4 border-t border-slate-200 bg-white px-3.5 py-3.5"
              >
                <div class="space-y-2">
                  <BaseLabel for="sale_reference_no">Reference No (Optional)</BaseLabel>
                  <BaseInput
                    id="sale_reference_no"
                    v-model="form.reference_no"
                    placeholder="Reference / voucher no"
                  />
                </div>

                <div class="space-y-2">
                  <BaseLabel for="sale_remarks">Remarks (Optional)</BaseLabel>
                  <textarea
                    id="sale_remarks"
                    v-model="form.remarks"
                    rows="3"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                    placeholder="Additional notes"
                  ></textarea>
                </div>
              </div>
            </div>

            <div class="flex flex-col gap-2.5 border-t border-slate-100 pt-4 sm:flex-row sm:flex-wrap">
              <BaseButton
                class="flex-1 rounded-lg bg-emerald-600 px-4 py-2.5 text-white shadow-sm hover:bg-emerald-700"
                :disabled="submitLoading"
                @click="saveEntry"
                v-can="'receive_payment.create'"
              >
                <i class="fa fa-save mr-1"></i>
                {{ submitLoading ? 'Collecting...' : 'Collect Sale' }}
              </BaseButton>
              <BaseButton
                :className="'rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-700 shadow-sm hover:bg-slate-50'"
                :disabled="submitLoading"
                @click="resetForm"
              >
                Reset
              </BaseButton>
            </div>
          </div>
        </div>
      </div>

      <!-- Account Changes (temporarily hidden)
      <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
          <h3 class="mb-4 text-base font-semibold text-gray-800">Account Changes</h3>
          <p class="mb-4 text-sm text-gray-500">
            Preview of balances after this payment is saved.
          </p>

          <div class="space-y-3">
            <div
              v-if="agentBalancePreview"
              class="rounded-lg border border-blue-100 bg-blue-50/60 px-3 py-3"
            >
              <div class="flex items-center gap-2 text-sm font-semibold text-blue-900">
                <i class="fa fa-user"></i>
                Agent Account
              </div>
              <p class="mt-1 text-xs text-blue-800">
                {{ agentBalancePreview.label }}
              </p>
              <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                <div>
                  <p class="text-xs text-gray-500">Current</p>
                  <p class="font-semibold text-gray-900">
                    {{ formatCurrency(agentBalancePreview.current) }}
                  </p>
                </div>
                <div>
                  <p class="text-xs text-gray-500">After Payment</p>
                  <p
                    class="font-semibold"
                    :class="
                      agentBalancePreview.after < agentBalancePreview.current
                        ? 'text-red-700'
                        : 'text-gray-900'
                    "
                  >
                    {{ formatCurrency(agentBalancePreview.after) }}
                  </p>
                </div>
              </div>
              <p
                v-if="agentBalancePreview.delta !== 0"
                class="mt-2 text-xs font-medium text-red-700"
              >
                − {{ formatCurrency(Math.abs(agentBalancePreview.delta)) }}
              </p>
              <p v-else class="mt-2 text-xs text-gray-500">No balance change</p>
            </div>

            <div
              v-if="clientBalancePreview"
              class="rounded-lg border border-violet-100 bg-violet-50/60 px-3 py-3"
            >
              <div class="flex items-center gap-2 text-sm font-semibold text-violet-900">
                <i class="fa fa-building"></i>
                Client Account
              </div>
              <p class="mt-1 text-xs text-violet-800">
                {{ clientBalancePreview.label }}
              </p>
              <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                <div>
                  <p class="text-xs text-gray-500">Current</p>
                  <p class="font-semibold text-gray-900">
                    {{ formatCurrency(clientBalancePreview.current) }}
                  </p>
                </div>
                <div>
                  <p class="text-xs text-gray-500">After Payment</p>
                  <p
                    class="font-semibold"
                    :class="
                      clientBalancePreview.after < clientBalancePreview.current
                        ? 'text-red-700'
                        : 'text-gray-900'
                    "
                  >
                    {{ formatCurrency(clientBalancePreview.after) }}
                  </p>
                </div>
              </div>
              <p
                v-if="clientBalancePreview.delta !== 0"
                class="mt-2 text-xs font-medium text-red-700"
              >
                − {{ formatCurrency(Math.abs(clientBalancePreview.delta)) }}
              </p>
              <p v-else class="mt-2 text-xs text-gray-500">No balance change</p>
            </div>

            <div
              v-if="mainBalancePreview"
              class="rounded-lg border border-emerald-100 bg-emerald-50/60 px-3 py-3"
            >
              <div class="flex items-center gap-2 text-sm font-semibold text-emerald-900">
                <i class="fa fa-university"></i>
                Main Account
              </div>
              <p class="mt-1 text-xs text-emerald-800">
                {{ mainBalancePreview.label }}
              </p>
              <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                <div>
                  <p class="text-xs text-gray-500">Current</p>
                  <p class="font-semibold text-gray-900">
                    {{ formatCurrency(mainBalancePreview.current) }}
                  </p>
                </div>
                <div>
                  <p class="text-xs text-gray-500">After Payment</p>
                  <p class="font-semibold text-emerald-700">
                    {{ formatCurrency(mainBalancePreview.after) }}
                  </p>
                </div>
              </div>
              <p class="mt-2 text-xs font-medium text-emerald-700">
                + {{ formatCurrency(mainBalancePreview.delta) }}
              </p>
            </div>

            <div
              v-if="!agentBalancePreview && !clientBalancePreview && !mainBalancePreview"
              class="rounded-lg border border-dashed border-gray-200 px-3 py-6 text-center text-sm text-gray-500"
            >
              Select payment details to preview account balance changes.
            </div>
          </div>
        </div>
        -->
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch, nextTick } from 'vue'
import { storeToRefs } from 'pinia'
import BaseButton from '@/shared/components/base/BaseButton.vue'
import BaseInput from '@/shared/components/base/BaseInput.vue'
import BaseLabel from '@/shared/components/base/BaseLabel.vue'
import BaseSelect from '@/shared/components/base/BaseSelect.vue'
import BaseSearchSelect from '@/shared/components/base/BaseSearchSelect.vue'
import JobInfoCard from '../agentBillParts/JobInfoCard.vue'
import SaleCandidatePaymentTable from './SaleCandidatePaymentTable.vue'
import { useJobListStore } from '@/finance/store/jobListStore'
import { formatSaleJobSelectOption, isRejectedOrDeclinedApplication } from '@/finance/utils/jobListMapper'
import { hasJobPayer } from '@/modules/job/utils/jobPayerUtils'
import { useAgentAccountStore } from '@/finance/store/agentAccountStore'
import { useAccountStore } from '@/finance/store/accountStore'
import { usePartyAccountsStore } from '@/finance/store/partyAccountsStore'
import { useSaleEntryStore } from '@/finance/store/saleEntryStore'
import { fetchSaleCollectionSummary } from '@/finance/services/financeAccountService'
import { formatCurrency, getCandidateSalePrice } from '@/finance/utils/billUtils'
import { paymentMethods } from '@/finance/data/paymentData'
import Swal from 'sweetalert2'

const emit = defineEmits(['saved'])

const agentAccountStore = useAgentAccountStore()
const jobListStore = useJobListStore()
const { applicationsByJob } = storeToRefs(jobListStore)
const accountStore = useAccountStore()
const partyAccountsStore = usePartyAccountsStore()
const saleEntryStore = useSaleEntryStore()

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

const today = new Date().toISOString().slice(0, 10)
const submitLoading = ref(false)
const candidatesLoading = ref(false)
const showAdditionalInfo = ref(false)
const particularManuallyEdited = ref(false)
const particularInputKey = ref(0)
const loadedJob = ref(null)
const loadedCandidates = ref([])
const selectedIds = ref([])
const payAmounts = reactive({})
const salePrices = reactive({})
const allMatchingCandidatesPaid = ref(false)
const allMatchingOnReceivable = ref(false)

const emptyCandidatesMessage = computed(() => {
  if (allMatchingOnReceivable.value) {
    return 'Outstanding due bills for this job are listed under Bills Receivable. Settle them there with cash or bank.'
  }

  if (allMatchingCandidatesPaid.value) {
    return 'All matching candidates for this job are already fully paid.'
  }

  return 'No candidates found for this job with ATS status.'
})

const payerTypes = [
  { id: 'agent', label: 'Agent', icon: 'fa fa-user' },
  { id: 'candidate', label: 'Candidate', icon: 'fa fa-id-card' },
  // For now: Client payer hidden from Select Payer tab
  // { id: 'client', label: 'Client', icon: 'fa fa-building' },
]

const receiveMethodOptions = paymentMethods.map((method) => ({
  id: method.id,
  name: method.name,
}))

const isDueReceiveMethod = computed(
  () => String(form.value.payment_method || '').toLowerCase() === 'due'
)

const requiresMainAccount = computed(() =>
  ['cash', 'bank'].includes(String(form.value.payment_method || '').toLowerCase())
)

const candidateTableTitle = computed(() => {
  if (form.value.payer_type === 'agent') return 'Agent Due Bills'
  if (form.value.payer_type === 'client') return 'Client Due Bills'
  return 'Applicant Due Bills'
})

const supportsDueBillAdjustments = computed(
  () => form.value.payer_type === 'agent' || form.value.payer_type === 'candidate'
)

const form = ref({
  payer_type: '',
  payer_client_id: '',
  agent_id: '',
  job_id: '',
  entry_date: today,
  payment_method: 'cash',
  main_account_id: '',
  reference_no: '',
  particular: '',
  remarks: '',
})

const agentOptions = computed(() => jobListStore.getSalePayerAgentOptions())

const isAgentSelectLoading = computed(() => jobListStore.isLoadingSalePayerAgents)

const clientOptions = computed(() => {
  const allowedMasterIds = new Set(
    jobListStore.getSalePayerClientMasterIds().map((id) => Number(id))
  )

  return partyAccountsStore
    .getAccounts('client')
    .filter((account) => {
      if (account.status !== 'Active') return false
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

const jobOptions = computed(() => {
  if (form.value.payer_type === 'candidate') {
    return jobListStore
      .getSaleJobOptionsByPaymentResponsibility('candidate')
      .map(formatSaleJobSelectOption)
  }

  if (form.value.payer_type === 'client') {
    const masterClientId =
      selectedClientPayer.value?.client_id ?? selectedClientPayer.value?.entity_id
    if (!masterClientId) return []

    return jobListStore
      .getSaleJobOptionsForClient(masterClientId)
      .map(formatSaleJobSelectOption)
  }

  if (!form.value.agent_id) return []

  return jobListStore.getSaleJobOptions(form.value.agent_id).map(formatSaleJobSelectOption)
})

const isJobSelectLoading = computed(
  () =>
    jobListStore.isLoadingJobs ||
    (form.value.payer_type === 'agent' && jobListStore.loadingAgentJobs) ||
    (form.value.payer_type === 'candidate' &&
      jobListStore.isLoadingPaymentResponsibilityJobs('candidate')) ||
    (form.value.payer_type === 'client' &&
      jobListStore.isLoadingPaymentResponsibilityJobs('client'))
)

const canSelectJob = computed(() => {
  if (form.value.payer_type === 'client' || form.value.payer_type === 'candidate') return true
  return Boolean(form.value.agent_id)
})

const requiresAgentForSave = computed(() => form.value.payer_type === 'agent')

const selectedAgent = computed(() => {
  if (!form.value.agent_id) return null
  return agentAccountStore.getAccountByBillAgentId(form.value.agent_id)
})

const selectedClientPayer = computed(() => {
  if (!form.value.payer_client_id) return null
  return partyAccountsStore.getAccount('client', form.value.payer_client_id)
})

const canProceedWithSale = computed(() => {
  if (!form.value.payer_type) return false
  if (form.value.payer_type === 'agent') return Boolean(form.value.agent_id)
  if (form.value.payer_type === 'client') return Boolean(form.value.payer_client_id)
  return true
})

const mainAccountOptions = computed(() => {
  const method = String(form.value.payment_method || '').toLowerCase()
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

function ensureDefaultMainAccount() {
  if (!requiresMainAccount.value) {
    form.value.main_account_id = ''
    return
  }

  const options = mainAccountOptions.value
  if (!options.length) {
    form.value.main_account_id = ''
    return
  }

  const stillValid = options.some(
    (option) => String(option.id) === String(form.value.main_account_id)
  )
  if (!stillValid) {
    form.value.main_account_id = options[0].id
  }
}

const selectedMainAccount = computed(() => {
  if (!form.value.main_account_id) return null
  return accountStore.getAccount(form.value.main_account_id)
})

const selectedMainAccountBalance = computed(() =>
  Number(selectedMainAccount.value?.balance ?? selectedMainAccount.value?.current_balance ?? 0)
)

const previewEntryNo = computed(() =>
  saleEntryStore.generateSaleEntryNo(
    form.value.entry_date,
    saleEntryStore.entries.map((item) => item.entry_no)
  )
)

const totalPayAmount = computed(() =>
  selectedIds.value.reduce((sum, candidateId) => sum + Number(payAmounts[candidateId] || 0), 0)
)

const generatedParticular = computed(() => {
  if (!form.value.payer_type || !loadedJob.value) return ''

  const payerLabel =
    form.value.payer_type === 'agent'
      ? 'Agent'
      : form.value.payer_type === 'client'
        ? 'Client'
        : 'Candidate'

  const parts = [`Payment collection (${payerLabel})`]

  if (form.value.payer_type === 'agent') {
    const agentOption = agentOptions.value.find(
      (option) => String(option.id) === String(form.value.agent_id)
    )
    const agentLabel =
      agentOption?.name ||
      (selectedAgent.value
        ? `${selectedAgent.value.agent_code} - ${selectedAgent.value.agent_name}`
        : '')
    if (agentLabel) parts.push(agentLabel)
  }

  if (form.value.payer_type === 'client' && selectedClientPayer.value) {
    parts.push(
      `${selectedClientPayer.value.client_code} - ${selectedClientPayer.value.client_name}`
    )
  }

  if (loadedJob.value.job_code) {
    parts.push(loadedJob.value.job_code)
  }

  const count = selectedIds.value.length
  parts.push(`(${count} candidate${count === 1 ? '' : 's'})`)

  return parts.join(' - ')
})

watch(
  generatedParticular,
  (value) => {
    if (!particularManuallyEdited.value) {
      form.value.particular = value
    }
  },
  { immediate: true }
)

const isResettingParticular = ref(false)

function onParticularInput(value) {
  form.value.particular = value
  if (!isResettingParticular.value) {
    particularManuallyEdited.value = true
  }
}

async function resetParticular() {
  isResettingParticular.value = true
  particularManuallyEdited.value = false
  form.value.particular = generatedParticular.value
  particularInputKey.value += 1
  await nextTick()
  isResettingParticular.value = false
}

const agentBalancePreview = computed(() => {
  if (form.value.payer_type !== 'agent' || !selectedAgent.value) return null

  const current = Number(selectedAgent.value.balance ?? 0)

  return {
    label: `${selectedAgent.value.agent_code} — ${selectedAgent.value.agent_name}`,
    current,
    after: current,
    delta: 0,
  }
})

const clientBalancePreview = computed(() => {
  if (form.value.payer_type !== 'client' || !selectedClientPayer.value) return null

  const current = Number(selectedClientPayer.value.balance ?? 0)

  return {
    label: `${selectedClientPayer.value.client_code} — ${selectedClientPayer.value.client_name}`,
    current,
    after: current,
    delta: 0,
  }
})

const mainBalancePreview = computed(() => {
  if (!selectedMainAccount.value) return null

  const current = selectedMainAccountBalance.value
  const credit = Number(totalPayAmount.value) || 0

  return {
    label: `${selectedMainAccount.value.account_type} — ${selectedMainAccount.value.account_name} (${selectedMainAccount.value.account_label})`,
    current,
    after: current + credit,
    delta: credit,
  }
})

watch(
  mainAccountOptions,
  () => {
    ensureDefaultMainAccount()
  },
  { immediate: true }
)

watch(
  () => form.value.agent_id,
  async (agentId) => {
    form.value.job_id = ''
    clearLoadedData()

    if (!agentId) return

    await jobListStore.fetchJobIdsForAgent(agentId)
  }
)

watch(
  () => form.value.payer_client_id,
  () => {
    if (form.value.payer_type !== 'client') return
    form.value.job_id = ''
    clearLoadedData()
  }
)

watch(
  () => form.value.job_id,
  async (jobId) => {
    clearLoadedData()

    if (!jobId) return
    if (form.value.payer_type === 'agent' && !form.value.agent_id) return

    await loadCandidates()
  }
)

watch(
  () => form.value.payment_method,
  (method) => {
    if (String(method || '').toLowerCase() !== 'due') return
    syncPayAmountsToDue()
  }
)

watch(
  () => form.value.payer_type,
  async (payerType) => {
    form.value.payment_method = 'cash'

    if (payerType === 'candidate') {
      form.value.payer_client_id = ''
      form.value.agent_id = ''
      form.value.job_id = ''
      clearLoadedData()
      await jobListStore.fetchJobIdsByPaymentResponsibility('candidate', true)
      return
    }

    if (payerType === 'client') {
      form.value.agent_id = ''
      form.value.job_id = ''
      form.value.payer_client_id = ''
      clearLoadedData()
      await ensureSalePayerClientAccounts()
      return
    }

    if (payerType === 'agent') {
      form.value.payer_client_id = ''
    }

    clearLoadedData()
  }
)

function setPayerType(typeId) {
  form.value.payer_type = typeId
}

function clearLoadedData() {
  loadedJob.value = null
  loadedCandidates.value = []
  selectedIds.value = []
  allMatchingCandidatesPaid.value = false
  allMatchingOnReceivable.value = false
  particularManuallyEdited.value = false
  Object.keys(payAmounts).forEach((key) => delete payAmounts[key])
  Object.keys(salePrices).forEach((key) => delete salePrices[key])
}

async function loadCandidates() {
  candidatesLoading.value = true
  allMatchingCandidatesPaid.value = false
  allMatchingOnReceivable.value = false

  try {
    await Promise.all([
      jobListStore.fetchJobList(true),
      jobListStore.fetchApplicationsForJob(form.value.job_id, true),
    ])

    const job = jobListStore.getJob(form.value.job_id)
    if (!job) {
      clearLoadedData()
      return
    }

    let applications = [...(applicationsByJob.value[Number(form.value.job_id)] ?? [])]

    if (form.value.payer_type === 'agent' && form.value.agent_id) {
      applications = applications.filter(
        (application) =>
          Number(application.agent_id) === Number(form.value.agent_id) &&
          hasJobPayer(application.payment_responsibility, 'agent')
      )
    } else if (form.value.payer_type === 'candidate') {
      applications = applications.filter((application) =>
        hasJobPayer(application.payment_responsibility, 'candidate')
      )
    } else if (form.value.payer_type === 'client') {
      const masterClientId = Number(
        selectedClientPayer.value?.client_id ?? selectedClientPayer.value?.entity_id
      )
      applications = applications.filter(
        (application) =>
          hasJobPayer(application.payment_responsibility, 'client') &&
          (!masterClientId || Number(application.client_id) === masterClientId)
      )
    }

    // Sale entry (agent / candidate): hide rejected or declined current process.
    if (form.value.payer_type === 'agent' || form.value.payer_type === 'candidate') {
      applications = applications.filter(
        (application) => !isRejectedOrDeclinedApplication(application)
      )
    }

    // Prefer previously billed/adjusted sale price + paid totals from collections.
    const jobPrice = Number(job.price) || 0
    let collectionSummaryById = {}

    try {
      const summaryRows = await fetchSaleCollectionSummary(applications.map((row) => row.id))
      collectionSummaryById = Object.fromEntries(
        summaryRows.map((row) => [Number(row.application_id), row])
      )
    } catch {
      collectionSummaryById = {}
    }

    const resolveCandidateFinancials = (application) => {
      const summary = collectionSummaryById[Number(application.id)]
      const localCollected = saleEntryStore.getCollectedAmountForCandidate(application.id)
      const localSalePrice = saleEntryStore.getLatestSalePriceForCandidate(application.id)

      const collectedAmount =
        summary != null
          ? Number(summary.collected_amount) || 0
          : localCollected

      const salePrice =
        Number(summary?.sale_price) > 0
          ? Number(summary.sale_price)
          : localSalePrice > 0
            ? localSalePrice
            : jobPrice

      const remaining = Math.max(0, Math.round((salePrice - collectedAmount) * 100) / 100)
      const hasDue = Boolean(summary?.has_due)

      return { salePrice, collectedAmount, remaining, hasDue }
    }

    // Outstanding due bills settle from Bills Receivable — hide them here.
    // Zero/unset sale price must still show so users can enter price & receive amount.
    const unpaidApplications = applications.filter((application) => {
      const { salePrice, remaining, hasDue } = resolveCandidateFinancials(application)
      if (hasDue) return false
      if (salePrice <= 0) return true
      return remaining > 0
    })

    const outstandingDueCount = applications.filter((application) => {
      const { remaining, hasDue } = resolveCandidateFinancials(application)
      return remaining > 0 && hasDue
    }).length

    allMatchingOnReceivable.value =
      applications.length > 0 &&
      unpaidApplications.length === 0 &&
      outstandingDueCount > 0

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
  } finally {
    candidatesLoading.value = false
  }
}

onMounted(async () => {
  await Promise.all([
    jobListStore.fetchJobList(),
    jobListStore.fetchSalePayerAgents(),
    agentAccountStore.fetchAccounts(),
    accountStore.fetchActiveAccounts(true),
    partyAccountsStore.fetchAccounts('client'),
    partyAccountsStore.fetchAccounts('applicant'),
    saleEntryStore.fetchSaleEntries({ force: true, page: 1, perPage: 100 }),
  ])
})

function filterAgentOption(option, query) {
  const haystack = [
    option?.name,
    option?.agent_code,
    option?.agent_name,
    option?.id,
  ]
    .filter(Boolean)
    .join(' ')
    .toLowerCase()

  return haystack.includes(query)
}

function toggleRow(id) {
  if (selectedIds.value.includes(id)) {
    selectedIds.value = selectedIds.value.filter((item) => item !== id)
  } else {
    selectedIds.value.push(id)
    const candidate = loadedCandidates.value.find((item) => item.id === id)
    if (candidate && salePrices[id] === undefined) {
      salePrices[id] = Number(candidate.sale_price ?? getCandidateSalePrice(candidate)) || 0
    }
    if (isDueReceiveMethod.value) {
      payAmounts[id] = resolveCandidateDue(candidate)
    } else if (!payAmounts[id]) {
      payAmounts[id] = candidate
        ? Number(candidate.remaining_amount ?? getCandidateSalePrice(candidate)) || 0
        : 0
    }
  }
}

function toggleAll() {
  if (selectedIds.value.length === loadedCandidates.value.length) {
    selectedIds.value = []
    return
  }

  selectedIds.value = loadedCandidates.value.map((candidate) => candidate.id)
  loadedCandidates.value.forEach((candidate) => {
    if (salePrices[candidate.id] === undefined) {
      salePrices[candidate.id] = Number(candidate.sale_price ?? getCandidateSalePrice(candidate)) || 0
    }
    if (isDueReceiveMethod.value) {
      payAmounts[candidate.id] = resolveCandidateDue(candidate)
    } else if (!payAmounts[candidate.id]) {
      payAmounts[candidate.id] =
        Number(candidate.remaining_amount ?? getCandidateSalePrice(candidate)) || 0
    }
  })
}

function resolveCandidateSalePrice(candidate) {
  if (!candidate) return 0
  if (salePrices[candidate.id] !== undefined && salePrices[candidate.id] !== null) {
    return Number(salePrices[candidate.id]) || 0
  }
  return Number(getCandidateSalePrice(candidate)) || 0
}

function resolveCandidateDue(candidate) {
  const salePrice = resolveCandidateSalePrice(candidate)
  const paid = Number(candidate?.collected_amount ?? 0) || 0
  return Math.max(0, Math.round((salePrice - paid) * 100) / 100)
}

function updateSalePrice({ candidateId, value }) {
  const candidate = loadedCandidates.value.find((item) => item.id === candidateId)
  if (!candidate) return
  // Locked after any previous payment (agent / candidate due bills).
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

  // Due method always locks receive amount to remaining due.
  if (isDueReceiveMethod.value) {
    payAmounts[candidateId] = nextDue
    return
  }

  // Keep pay amount synced to due when user was collecting the due balance.
  if (
    supportsDueBillAdjustments.value &&
    (Math.abs(previousPay - previousDue) < 0.01 || previousPay === 0)
  ) {
    payAmounts[candidateId] = nextDue
  } else if (!supportsDueBillAdjustments.value) {
    payAmounts[candidateId] = Math.min(previousPay, nextDue)
  }
}

function syncPayAmountsToDue() {
  loadedCandidates.value.forEach((candidate) => {
    payAmounts[candidate.id] = resolveCandidateDue(candidate)
  })
}

function updatePayAmount({ candidateId, value }) {
  if (isDueReceiveMethod.value) {
    const candidate = loadedCandidates.value.find((item) => item.id === candidateId)
    payAmounts[candidateId] = resolveCandidateDue(candidate)
    return
  }

  const amount = Math.max(0, Number(value) || 0)

  // Agent / candidate due bills can collect more than sale price / remaining.
  if (supportsDueBillAdjustments.value) {
    payAmounts[candidateId] = amount
    return
  }

  const candidate = loadedCandidates.value.find((item) => item.id === candidateId)
  const remaining = resolveCandidateDue(candidate)
  payAmounts[candidateId] = remaining > 0 ? Math.min(amount, remaining) : amount
}

function resetForm() {
  form.value = {
    payer_type: '',
    payer_client_id: '',
    agent_id: '',
    job_id: '',
    entry_date: today,
    payment_method: 'cash',
    main_account_id: '',
    reference_no: '',
    particular: '',
    remarks: '',
  }
  clearLoadedData()
  particularManuallyEdited.value = false
  ensureDefaultMainAccount()
}

async function saveEntry() {
  if (!loadedJob.value || !form.value.payer_type) return
  if (requiresAgentForSave.value && !form.value.agent_id) return
  if (submitLoading.value) return

  if (requiresAgentForSave.value && !selectedAgent.value) {
    await Swal.fire({
      icon: 'error',
      title: 'Agent Account Required',
      text: 'Please create a finance agent account for this agent before saving the payment collection.',
      confirmButtonColor: '#22C55E',
    })
    return
  }

  const candidateRows = selectedIds.value
    .map((candidateId) => {
      const candidate = loadedCandidates.value.find((item) => item.id === candidateId)
      if (!candidate) return null

      const applicantAccount =
        form.value.payer_type === 'candidate'
          ? partyAccountsStore.getAccountByEntityId('applicant', candidate.id)
          : null

      return {
        candidate_id: candidate.id,
        passport_no: candidate.passport_no,
        candidate_name: candidate.candidate_name,
        amount: Number(payAmounts[candidateId] || 0),
        sale_price: resolveCandidateSalePrice(candidate),
        collected_amount: Number(candidate.collected_amount || 0),
        applicant_account_id: applicantAccount?.id ?? null,
      }
    })
    .filter(Boolean)

  if (form.value.payer_type === 'candidate') {
    const missingApplicant = candidateRows.find(
      (row) => Number(row.amount) > 0 && !row.applicant_account_id
    )
    if (missingApplicant) {
      await Swal.fire({
        icon: 'error',
        title: 'Applicant Account Required',
        text: `Please create a finance applicant account for ${
          missingApplicant.candidate_name || 'the selected candidate'
        } before collecting payment.`,
        confirmButtonColor: '#22C55E',
      })
      return
    }
  }

  submitLoading.value = true

  try {
    const result = await saleEntryStore.saveSaleEntry({
      payerType: form.value.payer_type,
      payerId: form.value.payer_type === 'client' ? form.value.payer_client_id : null,
      agentAccountId: selectedAgent.value?.id ?? null,
      jobId: loadedJob.value.id,
      jobCode: loadedJob.value.job_code,
      jobTitle: loadedJob.value.job_title,
      clientName: loadedJob.value.client,
      demandLetter: loadedJob.value.demand_letter,
      entryDate: form.value.entry_date,
      paymentMethod: form.value.payment_method || 'cash',
      mainAccountId: form.value.main_account_id,
      referenceNo: form.value.reference_no,
      particular: form.value.particular,
      remarks: form.value.remarks,
      candidates: candidateRows,
    })

    if (!result.ok) {
      await Swal.fire({
        icon: 'error',
        title: 'Unable to Save',
        text: result.message,
        confirmButtonColor: '#22C55E',
      })
      return
    }

    await Swal.fire({
      icon: 'success',
      title: 'Receive Payment Saved',
      text:
        String(form.value.payment_method || '').toLowerCase() === 'due'
          ? `Entry ${result.entry.entry_no} saved. Settle it from Bills Receivable with cash or bank.`
          : Number(result.movedToReceivable || 0) > 0
            ? `Entry ${result.entry.entry_no} saved. Remaining amount moved to Bills Receivable.`
            : `Entry ${result.entry.entry_no} saved successfully.`,
      confirmButtonColor: '#22C55E',
    })

    if (
      String(form.value.payment_method || '').toLowerCase() === 'due' ||
      Number(result.movedToReceivable || 0) > 0
    ) {
      await saleEntryStore.fetchReceivableBills(true)
    }

    // Refresh job select counts so collected applicants drop off immediately.
    if (form.value.payer_type === 'candidate') {
      await jobListStore.fetchJobIdsByPaymentResponsibility('candidate', true)
    } else if (form.value.payer_type === 'client') {
      await jobListStore.fetchJobIdsByPaymentResponsibility('client', true)
    } else if (form.value.payer_type === 'agent' && form.value.agent_id) {
      await jobListStore.fetchJobIdsForAgent(form.value.agent_id, true)
    }

    resetForm()
    emit('saved')
  } finally {
    submitLoading.value = false
  }
}
</script>
