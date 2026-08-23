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
            class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200"
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
          <BaseSelect
            id="transaction_type"
            v-model="form.transaction_type"
            :options="transactionTypeOptions"
          />
        </div>

        <div class="space-y-1.5">
          <BaseLabel for="reference_no">{{ t('journals.reference_no') }}</BaseLabel>
          <BaseInput
            id="reference_no"
            v-model="form.reference_no"
            className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
          />
        </div>

        <div class="space-y-1.5">
          <BaseLabel for="party_type">{{ t('journals.party_type') }}</BaseLabel>
          <BaseSelect id="party_type" v-model="form.party_type" :options="partyTypeOptions" />
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
          <BaseSelect id="project_id" v-model="form.project_id" :options="projectOptions" />
        </div>
      </div>
    </div>

    <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
      <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h3 class="text-sm font-semibold text-slate-800">{{ t('journals.journal_lines') }}</h3>
          <p class="mt-0.5 text-xs text-slate-500">{{ t('journals.balance_hint') }}</p>
        </div>
        <BaseButton className="bg-slate-800 text-white hover:bg-slate-900">
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
              <th class="whitespace-nowrap border-b border-slate-200 px-3 py-2.5 text-right font-semibold">
                {{ t('journals.debit_label') }}
              </th>
              <th class="whitespace-nowrap border-b border-slate-200 px-3 py-2.5 text-right font-semibold">
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
              <td class="border-b border-slate-100 px-3 py-2">
                <BaseSearchSelect
                  v-model="line.account_id"
                  :options="accountOptions"
                  :placeholder="accountPlaceholder"
                  :disabled="isAccountLoading"
                  :filter-fn="filterByCodeOrName"
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
                <BaseSelect v-model="line.cost_type" :options="costTypeOptions" />
              </td>
              <td class="border-b border-slate-100 px-3 py-2">
                <BaseInput
                  type="number"
                  v-model="line.debit"
                  className="w-full rounded-md border border-gray-300 px-3 py-2 text-right text-sm tabular-nums focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </td>
              <td class="border-b border-slate-100 px-3 py-2">
                <BaseInput
                  type="number"
                  v-model="line.credit"
                  className="w-full rounded-md border border-gray-300 px-3 py-2 text-right text-sm tabular-nums focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary"
                />
              </td>
              <td class="border-b border-slate-100 px-3 py-2">
                <div class="flex flex-wrap gap-1">
                  <button
                    type="button"
                    class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50"
                  >
                    {{ t('journals.edit') }}
                  </button>
                  <button
                    type="button"
                    class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50"
                  >
                    {{ t('journals.copy') }}
                  </button>
                  <button
                    type="button"
                    class="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50"
                  >
                    {{ t('journals.ledger') }}
                  </button>
                  <button
                    type="button"
                    class="rounded border border-red-200 px-2 py-1 text-xs text-red-600 hover:bg-red-50"
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
      <BaseButton className="border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
        {{ t('journals.save_draft') }}
      </BaseButton>
      <BaseButton className="border border-indigo-300 bg-white text-indigo-700 hover:bg-indigo-50">
        {{ t('journals.submit_approval') }}
      </BaseButton>
      <BaseButton className="bg-indigo-600 text-white hover:bg-indigo-700">
        {{ t('journals.post_journal') }}
      </BaseButton>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import { useAccountOptionsQuery } from '../queries/useAccountOptionsQuery'
import { usePartyLedgerOptionsQuery } from '../queries/usePartyLedgerOptionsQuery'
import {
  costTypeOptions,
  defaultJournalForm,
  defaultJournalLines,
  partyTypeOptions,
  projectOptions,
  transactionTypeOptions,
} from '../data/postJournalStatic'

const { t } = useTranslate()
const router = useRouter()

const form = ref({ ...defaultJournalForm })
const lines = ref(defaultJournalLines.map((line) => ({ ...line })))

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

const postingPreview = computed(() =>
  lines.value.map((line) => {
    const account =
      accountOptions.value.find((opt) => String(opt.id) === String(line.account_id))?.name || '—'
    if (toNumber(line.debit) > 0) {
      return `${account}: ${t('journals.dr')} ${formatAmount(line.debit)}`
    }
    return `${account}: ${t('journals.cr')} ${formatAmount(line.credit)}`
  }),
)

const goBack = () => {
  router.push({ name: 'Journal Management' })
}
</script>
