<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ t('accounts.trial_balance') }}</PageTitle>
        <p class="mt-1 text-sm text-gray-500">{{ t('accounts.trial_balance_note') }}</p>
      </div>
    </PageHeader>

    <div
      class="mb-4 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:flex-wrap sm:items-end"
    >
      <div class="flex flex-col sm:min-w-[150px]">
        <label class="mb-1 text-sm font-medium text-gray-700">{{ t('accounts.from_date') }}</label>
        <input
          v-model="fromDate"
          type="date"
          class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        />
      </div>
      <div class="flex flex-col sm:min-w-[150px]">
        <label class="mb-1 text-sm font-medium text-gray-700">{{ t('accounts.to_date') }}</label>
        <input
          v-model="toDate"
          type="date"
          class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        />
      </div>
      <BaseButton class="bg-indigo-600 text-white hover:bg-indigo-700" :disabled="loading" @click="loadReport">
        <i class="fa fa-refresh mr-1"></i>
        {{ loading ? t('accounts.loading_trial_balance') : t('accounts.refresh') }}
      </BaseButton>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-center">
        <h3 class="text-lg font-bold text-slate-900">{{ t('accounts.trial_balance') }}</h3>
        <p class="mt-0.5 text-sm text-slate-600">{{ periodLabel }}</p>
      </div>

      <div v-if="loading" class="px-4 py-12 text-center text-slate-500">
        {{ t('accounts.loading_trial_balance') }}
      </div>
      <div v-else-if="loadError" class="px-4 py-12 text-center text-sm text-rose-600">
        {{ loadError }}
      </div>
      <div v-else-if="!tableRows.length" class="px-4 py-12 text-center text-slate-500">
        {{ t('accounts.trial_balance_empty') }}
      </div>
      <div v-else class="overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
          <thead>
            <tr class="bg-[#1e4b8c] text-left text-white">
              <th class="border border-[#163a6d] px-4 py-3 font-semibold">{{ t('accounts.code') }}</th>
              <th class="border border-[#163a6d] px-4 py-3 font-semibold">{{ t('accounts.account_name') }}</th>
              <th class="border border-[#163a6d] px-4 py-3 font-semibold">{{ t('accounts.type') }}</th>
              <th class="border border-[#163a6d] px-4 py-3 text-right font-semibold">{{ t('accounts.debit') }}</th>
              <th class="border border-[#163a6d] px-4 py-3 text-right font-semibold">{{ t('accounts.credit') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="entry in tableRows"
              :key="entry.key"
              :class="entry.kind === 'header' ? 'bg-slate-100' : 'hover:bg-slate-50'"
            >
              <template v-if="entry.kind === 'header'">
                <td colspan="5" class="border border-slate-200 px-4 py-2 text-xs font-bold uppercase tracking-wide text-slate-600">
                  {{ entry.label }}
                </td>
              </template>
              <template v-else>
                <td class="border border-slate-200 px-4 py-2.5 font-medium text-slate-800">
                  {{ entry.account_code }}
                </td>
                <td class="border border-slate-200 px-4 py-2.5 text-slate-900">
                  {{ entry.account_name }}
                </td>
                <td class="border border-slate-200 px-4 py-2.5 text-slate-600">
                  {{ entry.account_type }}
                </td>
                <td class="border border-slate-200 px-4 py-2.5 text-right tabular-nums text-slate-900">
                  {{ entry.debit_balance > 0 ? formatAmount(entry.debit_balance) : '—' }}
                </td>
                <td class="border border-slate-200 px-4 py-2.5 text-right tabular-nums text-slate-900">
                  {{ entry.credit_balance > 0 ? formatAmount(entry.credit_balance) : '—' }}
                </td>
              </template>
            </tr>
            <tr class="bg-slate-50 font-bold text-slate-900">
              <td colspan="3" class="border border-slate-300 px-4 py-3">{{ t('accounts.total') }}</td>
              <td class="border border-slate-300 px-4 py-3 text-right tabular-nums">
                {{ formatAmount(debitTotal) }}
              </td>
              <td class="border border-slate-300 px-4 py-3 text-right tabular-nums">
                {{ formatAmount(creditTotal) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div
      v-if="!loading && !loadError && hasData"
      class="mt-4 rounded-lg border p-4 text-center text-base font-semibold"
      :class="
        isBalanced
          ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
          : 'border-amber-200 bg-amber-50 text-amber-900'
      "
    >
      <template v-if="isBalanced">{{ t('accounts.balanced') }}</template>
      <template v-else>
        {{ t('accounts.not_balanced', { amount: formatAmount(Math.abs(difference)) }) }}
        ({{ difference > 0 ? t('accounts.debit_heavier') : t('accounts.credit_heavier') }})
      </template>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import { fetchTrialBalance } from '../services/trialBalanceService'
import { toast } from '@/shared/config/toastConfig'

const { t } = useTranslate()

const currentYear = new Date().getFullYear()
const fromDate = ref(`${currentYear}-01-01`)
const toDate = ref(`${currentYear}-12-31`)
const loading = ref(true)
const loadError = ref('')
const groups = ref([])
const debitTotal = ref(0)
const creditTotal = ref(0)
const difference = ref(0)
const isBalanced = ref(true)

const hasData = computed(() => tableRows.value.length > 0)

const tableRows = computed(() =>
  groups.value.flatMap((group) => [
    {
      kind: 'header',
      key: `header-${group.type}`,
      label: group.type_label,
    },
    ...group.rows.map((row) => ({
      kind: 'row',
      key: `row-${row.account_id}`,
      ...row,
    })),
  ]),
)

const periodLabel = computed(() => {
  if (fromDate.value && toDate.value) {
    return t('accounts.period_range', { from: fromDate.value, to: toDate.value })
  }
  if (toDate.value) {
    return t('accounts.period_as_of', { date: toDate.value })
  }
  return ''
})

function formatAmount(amount) {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number(amount) || 0)
}

function resolveErrorMessage(error) {
  return (
    error?.message
    || error?.errors?.to_date?.[0]
    || error?.errors?.from_date?.[0]
    || Object.values(error?.errors ?? {})[0]?.[0]
    || t('accounts.trial_balance_load_error')
  )
}

async function loadReport() {
  loading.value = true
  loadError.value = ''
  try {
    const payload = await fetchTrialBalance({
      from_date: fromDate.value || undefined,
      to_date: toDate.value || undefined,
    })
    const data = payload?.data ?? payload
    groups.value = Array.isArray(data?.groups) ? data.groups : []
    debitTotal.value = Number(data?.debit_total || 0)
    creditTotal.value = Number(data?.credit_total || 0)
    difference.value = Number(data?.difference || 0)
    isBalanced.value = data?.is_balanced !== false
  } catch (error) {
    const message = resolveErrorMessage(error)
    loadError.value = message
    groups.value = []
    toast.error(message)
  } finally {
    loading.value = false
  }
}

onMounted(loadReport)
</script>
