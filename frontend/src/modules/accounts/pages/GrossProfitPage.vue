<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ t('accounts.gross_profit') }}</PageTitle>
        <p class="mt-1 text-sm text-gray-500">{{ t('accounts.gross_profit_note') }}</p>
      </div>
    </PageHeader>

    <div
      class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
      v-if="!loading && !loadError"
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
        {{ loading ? t('accounts.loading_gross_profit') : t('accounts.refresh') }}
      </BaseButton>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-center">
        <h3 class="text-lg font-bold text-slate-900">{{ t('accounts.gross_profit') }}</h3>
        <p class="mt-0.5 text-sm text-slate-600">{{ periodLabel }}</p>
      </div>

      <div v-if="loading" class="px-4 py-12 text-center text-slate-500">
        {{ t('accounts.loading_gross_profit') }}
      </div>
      <div v-else-if="loadError" class="px-4 py-12 text-center text-sm text-rose-600">
        {{ loadError }}
      </div>
      <div v-else-if="!tableRows.length" class="px-4 py-12 text-center text-slate-500">
        {{ t('accounts.gross_profit_empty') }}
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
            <tr
              v-for="entry in tableRows"
              :key="entry.key"
              :class="entryRowClass(entry)"
            >
              <template v-if="entry.kind === 'header'">
                <td colspan="2" class="border border-slate-200 px-4 py-2 text-xs font-bold uppercase tracking-wide text-slate-600">
                  {{ entry.label }}
                </td>
                <td class="border border-slate-200 px-4 py-2 text-right text-xs font-bold tabular-nums text-slate-700">
                  {{ entry.sectionTotal != null ? formatAmount(entry.sectionTotal) : '' }}
                </td>
              </template>
              <template v-else-if="entry.kind === 'subtotal'">
                <td colspan="2" class="border border-slate-300 bg-slate-50 px-4 py-2.5 font-semibold text-slate-800">
                  {{ entry.label }}
                </td>
                <td class="border border-slate-300 bg-slate-50 px-4 py-2.5 text-right font-semibold tabular-nums text-slate-900">
                  {{ formatAmount(entry.amount) }}
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
            <tr class="bg-emerald-50 font-bold text-emerald-900">
              <td colspan="2" class="border border-emerald-200 px-4 py-3">
                {{ t('accounts.gross_profit') }}
              </td>
              <td class="border border-emerald-200 px-4 py-3 text-right tabular-nums">
                {{ formatAmount(summary.gross_profit) }}
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
import { fetchGrossProfit } from '../services/grossProfitService'
import { toast } from '@/shared/config/toastConfig'

const { t } = useTranslate()

const currentYear = new Date().getFullYear()
const fromDate = ref(`${currentYear}-01-01`)
const toDate = ref(`${currentYear}-12-31`)
const loading = ref(true)
const loadError = ref('')
const groups = ref([])
const summary = ref({
  total_revenue: 0,
  total_contra_revenue: 0,
  total_direct_cost_a: 0,
  total_direct_cost_b: 0,
  total_direct_cost: 0,
  gross_profit: 0,
})

const summaryCards = computed(() => [
  {
    key: 'revenue',
    title: t('accounts.total_revenue'),
    value: summary.value.total_revenue,
    valueClass: 'text-slate-900',
  },
  {
    key: 'direct-cost',
    title: t('accounts.total_direct_cost'),
    value: summary.value.total_direct_cost,
    valueClass: 'text-rose-700',
  },
  {
    key: 'contra',
    title: t('accounts.total_contra_revenue'),
    value: summary.value.total_contra_revenue,
    valueClass: 'text-amber-700',
  },
  {
    key: 'gross-profit',
    title: t('accounts.gross_profit'),
    value: summary.value.gross_profit,
    valueClass: summary.value.gross_profit >= 0 ? 'text-emerald-700' : 'text-rose-700',
  },
])

const tableRows = computed(() =>
  groups.value.flatMap((group) => {
    const rows = [
      {
        kind: 'header',
        key: `header-${group.type}`,
        label: group.type_label,
        sectionTotal: group.section_total,
      },
      ...group.rows.map((row) => ({
        kind: 'row',
        key: `row-${row.account_id}`,
        ...row,
      })),
    ]

    return rows
  }),
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

function entryRowClass(entry) {
  if (entry.kind === 'header') return 'bg-slate-100'
  if (entry.kind === 'subtotal') return ''
  return 'hover:bg-slate-50'
}

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
    || t('accounts.gross_profit_load_error')
  )
}

async function loadReport() {
  loading.value = true
  loadError.value = ''
  try {
    const payload = await fetchGrossProfit({
      from_date: fromDate.value || undefined,
      to_date: toDate.value || undefined,
    })
    const data = payload?.data ?? payload
    groups.value = Array.isArray(data?.groups) ? data.groups : []
    summary.value = {
      total_revenue: Number(data?.summary?.total_revenue || 0),
      total_contra_revenue: Number(data?.summary?.total_contra_revenue || 0),
      total_direct_cost_a: Number(data?.summary?.total_direct_cost_a || 0),
      total_direct_cost_b: Number(data?.summary?.total_direct_cost_b || 0),
      total_direct_cost: Number(data?.summary?.total_direct_cost || 0),
      gross_profit: Number(data?.summary?.gross_profit || 0),
    }
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
