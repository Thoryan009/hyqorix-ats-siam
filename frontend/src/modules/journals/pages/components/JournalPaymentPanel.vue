<template>
  <div class="space-y-4">
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
        <div>
          <h3 class="text-sm font-semibold text-slate-900">
            {{ t('journals.payment_select_title') }}
          </h3>
          <p class="mt-0.5 text-xs text-slate-500">
            {{ t('journals.payment_select_hint') }}
          </p>
        </div>
        <span
          v-if="approvedOptions.length"
          class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200"
        >
          {{ approvedOptions.length }} {{ t('journals.approved') }}
        </span>
      </div>

      <div class="mt-3 max-w-xl">
        <BaseLabel for="approved_journal">{{ t('journals.select_approved_journal') }}</BaseLabel>
        <BaseSearchSelect
          id="approved_journal"
          v-model="selectedJournalId"
          class="mt-1.5"
          :options="approvedOptions"
          :placeholder="approvedJournalPlaceholder"
          :disabled="isLoading || !approvedOptions.length"
          :filter-fn="filterApprovedJournal"
          teleport-dropdown
          list-class-name="max-h-72"
        />
      </div>
    </div>

    <div
      v-if="isLoading && !selectedJournalId"
      class="rounded-xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500 shadow-sm"
    >
      {{ t('journals.loading_approved_journals') }}
    </div>

    <div
      v-else-if="!isLoading && !approvedOptions.length"
      class="rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm"
    >
      <p class="text-sm font-medium text-slate-900">
        {{ t('journals.no_approved_journals') }}
      </p>
      <p class="mt-1 text-xs text-slate-500">
        {{ t('journals.no_approved_journals_hint') }}
      </p>
    </div>

    <div
      v-else-if="!selectedJournalId"
      class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center"
    >
      <p class="text-sm font-medium text-slate-800">
        {{ t('journals.select_journal_to_pay') }}
      </p>
      <p class="mt-1 text-xs text-slate-500">
        {{ t('journals.select_journal_to_pay_hint') }}
      </p>
    </div>

    <template v-else>
      <div
        v-if="form.manager_comment"
        class="rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm"
      >
        <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700/80">
          {{ t('journals.manager_comment') }}
        </p>
        <p class="mt-1 text-sm leading-relaxed text-amber-950">
          {{ form.manager_comment }}
        </p>
      </div>

      <div
        class="mb-0 grid grid-cols-2 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4"
      >
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
              class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200"
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

      <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <h3 class="mb-4 text-sm font-semibold text-slate-800">
          {{ t('journals.journal_information') }}
        </h3>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div class="space-y-1.5">
            <BaseLabel for="pay_voucher_date">{{ t('journals.voucher_date') }}</BaseLabel>
            <BaseInput
              id="pay_voucher_date"
              type="date"
              v-model="form.voucher_date"
              className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
            />
          </div>

          <div class="space-y-1.5">
            <BaseLabel for="pay_transaction_type">{{ t('journals.transaction_type') }}</BaseLabel>
            <BaseSearchSelect
              id="pay_transaction_type"
              v-model="form.transaction_type"
              :options="transactionTypeOptions"
              :placeholder="t('journals.select_transaction_type')"
              :filter-fn="filterByNameOrCode"
              teleport-dropdown
              list-class-name="max-h-72"
            />
          </div>

          <div class="space-y-1.5">
            <BaseLabel for="pay_reference_no">{{ t('journals.reference_no') }}</BaseLabel>
            <BaseInput
              id="pay_reference_no"
              v-model="form.reference_no"
              :placeholder="t('journals.reference_no_placeholder')"
              className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
            />
          </div>

          <div class="space-y-1.5">
            <BaseLabel for="pay_party_type">{{ t('journals.party_type') }}</BaseLabel>
            <BaseSearchSelect
              id="pay_party_type"
              v-model="form.party_type"
              :options="partyTypeOptions"
              :placeholder="t('journals.select_party_type')"
              :filter-fn="filterByNameOrCode"
              teleport-dropdown
              list-class-name="max-h-72"
            />
          </div>

          <div class="space-y-1.5">
            <BaseLabel for="pay_party_id">{{ t('journals.party_ledger') }}</BaseLabel>
            <BaseSearchSelect
              id="pay_party_id"
              v-model="form.party_id"
              :options="partyLedgerOptions"
              :placeholder="partyLedgerPlaceholder"
              :disabled="isPartyLedgerLoading"
              :filter-fn="filterByCodeOrName"
            />
          </div>

          <div class="space-y-1.5">
            <BaseLabel for="pay_project_id">{{ t('journals.project_client_demand') }}</BaseLabel>
            <BaseSelect
              id="pay_project_id"
              v-model="form.project_id"
              :options="projectOptions"
              :placeholder="t('journals.select_project')"
            />
          </div>
        </div>
      </div>

      <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
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

      <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <BaseLabel for="pay_narration">{{ t('journals.narration') }}</BaseLabel>
        <BaseTextArea
          id="pay_narration"
          v-model="form.narration"
          :rows="4"
          className="mt-1.5 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
          :placeholder="t('journals.narration_placeholder')"
        />
      </div>

      <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
        <p v-if="validationMessage" class="w-full text-sm text-red-600 sm:mr-auto sm:self-center">
          {{ validationMessage }}
        </p>
        <BaseButton
          className="bg-emerald-600 text-white hover:bg-emerald-700"
          :disabled="isPaying"
          @click="handlePay"
        >
          <span v-if="isPaying">{{ t('journals.paying') }}</span>
          <span v-else>{{ t('journals.pay_journal') }}</span>
        </BaseButton>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { toast } from '@/shared/config/toastConfig'
