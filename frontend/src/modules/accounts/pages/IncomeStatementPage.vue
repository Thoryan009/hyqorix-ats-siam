<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ t('accounts.income_statement') }}</PageTitle>
        <p class="mt-1 text-sm text-gray-500">{{ t('accounts.income_statement_note') }}</p>
      </div>
    </PageHeader>

    <div
      v-if="!loading && !loadError"
      class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 print:hidden"
    >
      <div
        v-for="card in summaryCards"
        :key="card.key"
        class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
      >
        <p class="text-sm text-slate-500">{{ card.title }}</p>
        <p class="mt-1 text-2xl font-bold tabular-nums" :class="card.valueClass">
          {{ formatAmount(card.value) }}
        </p>
      </div>
    </div>

    <div
      class="mb-4 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm print:hidden sm:flex-row sm:flex-wrap sm:items-end"
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
        {{ loading ? t('accounts.loading_income_statement') : t('accounts.refresh') }}
      </BaseButton>
      <BaseButton
        class="bg-emerald-700 text-white hover:bg-emerald-800"
        :disabled="loading || !!loadError || !hasContent"
        @click="printStatement"
      >
        <i class="fa fa-print mr-1"></i>
        {{ printing ? t('accounts.preparing_print') : t('accounts.print') }}
      </BaseButton>
    </div>

    <div id="income-statement-print" class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-center">
        <h3 class="text-lg font-bold text-slate-900">{{ t('accounts.income_statement') }}</h3>
        <p class="mt-0.5 text-sm text-slate-600">{{ periodLabel }}</p>
      </div>

      <div v-if="loading" class="px-4 py-12 text-center text-slate-500">
        {{ t('accounts.loading_income_statement') }}
      </div>
      <div v-else-if="loadError" class="px-4 py-12 text-center text-sm text-rose-600">
        {{ loadError }}
      </div>
      <div v-else-if="!hasContent" class="px-4 py-12 text-center text-slate-500">
        {{ t('accounts.income_statement_empty') }}
      </div>
      <div v-else class="overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
          <thead>
            <tr class="bg-[#1e4b8c] text-left text-white">
              <th class="border border-[#163a6d] px-4 py-3 font-semibold">{{ t('accounts.code') }}</th>
              <th class="border border-[#163a6d] px-4 py-3 font-semibold">{{ t('accounts.account_name') }}</th>
              <th class="border border-[#163a6d] px-4 py-3 text-right font-semibold">{{ t('accounts.amount') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr class="bg-indigo-50 font-semibold text-indigo-900">
              <td colspan="2" class="border border-indigo-100 px-4 py-3">
                {{ t('accounts.gross_profit_brought_down') }}
              </td>
              <td class="border border-indigo-100 px-4 py-3 text-right tabular-nums">
                {{ formatAmount(grossProfitBroughtDown) }}
              </td>
            </tr>

            <tr
              v-for="entry in tableRows"
              :key="entry.key"
              :class="entry.kind === 'header' ? 'bg-slate-100' : 'hover:bg-slate-50'"
            >
              <template v-if="entry.kind === 'header'">
                <td colspan="2" class="border border-slate-200 px-4 py-2 text-xs font-bold uppercase tracking-wide text-slate-600">
                  {{ entry.label }}
                </td>
                <td class="border border-slate-200 px-4 py-2 text-right text-xs font-bold tabular-nums text-slate-700">
                  {{ formatAmount(entry.sectionTotal) }}
                </td>
              </template>
              <template v-else>
                <td class="border border-slate-200 px-4 py-2.5 font-medium text-slate-800">
                  {{ entry.account_code }}
                </td>
                <td class="border border-slate-200 px-4 py-2.5 text-slate-900">
                  {{ entry.account_name }}
                </td>
                <td class="border border-slate-200 px-4 py-2.5 text-right tabular-nums text-slate-900">
                  {{ formatAmount(entry.amount) }}
                </td>
              </template>
            </tr>

            <tr
              class="font-bold"
              :class="summary.is_profit ? 'bg-emerald-50 text-emerald-900' : 'bg-rose-50 text-rose-900'"
            >
              <td colspan="2" class="border px-4 py-3" :class="summary.is_profit ? 'border-emerald-200' : 'border-rose-200'">
                {{ summary.is_profit ? t('accounts.net_profit') : t('accounts.net_loss') }}
              </td>
              <td
                class="border px-4 py-3 text-right tabular-nums"
                :class="summary.is_profit ? 'border-emerald-200' : 'border-rose-200'"
              >
                {{ formatAmount(Math.abs(summary.net_profit)) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import { fetchIncomeStatement } from '../services/incomeStatementService'
import { localizeAccountType } from '../utils/localizeAccountType'
import { useStatementPrint } from '../composables/useStatementPrint'
import { toast } from '@/shared/config/toastConfig'

const { t } = useTranslate()
const { printing, printStatement } = useStatementPrint('income-statement-print')

const currentYear = new Date().getFullYear()
const fromDate = ref(`${currentYear}-01-01`)
const toDate = ref(`${currentYear}-12-31`)
const loading = ref(true)
const loadError = ref('')
const grossProfitBroughtDown = ref(0)
const groups = ref([])
const summary = ref({
  gross_profit_brought_down: 0,
  total_income: 0,
  total_expenses: 0,
  net_profit: 0,
  is_profit: true,
})

const hasContent = computed(() =>
  Math.abs(grossProfitBroughtDown.value) >= 0.005 || tableRows.value.length > 0,
)

const tableRows = computed(() =>
  groups.value.flatMap((group) => [
    {
      kind: 'header',
      key: `header-${group.type}`,
      label: localizeAccountType(group.type, t, group.type_label),
      sectionTotal: group.section_total,
    },
    ...group.rows.map((row) => ({
      kind: 'row',
      key: `row-${row.account_id}`,
      ...row,
    })),
  ]),
)

const summaryCards = computed(() => [
  {
    key: 'gp',
    title: t('accounts.gross_profit_brought_down'),
    value: summary.value.gross_profit_brought_down,
    valueClass: summary.value.gross_profit_brought_down >= 0 ? 'text-indigo-700' : 'text-rose-700',
  },
  {
    key: 'income',
    title: t('accounts.total_other_income'),
    value: summary.value.total_income,
    valueClass: 'text-emerald-700',
  },
  {
    key: 'expenses',
    title: t('accounts.total_expenses'),
    value: summary.value.total_expenses,
    valueClass: 'text-rose-700',
  },
  {
    key: 'net',
    title: summary.value.is_profit ? t('accounts.net_profit') : t('accounts.net_loss'),
    value: Math.abs(summary.value.net_profit),
    valueClass: summary.value.is_profit ? 'text-emerald-700' : 'text-rose-700',
  },
])

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
    || t('accounts.income_statement_load_error')
  )
}

async function loadReport() {
  loading.value = true
  loadError.value = ''
  try {
    const payload = await fetchIncomeStatement({
      from_date: fromDate.value || undefined,
      to_date: toDate.value || undefined,
    })
    const data = payload?.data ?? payload
    grossProfitBroughtDown.value = Number(data?.gross_profit_brought_down || 0)
    groups.value = Array.isArray(data?.groups) ? data.groups : []
    summary.value = {
      gross_profit_brought_down: Number(data?.summary?.gross_profit_brought_down || 0),
      total_income: Number(data?.summary?.total_income || 0),
      total_expenses: Number(data?.summary?.total_expenses || 0),
      net_profit: Number(data?.summary?.net_profit || 0),
      is_profit: data?.summary?.is_profit !== false,
    }
  } catch (error) {
    const message = resolveErrorMessage(error)
    loadError.value = message
    grossProfitBroughtDown.value = 0
    groups.value = []
    toast.error(message)
  } finally {
    loading.value = false
  }
}

onMounted(loadReport)
</script>

<style>
@media print {
  #income-statement-print,
  #income-statement-print * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  #income-statement-print .overflow-x-auto {
    overflow: visible !important;
  }

  #income-statement-print table {
    width: 100%;
    font-size: 11px;
  }

  #income-statement-print th,
  #income-statement-print td {
    padding: 4px 6px;
  }
}
</style>
