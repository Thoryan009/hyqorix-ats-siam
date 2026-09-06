<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ t('accounts.balance_sheet') }}</PageTitle>
        <p class="mt-1 text-sm text-gray-500">{{ t('accounts.balance_sheet_note') }}</p>
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
        <label class="mb-1 text-sm font-medium text-gray-700">{{ t('accounts.as_of_date') }}</label>
        <input
          v-model="toDate"
          type="date"
          class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        />
      </div>
      <BaseButton class="bg-indigo-600 text-white hover:bg-indigo-700" :disabled="loading" @click="loadReport">
        <i class="fa fa-refresh mr-1"></i>
        {{ loading ? t('accounts.loading_balance_sheet') : t('accounts.refresh') }}
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

    <div id="balance-sheet-print" class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-center">
        <h3 class="text-lg font-bold text-slate-900">{{ t('accounts.balance_sheet') }}</h3>
        <p class="mt-0.5 text-sm text-slate-600">{{ periodLabel }}</p>
      </div>

      <div v-if="loading" class="px-4 py-12 text-center text-slate-500">
        {{ t('accounts.loading_balance_sheet') }}
      </div>
      <div v-else-if="loadError" class="px-4 py-12 text-center text-sm text-rose-600">
        {{ loadError }}
      </div>
      <div v-else-if="!hasContent" class="px-4 py-12 text-center text-slate-500">
        {{ t('accounts.balance_sheet_empty') }}
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
            <template v-for="block in sectionBlocks" :key="block.key">
              <tr class="bg-slate-200">
                <td colspan="3" class="border border-slate-300 px-4 py-2 text-sm font-bold uppercase tracking-wide text-slate-800">
                  {{ block.label }}
                </td>
              </tr>
              <tr
                v-for="entry in block.rows"
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
              <tr v-if="block.key === 'equity'" class="bg-indigo-50 font-medium text-indigo-900">
                <td colspan="2" class="border border-indigo-100 px-4 py-2.5">
                  {{ currentYearProfit >= 0 ? t('accounts.current_year_profit') : t('accounts.current_year_loss') }}
                </td>
                <td class="border border-indigo-100 px-4 py-2.5 text-right tabular-nums">
                  {{ formatAmount(Math.abs(currentYearProfit)) }}
                </td>
              </tr>
              <tr class="bg-slate-50 font-bold text-slate-900">
                <td colspan="2" class="border border-slate-300 px-4 py-3">
                  {{ block.totalLabel }}
                </td>
                <td class="border border-slate-300 px-4 py-3 text-right tabular-nums">
                  {{ formatAmount(block.total) }}
                </td>
              </tr>
            </template>

            <tr class="bg-emerald-50 font-bold text-emerald-900">
              <td colspan="2" class="border border-emerald-200 px-4 py-3">
                {{ t('accounts.total_liabilities_and_equity') }}
              </td>
              <td class="border border-emerald-200 px-4 py-3 text-right tabular-nums">
                {{ formatAmount(summary.total_liabilities_and_equity) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div
      v-if="!loading && !loadError && hasContent"
      class="mt-4 rounded-lg border p-4 text-center text-base font-semibold print:hidden"
      :class="
        summary.is_balanced
          ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
          : 'border-amber-200 bg-amber-50 text-amber-900'
      "
    >
      <template v-if="summary.is_balanced">{{ t('accounts.balance_sheet_balanced') }}</template>
      <template v-else>
        {{ t('accounts.balance_sheet_not_balanced', { amount: formatAmount(Math.abs(summary.difference)) }) }}
      </template>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import { fetchBalanceSheet } from '../services/balanceSheetService'
import { localizeAccountType } from '../utils/localizeAccountType'
import { useStatementPrint } from '../composables/useStatementPrint'
import { toast } from '@/shared/config/toastConfig'

const { t } = useTranslate()
const { printing, printStatement } = useStatementPrint('balance-sheet-print')

const currentYear = new Date().getFullYear()
const fromDate = ref(`${currentYear}-01-01`)
const toDate = ref(`${currentYear}-12-31`)
const loading = ref(true)
const loadError = ref('')
const currentYearProfit = ref(0)
const groups = ref([])
const summary = ref({
  total_assets: 0,
  total_liabilities: 0,
  total_equity: 0,
  total_liabilities_and_equity: 0,
  difference: 0,
  is_balanced: true,
})

const sectionConfig = {
  assets: {
    key: 'assets',
    labelKey: 'accounts.assets',
    totalLabelKey: 'accounts.total_assets',
    types: ['Asset', 'Contra Asset'],
  },
  liabilities: {
    key: 'liabilities',
    labelKey: 'accounts.liabilities',
    totalLabelKey: 'accounts.total_liabilities',
    types: ['Liability'],
  },
  equity: {
    key: 'equity',
    labelKey: 'accounts.equity',
    totalLabelKey: 'accounts.total_equity',
    types: ['Equity', 'Contra Equity'],
  },
}

const hasContent = computed(() =>
  sectionBlocks.value.some((block) => block.rows.length > 0) || Math.abs(currentYearProfit.value) >= 0.005,
)

const sectionBlocks = computed(() => {
  const blocks = []

  Object.values(sectionConfig).forEach((config) => {
    const sectionGroups = groups.value.filter((group) => config.types.includes(group.type))
    const rows = sectionGroups.flatMap((group) => [
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
    ])

    let total = 0
    if (config.key === 'assets') {
      total = summary.value.total_assets
    } else if (config.key === 'liabilities') {
      total = summary.value.total_liabilities
    } else {
      total = summary.value.total_equity
    }

    if (rows.length || (config.key === 'equity' && Math.abs(currentYearProfit.value) >= 0.005)) {
      blocks.push({
        key: config.key,
        label: t(config.labelKey),
        totalLabel: t(config.totalLabelKey),
        rows,
        total,
      })
    }
  })

  return blocks
})

const summaryCards = computed(() => [
  {
    key: 'assets',
    title: t('accounts.total_assets'),
    value: summary.value.total_assets,
    valueClass: 'text-slate-900',
  },
  {
    key: 'liabilities',
    title: t('accounts.total_liabilities'),
    value: summary.value.total_liabilities,
    valueClass: 'text-rose-700',
  },
  {
    key: 'equity',
    title: t('accounts.total_equity'),
    value: summary.value.total_equity,
    valueClass: 'text-indigo-700',
  },
  {
    key: 'balanced',
    title: t('accounts.total_liabilities_and_equity'),
    value: summary.value.total_liabilities_and_equity,
    valueClass: summary.value.is_balanced ? 'text-emerald-700' : 'text-amber-700',
  },
])

const periodLabel = computed(() => {
  if (fromDate.value && toDate.value) {
    return t('accounts.balance_sheet_period', { from: fromDate.value, to: toDate.value })
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
    || t('accounts.balance_sheet_load_error')
  )
}

async function loadReport() {
  loading.value = true
  loadError.value = ''
  try {
    const payload = await fetchBalanceSheet({
      from_date: fromDate.value || undefined,
      to_date: toDate.value || undefined,
    })
    const data = payload?.data ?? payload
    currentYearProfit.value = Number(data?.current_year_profit || 0)
    groups.value = Array.isArray(data?.groups) ? data.groups : []
    summary.value = {
      total_assets: Number(data?.summary?.total_assets || 0),
      total_liabilities: Number(data?.summary?.total_liabilities || 0),
      total_equity: Number(data?.summary?.total_equity || 0),
      total_liabilities_and_equity: Number(data?.summary?.total_liabilities_and_equity || 0),
      difference: Number(data?.summary?.difference || 0),
      is_balanced: data?.summary?.is_balanced !== false,
    }
  } catch (error) {
    const message = resolveErrorMessage(error)
    loadError.value = message
    currentYearProfit.value = 0
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
  #balance-sheet-print,
  #balance-sheet-print * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  #balance-sheet-print .overflow-x-auto {
    overflow: visible !important;
  }

  #balance-sheet-print table {
    width: 100%;
    font-size: 11px;
  }

  #balance-sheet-print th,
  #balance-sheet-print td {
    padding: 4px 6px;
  }
}
</style>