import { useTranslate } from '@/shared/composables/useTranslate'
import { useAccountOptionsQuery } from '../../queries/useAccountOptionsQuery'
import { useJournalMutations } from '../../queries/useJournalMutations'
import { usePartyLedgerOptionsQuery } from '../../queries/usePartyLedgerOptionsQuery'
import { useApprovedJournalsQuery } from '../../queries/usePendingApprovalJournalsQuery'
import { usePartyTypeOptionsQuery } from '@/modules/parties/queries/usePartyTypeOptionsQuery'
import { useJournalTransactionTypeOptionsQuery } from '../../queries/useJournalTransactionTypeOptionsQuery'
import {
  buildPayPayload,
  costTypeOptions,
  createEmptyJournalLine,
  mapJournalToForm,
  mapJournalToLines,
  projectOptions,
  validateJournalForm,
} from '../../data/postJournalStatic'

const props = defineProps({
  initialJournalId: {
    type: [String, Number],
    default: '',
  },
  active: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['paid'])

const { t } = useTranslate()
const selectedJournalId = ref(props.initialJournalId ? String(props.initialJournalId) : '')
const form = ref(mapJournalToForm(null))
const lines = ref(mapJournalToLines(null))
const validationMessage = ref('')
let nextLineId = 1000
let skipPartyClear = false

const {
  data: approvedData,
  isLoading,
  refetch,
} = useApprovedJournalsQuery(computed(() => props.active))

const approvedJournals = computed(() => approvedData.value?.data?.data ?? [])

const approvedOptions = computed(() =>
  approvedJournals.value.map((journal) => {
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

const selectedJournal = computed(
  () =>
    approvedJournals.value.find(
      (journal) => String(journal.id) === String(selectedJournalId.value),
    ) ?? null,
)

const approvedJournalPlaceholder = computed(() =>
  isLoading.value
    ? t('journals.loading_approved_journals')
    : approvedOptions.value.length
      ? t('journals.search_approved_journal')
      : t('journals.no_approved_journals'),
)

const filterApprovedJournal = (option, query) => {
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

const { pay, payLoading: isPaying } = useJournalMutations({
  onPaySuccess(result) {
    const voucher = result?.data?.voucher_no || result?.voucher_no || ''
    toast.success(t('journals.paid_success', { voucher }))
    selectedJournalId.value = ''
    emit('paid', result)
  },
})

const loadSelectedJournal = (journal) => {
  if (!journal) {
    form.value = mapJournalToForm(null)
    lines.value = mapJournalToLines(null)
    return
  }

  skipPartyClear = true
  form.value = mapJournalToForm(journal)
  lines.value = mapJournalToLines(journal)
  nextLineId = Math.max(...lines.value.map((line) => Number(line.id) || 0), 0) + 1
  validationMessage.value = ''
  queueMicrotask(() => {
    skipPartyClear = false
  })
}

watch(
  () => props.initialJournalId,
  (id) => {
    if (id) selectedJournalId.value = String(id)
  },
)

watch(
  () => props.active,
  (active) => {
    if (active) refetch()
  },
)

watch(selectedJournal, (journal) => {
  loadSelectedJournal(journal)
})

watch(approvedOptions, (options) => {
  if (!options.length || isLoading.value) return
  if (
    selectedJournalId.value &&
    !options.some((option) => String(option.id) === String(selectedJournalId.value))
  ) {
    selectedJournalId.value = ''
  }
})

watch(
  () => form.value.party_type,
  () => {
    if (skipPartyClear) return
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

const addLine = () => {
  lines.value.push(createEmptyJournalLine(nextLineId++))
}

const copyLine = (index) => {
  const source = lines.value[index]
  if (!source) return
  lines.value.splice(index + 1, 0, { ...source, id: nextLineId++ })
}

const deleteLine = (index) => {
  if (lines.value.length <= 1) return
  lines.value.splice(index, 1)
}

const handlePay = async () => {
  validationMessage.value = ''
  const errors = validateJournalForm(form.value, lines.value)
  if (errors.length) {
    const firstError = errors[0]
    const message = t(firstError.key, firstError.params || {})
    validationMessage.value = message
    toast.error(message)
    return
  }

  try {
    await pay.mutateAsync({
      id: selectedJournalId.value,
      ...buildPayPayload(form.value, lines.value),
    })
  } catch (error) {
    const message =
      error?.message ||
      error?.errors?.lines?.[0] ||
      Object.values(error?.errors ?? {})[0]?.[0] ||
      t('journals.error_unbalanced')
    validationMessage.value = message
  }
}

defineExpose({ refetch })
</script>
