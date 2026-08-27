<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ t('journals.post_title') }}</PageTitle>
        <p class="mt-1 text-sm text-gray-500">{{ t('journals.post_static_note') }}</p>
      </div>
      <BaseButton
        className="border border-gray-300 bg-white text-gray-700 hover:bg-gray-50"
        @click="goBack"
      >
        {{ t('journals.back_to_list') }}
      </BaseButton>
    </PageHeader>

    <div class="mb-6 flex flex-wrap gap-2">
      <button
        v-for="tab in pageTabs"
        :key="tab.id"
        type="button"
        class="rounded-lg border px-4 py-2 text-sm font-semibold transition-all"
        :class="
          activeTab === tab.id
            ? 'border-primary bg-primary-light! text-primary ring-1 ring-primary'
            : 'border-gray-200 bg-white text-gray-700 hover:border-primary hover:bg-primary-light!'
        "
        @click="activeTab = tab.id"
      >
        {{ tab.label }}
      </button>
    </div>

    <div v-if="activeTab === 'bill_entry'">
    <div class="mb-4 grid grid-cols-2 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
      <div>
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
          {{ t('journals.voucher_no') }}
        </p>
        <p class="mt-1 text-sm font-semibold text-slate-900">{{ form.voucher_no }}</p>
      </div>
      <div>
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
          {{ t('journals.status') }}
        </p>
        <p class="mt-1">
          <span
            class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1"
            :class="statusBadgeClass"
          >
            {{ form.status }}
          </span>
        </p>
      </div>
      <div>
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
          {{ t('journals.total_debit') }}
        </p>
        <p class="mt-1 text-sm font-semibold tabular-nums text-slate-900">
          {{ formatAmount(totalDebit) }}
        </p>
      </div>
      <div>
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
          {{ t('journals.total_credit') }}
        </p>
        <p class="mt-1 text-sm font-semibold tabular-nums text-slate-900">
          {{ formatAmount(totalCredit) }}
        </p>
      </div>
    </div>

    <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
      <h3 class="mb-4 text-sm font-semibold text-slate-800">
        {{ t('journals.journal_information') }}
      </h3>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="space-y-1.5">
          <BaseLabel for="voucher_date">{{ t('journals.voucher_date') }}</BaseLabel>
          <BaseInput
            id="voucher_date"
            type="date"
            v-model="form.voucher_date"
            className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
          />
        </div>

        <div class="space-y-1.5">
          <BaseLabel for="transaction_type">{{ t('journals.transaction_type') }}</BaseLabel>
          <BaseSearchSelect
            id="transaction_type"
            v-model="form.transaction_type"
            :options="transactionTypeOptions"
            :placeholder="t('journals.select_transaction_type')"
            :filter-fn="filterByNameOrCode"
            teleport-dropdown
            list-class-name="max-h-72"
          />
        </div>

        <div class="space-y-1.5">
          <BaseLabel for="reference_no">{{ t('journals.reference_no') }}</BaseLabel>
          <BaseInput
            id="reference_no"
            v-model="form.reference_no"
            :placeholder="t('journals.reference_no_placeholder')"
            className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
          />
        </div>

        <div class="space-y-1.5">
          <BaseLabel for="party_type">{{ t('journals.party_type') }}</BaseLabel>
          <BaseSearchSelect
            id="party_type"
            v-model="form.party_type"
            :options="partyTypeOptions"
            :placeholder="t('journals.select_party_type')"
            :filter-fn="filterByNameOrCode"
            teleport-dropdown
            list-class-name="max-h-72"
          />
        </div>

        <div class="space-y-1.5">
          <BaseLabel for="party_id">{{ t('journals.party_ledger') }}</BaseLabel>
          <BaseSearchSelect
            id="party_id"
            v-model="form.party_id"
            :options="partyLedgerOptions"
            :placeholder="partyLedgerPlaceholder"
            :disabled="isPartyLedgerLoading"
            :filter-fn="filterByCodeOrName"
          />
        </div>

        <div class="space-y-1.5">
          <BaseLabel for="project_id">{{ t('journals.project_client_demand') }}</BaseLabel>
          <BaseSelect
            id="project_id"
            v-model="form.project_id"
            :options="projectOptions"
            :placeholder="t('journals.select_project')"
          />
        </div>
      </div>
    </div>

    <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
      <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h3 class="text-sm font-semibold text-slate-800">{{ t('journals.journal_lines') }}</h3>
          <p class="mt-0.5 text-xs text-slate-500">{{ t('journals.balance_hint') }}</p>
        </div>
        <BaseButton className="bg-slate-800 text-white hover:bg-slate-900" @click="addLine">
          + {{ t('journals.add_line') }}
        </BaseButton>
      </div>

      <div class="overflow-x-auto rounded-md border border-slate-200">
        <table class="min-w-full border-collapse text-sm">
          <thead>
            <tr class="bg-slate-50 text-left text-slate-600">
              <th class="whitespace-nowrap border-b border-slate-200 px-3 py-2.5 font-semibold">#</th>
              <th class="min-w-[220px] border-b border-slate-200 px-3 py-2.5 font-semibold">
                {{ t('journals.account') }}
              </th>
              <th class="min-w-[160px] border-b border-slate-200 px-3 py-2.5 font-semibold">
                {{ t('journals.sub_ledger') }}
              </th>
              <th class="min-w-[140px] border-b border-slate-200 px-3 py-2.5 font-semibold">
                {{ t('journals.cost_revenue_type') }}
              </th>
              <th class="w-36 whitespace-nowrap border-b border-slate-200 px-2 py-2.5 text-right font-semibold">
                {{ t('journals.debit_label') }}
              </th>
              <th class="w-36 whitespace-nowrap border-b border-slate-200 px-2 py-2.5 text-right font-semibold">
                {{ t('journals.credit_label') }}
              </th>
              <th class="whitespace-nowrap border-b border-slate-200 px-3 py-2.5 font-semibold">
                {{ t('journals.action') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(line, index) in lines" :key="line.id" class="align-middle">
              <td class="border-b border-slate-100 px-3 py-2 text-slate-700">{{ index + 1 }}</td>
              <td class="relative overflow-visible border-b border-slate-100 px-3 py-2">
                <BaseSearchSelect
                  v-model="line.account_id"
                  :options="accountOptions"
                  :placeholder="accountPlaceholder"
                  :disabled="isAccountLoading"
                  :filter-fn="filterByCodeOrName"
                  teleport-dropdown
                  list-class-name="max-h-96"
                />
              </td>
              <td class="border-b border-slate-100 px-3 py-2">
                <BaseInput
                  v-model="line.sub_ledger"
                  :placeholder="t('journals.optional')"
                  :disabled="true"
                  className="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500"
                />
              </td>
              <td class="border-b border-slate-100 px-3 py-2">
                <BaseSelect
                  v-model="line.cost_type"
                  :options="costTypeOptions"
                  :placeholder="t('journals.select_cost_type')"
                />
              </td>
              <td class="w-36 border-b border-slate-100 px-2 py-2">
                <BaseInput
                  type="number"
                  v-model="line.debit"
                  className="w-full rounded-md border border-gray-300 px-2 py-2 text-right text-sm tabular-nums focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </td>
              <td class="w-36 border-b border-slate-100 px-2 py-2">
                <BaseInput
                  type="number"
                  v-model="line.credit"
                  className="w-full rounded-md border border-gray-300 px-2 py-2 text-right text-sm tabular-nums focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </td>
              <td class="whitespace-nowrap border-b border-slate-100 px-3 py-2">
                <div class="flex flex-nowrap items-center gap-1">
                  <button
                    type="button"
                    class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50"
                    @click="copyLine(index)"
                  >
                    {{ t('journals.copy') }}
                  </button>
                  <button
                    type="button"
                    class="rounded border border-red-200 px-2 py-1 text-xs text-red-600 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40"
                    :disabled="lines.length <= 1"
                    @click="deleteLine(index)"
                  >
                    {{ t('journals.delete') }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="bg-slate-50 font-semibold text-slate-800">
              <td class="px-3 py-2.5" colspan="4">{{ t('journals.total') }}</td>
              <td class="px-3 py-2.5 text-right tabular-nums">{{ formatAmount(totalDebit) }}</td>
              <td class="px-3 py-2.5 text-right tabular-nums">{{ formatAmount(totalCredit) }}</td>
              <td class="px-3 py-2.5"></td>
            </tr>
            <tr class="text-slate-700">
              <td class="px-3 py-2.5" colspan="4">
                {{ t('journals.difference') }}:
                <span class="font-medium">{{ differenceLabel }}</span>
              </td>
              <td class="px-3 py-2.5" colspan="3">
                <div class="flex justify-end">
                  <span
                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                    :class="
                      isBalanced
                        ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
                        : 'bg-red-50 text-red-700 ring-1 ring-red-200'
                    "
                  >
                    <span
                      class="h-1.5 w-1.5 rounded-full"
                      :class="isBalanced ? 'bg-emerald-500' : 'bg-red-500'"
                    ></span>
                    {{ isBalanced ? t('journals.balanced') : t('journals.unbalanced') }}
                  </span>
                </div>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
      <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <BaseLabel for="narration">{{ t('journals.narration') }}</BaseLabel>
        <BaseTextArea
          id="narration"
          v-model="form.narration"
          :rows="5"
          className="mt-1.5 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
          :placeholder="t('journals.narration_placeholder')"
        />
      </div>

      <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 shadow-sm">
        <h3 class="mb-3 text-sm font-semibold text-slate-800">
          {{ t('journals.posting_preview') }}
        </h3>
        <ul class="space-y-2 text-sm text-slate-700">
          <li v-for="item in postingPreview" :key="item">• {{ item }}</li>
        </ul>
        <div class="mt-4 space-y-1 border-t border-slate-200 pt-3 text-xs text-slate-500">
          <p>{{ t('journals.prepared_by') }}: {{ t('journals.current_user') }}</p>
          <p>{{ t('journals.approval') }}: {{ t('journals.manager_required') }}</p>
        </div>
      </div>
    </div>

    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
      <p v-if="validationMessage" class="w-full text-sm text-red-600 sm:mr-auto sm:self-center">
        {{ validationMessage }}
      </p>
      <BaseButton
        className="border border-indigo-300 bg-white text-indigo-700 hover:bg-indigo-50"
        :disabled="isSubmitting"
        @click="handleSubmit('pending_approval')"
      >
        <span v-if="isSubmitting && pendingStatus === 'pending_approval'">
          {{ t('journals.submitting') }}
        </span>
        <span v-else>{{ t('journals.submit_approval') }}</span>
      </BaseButton>
      <BaseButton
        className="bg-indigo-600 text-white hover:bg-indigo-700"
        :disabled="isSubmitting"
        @click="handleSubmit('posted')"
      >
        <span v-if="isSubmitting && pendingStatus === 'posted'">
          {{ t('journals.posting') }}
        </span>
        <span v-else>{{ t('journals.post_journal') }}</span>
      </BaseButton>
    </div>
    </div>

    <div v-else-if="activeTab === 'approval'" class="space-y-4">
      <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
          <div>
            <h3 class="text-sm font-semibold text-slate-900">
              {{ t('journals.approval_select_title') }}
            </h3>
            <p class="mt-0.5 text-xs text-slate-500">
              {{ t('journals.approval_select_hint') }}
            </p>
          </div>
          <span
            v-if="pendingApprovalOptions.length"
            class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200"
          >
            {{ pendingApprovalOptions.length }} {{ t('journals.waiting_for_approval') }}
          </span>
        </div>

        <div class="mt-3 max-w-xl">
          <BaseLabel for="pending_journal">{{ t('journals.select_pending_journal') }}</BaseLabel>
          <BaseSearchSelect
            id="pending_journal"
            v-model="selectedApprovalJournalId"
            class="mt-1.5"
            :options="pendingApprovalOptions"
            :placeholder="pendingJournalPlaceholder"
            :disabled="isPendingApprovalLoading || !pendingApprovalOptions.length"
            :filter-fn="filterPendingJournal"
            teleport-dropdown
            list-class-name="max-h-72"
          />
        </div>
      </div>

      <div
        v-if="isPendingApprovalLoading && !selectedApprovalJournal"
        class="rounded-xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500 shadow-sm"
      >
        {{ t('journals.loading_pending_journals') }}
      </div>

      <div
        v-else-if="!isPendingApprovalLoading && !pendingApprovalOptions.length"
        class="rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm"
      >
        <p class="text-sm font-medium text-slate-900">
          {{ t('journals.no_pending_journals') }}
        </p>
        <p class="mt-1 text-xs text-slate-500">
          {{ t('journals.no_pending_journals_hint') }}
        </p>
      </div>

      <div
        v-else-if="!selectedApprovalJournal"
        class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center"
      >
        <p class="text-sm font-medium text-slate-800">
          {{ t('journals.select_journal_to_preview') }}
        </p>
        <p class="mt-1 text-xs text-slate-500">
          {{ t('journals.select_journal_to_preview_hint') }}
        </p>
      </div>

      <template v-else>
        <div class="space-y-4">
          <JournalApprovalPreview
            :key="selectedApprovalJournal.id"
            :journal="selectedApprovalJournal"
          />

          <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <BaseLabel for="manager_comment">{{ t('journals.manager_comment') }}</BaseLabel>
            <BaseTextArea
              id="manager_comment"
              v-model="managerComment"
              :rows="3"
              className="mt-1.5 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
              :placeholder="t('journals.manager_comment_placeholder')"
            />
            <p v-if="approveValidationMessage" class="mt-2 text-sm text-red-600">
              {{ approveValidationMessage }}
            </p>
            <div class="mt-4 flex justify-end">
              <BaseButton
                className="bg-indigo-600 text-white hover:bg-indigo-700"
                :disabled="isApproving"
                @click="handleApprove"
              >
                <span v-if="isApproving">{{ t('journals.approving') }}</span>
                <span v-else>{{ t('journals.approve_journal') }}</span>
              </BaseButton>
            </div>
          </div>
        </div>
      </template>
    </div>

    <JournalPaymentPanel
      v-else-if="activeTab === 'payment'"
      :active="activeTab === 'payment'"
      :initial-journal-id="selectedPaymentJournalId"
      @paid="handlePaid"
    />
  </SectionHeader>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { toast } from '@/shared/config/toastConfig'
import { useTranslate } from '@/shared/composables/useTranslate'
import { useAccountOptionsQuery } from '../queries/useAccountOptionsQuery'
import { useJournalMutations } from '../queries/useJournalMutations'
import { usePartyLedgerOptionsQuery } from '../queries/usePartyLedgerOptionsQuery'
import { usePendingApprovalJournalsQuery } from '../queries/usePendingApprovalJournalsQuery'
import JournalApprovalPreview from './components/JournalApprovalPreview.vue'
import JournalPaymentPanel from './components/JournalPaymentPanel.vue'
import {
  buildJournalPayload,
  costTypeOptions,
  createEmptyJournalLine,
  defaultJournalForm,
  defaultJournalLines,
  projectOptions,
  todayIsoDate,
  validateJournalForm,
} from '../data/postJournalStatic'
import { usePartyTypeOptionsQuery } from '@/modules/parties/queries/usePartyTypeOptionsQuery'
import { useJournalTransactionTypeOptionsQuery } from '../queries/useJournalTransactionTypeOptionsQuery'

const { t } = useTranslate()
const router = useRouter()

const activeTab = ref('bill_entry')
const selectedApprovalJournalId = ref('')
const selectedPaymentJournalId = ref('')
const managerComment = ref('')
const approveValidationMessage = ref('')
const pageTabs = computed(() => [
  { id: 'bill_entry', label: t('journals.tab_bill_entry') },
  { id: 'approval', label: t('journals.tab_approval') },
  { id: 'payment', label: t('journals.tab_payment') },
])

const isApprovalTab = computed(() => activeTab.value === 'approval')
const {
  data: pendingApprovalData,
  isLoading: isPendingApprovalLoading,
  refetch: refetchPendingApprovals,
} = usePendingApprovalJournalsQuery(isApprovalTab)

const pendingApprovalJournals = computed(() => pendingApprovalData.value?.data?.data ?? [])

const pendingApprovalOptions = computed(() =>
  pendingApprovalJournals.value.map((journal) => {
    const party = journal.party_code || journal.party_name || ''
    const amount = Number(journal.total_debit || 0).toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })
    const parts = [
      journal.voucher_no,
      journal.voucher_date_label || journal.voucher_date,
      party,
      amount ? `Dr ${amount}` : '',
    ].filter(Boolean)

    return {
      id: journal.id,
      code: journal.voucher_no,
      name: parts.join(' · '),
      voucher_no: journal.voucher_no,
      party_name: journal.party_name || '',
      party_code: journal.party_code || '',
      narration: journal.narration || '',
      reference_no: journal.reference_no || '',
    }
  }),
)

const selectedApprovalJournal = computed(() =>
  pendingApprovalJournals.value.find(
    (journal) => String(journal.id) === String(selectedApprovalJournalId.value),
  ) ?? null,
)

const pendingJournalPlaceholder = computed(() =>
  isPendingApprovalLoading.value
    ? t('journals.loading_pending_journals')
    : pendingApprovalOptions.value.length
      ? t('journals.search_pending_journal')
      : t('journals.no_pending_journals'),
)

const filterPendingJournal = (option, query) => {
  const q = String(query || '').toLowerCase()
  if (!q) return true
  return [
    option?.name,
    option?.code,
    option?.voucher_no,
    option?.party_name,
    option?.party_code,
    option?.narration,
    option?.reference_no,
  ]
    .filter(Boolean)
    .some((value) => String(value).toLowerCase().includes(q))
}

const form = ref({ ...defaultJournalForm, voucher_date: todayIsoDate() })
const lines = ref(defaultJournalLines.map((line) => ({ ...line })))
const validationMessage = ref('')
const pendingStatus = ref('')
let nextLineId = Math.max(...lines.value.map((line) => Number(line.id) || 0), 0) + 1

const resetBillEntryForm = () => {
  form.value = { ...defaultJournalForm, voucher_date: todayIsoDate() }
  lines.value = defaultJournalLines.map((line) => ({ ...line }))
  validationMessage.value = ''
  pendingStatus.value = ''
  nextLineId = Math.max(...lines.value.map((line) => Number(line.id) || 0), 0) + 1
}

const { data: transactionTypeOptionsData } = useJournalTransactionTypeOptionsQuery('active')
const transactionTypeOptions = computed(() =>
  (transactionTypeOptionsData.value ?? []).map((item) => ({
    id: item.id ?? item.code,
    code: item.code ?? item.id,
    name: item.name,
  })),
)

const { data: partyTypeOptionsData } = usePartyTypeOptionsQuery('active')
const partyTypeOptions = computed(() => [
  { id: '', code: '', name: 'None' },
  ...(partyTypeOptionsData.value ?? []).map((item) => ({
    id: item.id ?? item.code,
    code: item.code ?? item.id,
    name: item.name,
  })),
])

const { submit, submitLoading: isSubmitting, approve, approveLoading: isApproving } =
  useJournalMutations({
    onSuccess(result) {
      const voucher = result?.data?.voucher_no || result?.voucher_no || ''
      const journalId = result?.data?.id || result?.id || ''
      const status = result?.data?.status_raw || pendingStatus.value

      if (status === 'pending_approval') {
        toast.success(t('journals.submitted_success', { voucher }))
        resetBillEntryForm()
        selectedApprovalJournalId.value = journalId ? String(journalId) : ''
        managerComment.value = ''
        approveValidationMessage.value = ''
        activeTab.value = 'approval'
        refetchPendingApprovals()
        return
      }

      toast.success(t('journals.posted_success', { voucher }))
      router.push({ name: 'Journal Management' })
    },
    onApproveSuccess(result) {
      const voucher = result?.data?.voucher_no || result?.voucher_no || ''
      const journalId = result?.data?.id || result?.id || ''
      toast.success(t('journals.approved_success', { voucher }))
      selectedApprovalJournalId.value = ''
      managerComment.value = ''
      approveValidationMessage.value = ''
      selectedPaymentJournalId.value = journalId ? String(journalId) : ''
      activeTab.value = 'payment'
      refetchPendingApprovals()
    },
  })

const handleApprove = async () => {
  approveValidationMessage.value = ''
  const comment = String(managerComment.value || '').trim()
  if (!comment) {
    approveValidationMessage.value = t('journals.error_manager_comment_required')
    toast.error(approveValidationMessage.value)
    return
  }
  if (!selectedApprovalJournalId.value) return

  try {
    await approve.mutateAsync({
      id: selectedApprovalJournalId.value,
      manager_comment: comment,
    })
  } catch (error) {
    approveValidationMessage.value =
      error?.errors?.manager_comment?.[0] ||
      error?.message ||
      t('journals.error_manager_comment_required')
  }
}

const handlePaid = () => {
  selectedPaymentJournalId.value = ''
  router.push({ name: 'Journal Management' })
}

watch(isApprovalTab, (enabled) => {
  if (enabled) {
    refetchPendingApprovals()
  }
})

watch(selectedApprovalJournalId, () => {
  managerComment.value = ''
  approveValidationMessage.value = ''
})

watch(pendingApprovalOptions, (options) => {
  if (!options.length || isPendingApprovalLoading.value) return
  if (
    selectedApprovalJournalId.value &&
    !options.some((option) => String(option.id) === String(selectedApprovalJournalId.value))
  ) {
    selectedApprovalJournalId.value = ''
  }
})

const statusBadgeClass = computed(() => {
  const status = String(form.value.status || '').toLowerCase()
  if (status === 'posted') return 'bg-emerald-50 text-emerald-700 ring-emerald-200'
  if (status.includes('pending')) return 'bg-blue-50 text-blue-700 ring-blue-200'
  return 'bg-amber-50 text-amber-700 ring-amber-200'
})

const addLine = () => {
  lines.value.push(createEmptyJournalLine(nextLineId++))
}

const copyLine = (index) => {
  const source = lines.value[index]
  if (!source) return

  const copied = {
    ...source,
    id: nextLineId++,
  }
  lines.value.splice(index + 1, 0, copied)
}

const deleteLine = (index) => {
  if (lines.value.length <= 1) return
  lines.value.splice(index, 1)
}

const partyTypeRef = computed(() => form.value.party_type)
const { data: partiesData, isLoading: isPartyLedgerLoading } =
  usePartyLedgerOptionsQuery(partyTypeRef)
const { data: accountsData, isLoading: isAccountLoading } = useAccountOptionsQuery()

const partyLedgerOptions = computed(() => {
  const parties = partiesData.value?.data?.data ?? []
  return parties.map((party) => ({
    id: party.id,
    name: `${party.code} – ${party.name}`,
    code: party.code,
  }))
})

const accountOptions = computed(() => {
  const accounts = accountsData.value?.data?.data ?? []
  return accounts.map((account) => ({
    id: account.id,
    name: `${account.code} – ${account.name}`,
    code: account.code,
  }))
})

const partyLedgerPlaceholder = computed(() =>
  isPartyLedgerLoading.value
    ? t('journals.loading_parties')
    : t('journals.select_party'),
)

const accountPlaceholder = computed(() =>
  isAccountLoading.value
    ? t('journals.loading_accounts')
    : t('journals.select_account'),
)

const filterByCodeOrName = (option, query) => {
  const label = String(option?.name ?? '').toLowerCase()
  const code = String(option?.code ?? '').toLowerCase()
  return label.includes(query) || code.includes(query)
}

const filterByNameOrCode = (option, query) => {
  const label = String(option?.name ?? '').toLowerCase()
  const code = String(option?.code ?? option?.id ?? '').toLowerCase()
  return label.includes(query) || code.includes(query)
}

watch(
  () => form.value.party_type,
  () => {
    form.value.party_id = ''
  },
)

const toNumber = (value) => {
  const n = Number(value)
  return Number.isFinite(n) ? n : 0
}

const totalDebit = computed(() =>
  lines.value.reduce((sum, line) => sum + toNumber(line.debit), 0),
)

const totalCredit = computed(() =>
  lines.value.reduce((sum, line) => sum + toNumber(line.credit), 0),
)

const difference = computed(() => totalDebit.value - totalCredit.value)
const isBalanced = computed(() => Math.abs(difference.value) < 0.0001)

const formatAmount = (value) => {
  if (value === null || value === undefined || value === '') return '0'
  return Number(value).toLocaleString('en-US')
}

const differenceLabel = computed(() => {
  const abs = Math.abs(difference.value)
  if (abs < 0.0001) return `0 ${t('journals.cr')}`
  return `${formatAmount(abs)} ${difference.value > 0 ? t('journals.dr') : t('journals.cr')}`
})

const postingPreview = computed(() => {
  const previewLines = lines.value.filter(
    (line) => line.account_id || toNumber(line.debit) > 0 || toNumber(line.credit) > 0,
  )

  if (!previewLines.length) {
    return [t('journals.posting_preview_empty')]
  }

  return previewLines.map((line) => {
    const account =
      accountOptions.value.find((opt) => String(opt.id) === String(line.account_id))?.name || '—'
    if (toNumber(line.debit) > 0) {
      return `${account}: ${t('journals.dr')} ${formatAmount(line.debit)}`
    }
    return `${account}: ${t('journals.cr')} ${formatAmount(line.credit)}`
  })
})

const goBack = () => {
  router.push({ name: 'Journal Management' })
}

const handleSubmit = async (status) => {
  validationMessage.value = ''
  pendingStatus.value = status

  const errors = validateJournalForm(form.value, lines.value)
  if (errors.length) {
    const firstError = errors[0]
    const message = t(firstError.key, firstError.params || {})
    validationMessage.value = message
    toast.error(message)
    return
  }

  try {
    await submit.mutateAsync(buildJournalPayload(form.value, lines.value, status))
  } catch (error) {
    const message =
      error?.message ||
      error?.errors?.lines?.[0] ||
      Object.values(error?.errors ?? {})[0]?.[0] ||
      t('journals.error_unbalanced')
    validationMessage.value = message
  }
}
</script>
