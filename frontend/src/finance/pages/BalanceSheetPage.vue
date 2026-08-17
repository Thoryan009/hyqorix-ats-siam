<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>Balance Sheet</PageTitle>
        <p class="mt-1 text-sm text-gray-500">
          Assets, liabilities, and owner's equity as of the selected assessment year end
        </p>
      </div>
    </PageHeader>

    <div
      class="mb-4 flex flex-col gap-3 rounded-lg border border-gray-100 bg-white p-4 shadow-sm sm:flex-row sm:flex-wrap sm:items-end"
    >
      <div class="flex flex-col sm:min-w-45">
        <label class="mb-1 text-sm font-medium text-gray-700">Assessment Year</label>
        <select
          v-model.number="selectedYear"
          class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
          @change="onYearChange"
        >
          <option v-for="year in yearOptions" :key="year" :value="year">
            {{ year }}{{ year === currentYear ? ' (Current)' : '' }}
          </option>
        </select>
      </div>
      <p class="text-sm text-gray-500 sm:mb-2">As at {{ formatDisplayDate(yearDateRange.to_date) }}</p>
      <BaseButton class="bg-primary text-white hover:opacity-90" :disabled="loading" @click="loadReport">
        <i class="fa fa-refresh mr-1"></i>
        {{ loading ? 'Loading...' : 'Refresh' }}
      </BaseButton>
    </div>

    <div v-if="loading" class="rounded-lg border border-gray-200 bg-white px-4 py-12 text-center text-gray-500 shadow-sm">
      Loading balance sheet...
    </div>

    <template v-else>
      <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 bg-primary-light px-4 py-3 text-center">
          <h3 class="text-lg font-bold text-gray-900">Balance Sheet</h3>
          <p class="mt-0.5 text-sm text-gray-600">As at {{ formatDisplayDate(yearDateRange.to_date) }}</p>
        </div>

        <div class="overflow-x-auto p-4">
          <!-- Assets -->
          <section class="mb-8">
            <h4 class="mb-3 text-base font-bold text-gray-900">Assets</h4>
            <table class="min-w-full border-collapse text-sm">
              <thead>
                <tr class="bg-primary-gradient text-gray-900">
                  <th class="border border-gray-300 px-4 py-2.5 text-left font-semibold">Account</th>
                  <th class="border border-gray-300 px-4 py-2.5 text-right font-semibold">Amount (BDT)</th>
                </tr>
              </thead>
              <tbody>
                <tr class="bg-slate-100 font-semibold text-gray-900">
                  <td class="border border-gray-200 px-4 py-2" colspan="2">Current Assets</td>
                </tr>
                <tr v-if="!currentAssets.length">
                  <td colspan="2" class="border border-gray-200 px-4 py-3 text-center text-gray-500">
                    No current asset balances
                  </td>
                </tr>
                <tr
                  v-for="row in currentAssets"
                  :key="`current-asset-${row.account_id}-${row.label}`"
                  class="hover:bg-gray-50"
                >
                  <td class="border border-gray-200 px-4 py-2.5 pl-8 text-gray-900">{{ row.label }}</td>
                  <td class="border border-gray-200 px-4 py-2.5 text-right tabular-nums text-gray-900">
                    {{ formatAmount(row.amount) }}
                  </td>
                </tr>
                <tr class="bg-slate-50 font-semibold text-gray-900">
                  <td class="border border-gray-200 px-4 py-2.5">Total Current Assets</td>
                  <td class="border border-gray-200 px-4 py-2.5 text-right tabular-nums">
                    {{ formatAmount(summary.total_current_assets) }}
                  </td>
                </tr>

                <tr class="bg-slate-100 font-semibold text-gray-900">
                  <td class="border border-gray-200 px-4 py-2" colspan="2">Non-Current Assets</td>
                </tr>
                <tr v-if="!nonCurrentAssets.length">
                  <td colspan="2" class="border border-gray-200 px-4 py-3 text-center text-gray-500">
                    No non-current asset balances
                  </td>
                </tr>
                <tr
                  v-for="row in nonCurrentAssets"
                  :key="`non-current-asset-${row.account_id}-${row.label}`"
                  class="hover:bg-gray-50"
                >
                  <td class="border border-gray-200 px-4 py-2.5 pl-8 text-gray-900">{{ row.label }}</td>
                  <td
                    class="border border-gray-200 px-4 py-2.5 text-right tabular-nums"
                    :class="row.amount < 0 ? 'text-red-700' : 'text-gray-900'"
                  >
                    {{ formatAmount(row.amount) }}
                  </td>
                </tr>
                <tr class="bg-slate-50 font-semibold text-gray-900">
                  <td class="border border-gray-200 px-4 py-2.5">Total Non-Current Assets</td>
                  <td class="border border-gray-200 px-4 py-2.5 text-right tabular-nums">
                    {{ formatAmount(summary.total_non_current_assets) }}
                  </td>
                </tr>

                <tr class="bg-primary-light font-bold text-gray-900">
                  <td class="border border-gray-300 px-4 py-3">Total Assets</td>
                  <td class="border border-gray-300 px-4 py-3 text-right tabular-nums">
                    {{ formatAmount(summary.total_assets) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </section>

          <!-- Liabilities -->
          <section class="mb-8">
            <h4 class="mb-3 text-base font-bold text-gray-900">Liabilities</h4>
            <table class="min-w-full border-collapse text-sm">
              <thead>
                <tr class="bg-primary-gradient text-gray-900">
                  <th class="border border-gray-300 px-4 py-2.5 text-left font-semibold">Account</th>
                  <th class="border border-gray-300 px-4 py-2.5 text-right font-semibold">Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!liabilities.length">
                  <td colspan="2" class="border border-gray-200 px-4 py-3 text-center text-gray-500">
                    No liability balances
                  </td>
                </tr>
                <tr
                  v-for="row in liabilities"
                  :key="`liability-${row.account_id}-${row.label}`"
                  class="hover:bg-gray-50"
                >
                  <td class="border border-gray-200 px-4 py-2.5 text-gray-900">{{ row.label }}</td>
                  <td class="border border-gray-200 px-4 py-2.5 text-right tabular-nums text-gray-900">
                    {{ formatAmount(row.amount) }}
                  </td>
                </tr>
                <tr class="bg-primary-light font-bold text-gray-900">
                  <td class="border border-gray-300 px-4 py-3">Total Liabilities</td>
                  <td class="border border-gray-300 px-4 py-3 text-right tabular-nums">
                    {{ formatAmount(summary.total_liabilities) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </section>

          <!-- Owner's Equity -->
          <section class="mb-8">
            <h4 class="mb-3 text-base font-bold text-gray-900">Owner's Equity</h4>
            <table class="min-w-full border-collapse text-sm">
              <thead>
                <tr class="bg-primary-gradient text-gray-900">
                  <th class="border border-gray-300 px-4 py-2.5 text-left font-semibold">Account</th>
                  <th class="border border-gray-300 px-4 py-2.5 text-right font-semibold">Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!equity.length">
                  <td colspan="2" class="border border-gray-200 px-4 py-3 text-center text-gray-500">
                    No equity balances
                  </td>
                </tr>
                <tr v-for="row in equity" :key="`equity-${row.account_id}-${row.category}-${row.label}`" class="hover:bg-gray-50">
                  <td class="border border-gray-200 px-4 py-2.5 text-gray-900">{{ row.label }}</td>
                  <td
                    class="border border-gray-200 px-4 py-2.5 text-right tabular-nums"
                    :class="row.amount < 0 ? 'text-red-700' : 'text-gray-900'"
                  >
                    {{ formatAmount(row.amount) }}
                  </td>
                </tr>
                <tr class="bg-primary-light font-bold text-gray-900">
                  <td class="border border-gray-300 px-4 py-3">Total Owner's Equity</td>
                  <td
                    class="border border-gray-300 px-4 py-3 text-right tabular-nums"
                    :class="summary.total_equity < 0 ? 'text-red-700' : ''"
                  >
                    {{ formatAmount(summary.total_equity) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </section>

          <!-- Final Balance Sheet -->
          <section>
            <h4 class="mb-3 text-base font-bold text-gray-900">Final Balance Sheet</h4>
            <table class="min-w-full border-collapse text-sm">
              <thead>
                <tr class="bg-gray-100 text-gray-900">
                  <th class="border border-gray-300 px-4 py-2.5 text-left font-semibold">Assets</th>
                  <th class="border border-gray-300 px-4 py-2.5 text-right font-semibold">Amount</th>
                  <th class="border border-gray-300 px-4 py-2.5 text-left font-semibold">Liabilities &amp; Equity</th>
                  <th class="border border-gray-300 px-4 py-2.5 text-right font-semibold">Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr class="font-semibold text-gray-900">
                  <td class="border border-gray-200 px-4 py-2.5">Current Assets</td>
                  <td class="border border-gray-200 px-4 py-2.5 text-right tabular-nums">
                    {{ formatAmount(summary.total_current_assets) }}
                  </td>
                  <td class="border border-gray-200 px-4 py-2.5">Total Liabilities</td>
                  <td class="border border-gray-200 px-4 py-2.5 text-right tabular-nums">
                    {{ formatAmount(summary.total_liabilities) }}
                  </td>
                </tr>
                <tr class="font-semibold text-gray-900">
                  <td class="border border-gray-200 px-4 py-2.5">Non-Current Assets</td>
                  <td class="border border-gray-200 px-4 py-2.5 text-right tabular-nums">
                    {{ formatAmount(summary.total_non_current_assets) }}
                  </td>
                  <td class="border border-gray-200 px-4 py-2.5">Owner's Equity</td>
                  <td class="border border-gray-200 px-4 py-2.5 text-right tabular-nums">
                    {{ formatAmount(summary.total_equity) }}
                  </td>
                </tr>
                <tr class="font-semibold text-gray-900">
                  <td class="border border-gray-200 px-4 py-2.5">Total Assets</td>
                  <td class="border border-gray-200 px-4 py-2.5 text-right tabular-nums">
                    {{ formatAmount(summary.total_assets) }}
                  </td>
                  <td class="border border-gray-200 px-4 py-2.5">Total Liabilities &amp; Equity</td>
                  <td class="border border-gray-200 px-4 py-2.5 text-right tabular-nums">
                    {{ formatAmount(summary.total_liabilities_and_equity) }}
                  </td>
                </tr>
                <tr class="bg-primary-light font-bold text-gray-900">
                  <td class="border border-gray-300 px-4 py-3">Total</td>
                  <td class="border border-gray-300 px-4 py-3 text-right tabular-nums">
                    {{ formatAmount(summary.total_assets) }}
                  </td>
                  <td class="border border-gray-300 px-4 py-3">Total</td>
                  <td class="border border-gray-300 px-4 py-3 text-right tabular-nums">
                    {{ formatAmount(summary.total_liabilities_and_equity) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </section>
        </div>
      </div>

      <div
        v-if="assets.length || liabilities.length || equity.length"
        class="mt-4 rounded-lg border p-4 text-center text-base font-semibold"
        :class="
          summary.is_balanced
            ? 'border-primary/30 bg-primary-light text-primary-dark'
            : 'border-amber-200 bg-amber-50 text-amber-900'
        "
      >
        <template v-if="summary.is_balanced">
          Balanced — Total Assets equals Total Liabilities &amp; Owner's Equity
        </template>
        <template v-else>
          Not balanced — difference {{ formatAmount(Math.abs(summary.difference)) }}
          ({{ summary.difference > 0 ? 'Assets heavier' : 'Liabilities & Equity heavier' }})
        </template>
      </div>
    </template>
  </SectionHeader>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { fetchBalanceSheet } from '@/finance/services/balanceSheetService'
import { formatDisplayDate } from '@/finance/utils/billUtils'
import { toast } from '@/shared/config/toastConfig'

const route = useRoute()
const currentYear = new Date().getFullYear()
const selectedYear = ref(currentYear)
const loading = ref(false)
const assets = ref([])
const currentAssets = ref([])
const nonCurrentAssets = ref([])
const liabilities = ref([])
const equity = ref([])
const summary = ref({
  total_current_assets: 0,
  total_non_current_assets: 0,
  total_assets: 0,
  total_liabilities: 0,
  total_equity: 0,
  total_liabilities_and_equity: 0,
  current_year_profit: 0,
  difference: 0,
  is_balanced: true,
})

const yearOptions = computed(() => {
  const years = []
  for (let year = currentYear + 1; year >= currentYear - 5; year -= 1) {
    years.push(year)
  }
  return years
})

const yearDateRange = computed(() => ({
  from_date: `${selectedYear.value}-01-01`,
  to_date: `${selectedYear.value}-12-31`,
}))

function formatAmount(amount) {
  return new Intl.NumberFormat('en-IN', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(Number(amount) || 0)
}

function resolveYearFromQuery() {
  const fromDate = route.query.from_date
  const toDate = route.query.to_date

  if (typeof fromDate === 'string' && fromDate.length >= 4) {
    const year = Number(fromDate.slice(0, 4))
    if (yearOptions.value.includes(year)) {
      selectedYear.value = year
      return
    }
  }

  if (typeof toDate === 'string' && toDate.length >= 4) {
    const year = Number(toDate.slice(0, 4))
    if (yearOptions.value.includes(year)) {
      selectedYear.value = year
    }
  }
}

async function onYearChange() {
  await loadReport()
}

async function loadReport() {
  loading.value = true
  try {
    const payload = await fetchBalanceSheet({
      from_date: yearDateRange.value.from_date,
      to_date: yearDateRange.value.to_date,
    })
    const data = payload?.data ?? payload
    assets.value = Array.isArray(data?.assets) ? data.assets : []
    currentAssets.value = Array.isArray(data?.current_assets)
      ? data.current_assets
      : assets.value.filter((row) => !row.is_non_current_asset)
    nonCurrentAssets.value = Array.isArray(data?.non_current_assets)
      ? data.non_current_assets
      : assets.value.filter((row) => row.is_non_current_asset)
    liabilities.value = Array.isArray(data?.liabilities) ? data.liabilities : []
    equity.value = Array.isArray(data?.equity) ? data.equity : []
    summary.value = {
      total_current_assets: Number(
        data?.summary?.total_current_assets ??
          currentAssets.value.reduce((sum, row) => sum + Number(row.amount || 0), 0)
      ),
      total_non_current_assets: Number(
        data?.summary?.total_non_current_assets ??
          nonCurrentAssets.value.reduce((sum, row) => sum + Number(row.amount || 0), 0)
      ),
      total_assets: Number(data?.summary?.total_assets || 0),
      total_liabilities: Number(data?.summary?.total_liabilities || 0),
      total_equity: Number(data?.summary?.total_equity || 0),
      total_liabilities_and_equity: Number(data?.summary?.total_liabilities_and_equity || 0),
      current_year_profit: Number(data?.summary?.current_year_profit || 0),
      difference: Number(data?.summary?.difference || 0),
      is_balanced: data?.summary?.is_balanced !== false,
    }
  } catch (error) {
    toast.error(error?.message || 'Failed to load balance sheet.')
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  resolveYearFromQuery()
  await loadReport()
})
</script>
