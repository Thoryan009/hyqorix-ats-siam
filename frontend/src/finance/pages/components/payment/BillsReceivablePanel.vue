<template>
  <div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="relative min-w-[220px] flex-1">
        <i class="fa fa-search absolute top-1/2 left-3 -translate-y-1/2 text-gray-400"></i>
        <input
          v-model="filters.search"
          type="text"
          placeholder="Search candidate, passport, job, voucher..."
          class="w-full rounded-lg border border-gray-300 py-2 pr-3 pl-9 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
        />
      </div>
      <span
        class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800"
      >
        {{ filteredRows.length }} receivable
      </span>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="w-full border-collapse text-sm">
        <thead>
          <tr class="bg-gray-50 text-left text-xs tracking-wide text-gray-500 uppercase">
            <th class="px-3 py-2.5 font-semibold">Source</th>
            <th class="px-3 py-2.5 font-semibold">Candidate</th>
            <th class="px-3 py-2.5 font-semibold">Payer</th>
            <th class="px-3 py-2.5 font-semibold">Job</th>
            <th class="px-3 py-2.5 font-semibold">Due Date</th>
            <th class="px-3 py-2.5 font-semibold">Voucher</th>
            <th class="px-3 py-2.5 text-right font-semibold">Remaining</th>
            <th class="px-3 py-2.5 text-center font-semibold">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="saleEntryStore.billsReceivableLoading">
            <td colspan="8" class="px-3 py-10 text-center text-gray-500">
              Loading bills receivable...
            </td>
          </tr>
          <tr v-else-if="!paginatedRows.length">
            <td colspan="8" class="px-3 py-10 text-center text-gray-500">
              <p class="font-medium text-gray-700">No bills receivable yet.</p>
              <p class="mt-1 text-xs text-gray-500">
                Due Sale and income receivables appear here until settled with cash or bank.
                Accounting posts go to Bills Receivable Ledger / Income Receivable ledgers.
              </p>
            </td>
          </tr>
          <tr
            v-for="entry in paginatedRows"
            :key="entry.id ?? entry.application_id"
            class="border-t border-gray-100 hover:bg-gray-50/80"
          >
            <td class="px-3 py-2.5">
              <span
                class="rounded-full px-2 py-0.5 text-xs font-semibold"
                :class="
                  entry.source === 'pl_income'
                    ? 'bg-violet-100 text-violet-800'
                    : 'bg-emerald-100 text-emerald-800'
                "
              >
                {{ formatReceivableSourceLabel(entry) }}
              </span>
            </td>
            <td class="px-3 py-2.5">
              <p class="font-medium text-gray-900">
                {{ entry.candidate_name || entry.income_head_name || '—' }}
              </p>
              <p class="text-xs text-gray-500">
                {{
                  entry.passport_no ||
                  entry.income_category_name ||
                  entry.particular ||
                  '—'
                }}
              </p>
            </td>
            <td class="px-3 py-2.5">
              <p class="font-medium text-gray-800">{{ formatPayerTypeLabel(entry.payer_type) }}</p>
              <p class="text-xs text-gray-500">{{ entry.party_account_label || '—' }}</p>
            </td>
            <td class="px-3 py-2.5">
              <p class="font-medium text-gray-800">{{ entry.job_title || '—' }}</p>
              <p class="text-xs text-gray-500">{{ entry.job_code || '—' }}</p>
            </td>
            <td class="px-3 py-2.5 text-gray-700">{{ entry.collection_date || '—' }}</td>
            <td class="px-3 py-2.5 text-gray-700">
              {{ entry.voucher_no || entry.entry_no || '—' }}
            </td>
            <td class="px-3 py-2.5 text-right">
              <p class="font-semibold tabular-nums text-amber-700">
                {{ formatCurrency(getReceivableRemainingAmount(entry)) }}
              </p>
              <p
                v-if="Number(entry.collected_amount) > 0"
                class="text-xs font-normal text-gray-500"
              >
                of {{ formatCurrency(entry.sale_price) }}
              </p>
            </td>
            <td class="px-3 py-2.5 text-center">
              <BaseTableButton
                v-can="'transaction.create'"
                icon="fa fa-money"
                variant="success"
                title="Receive Payment"
                @click="openReceive(entry)"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <BasePagination
      class="mt-2"
      :total="filteredRows.length"
      :showing="showing"
      :links="links"
      :per-page="perPage"
      @update:page="setPage"
      @update:perPage="setPerPage"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import BasePagination from '@/shared/components/base/BasePagination.vue'
import BaseTableButton from '@/shared/components/base/BaseTableButton.vue'
import { useSaleEntryStore } from '@/finance/store/saleEntryStore'
import { formatCurrency } from '@/finance/utils/billUtils'
import {
  formatPayerTypeLabel,
  formatReceivableSourceLabel,
  getReceivableRemainingAmount,
} from '@/finance/utils/receivableBillUtils'

const router = useRouter()
const saleEntryStore = useSaleEntryStore()

const filters = reactive({
  search: '',
})

const page = ref(1)
const perPage = ref(10)
const showing = ref(0)
const links = ref([])

const filteredRows = computed(() => {
  const query = filters.search.trim().toLowerCase()

  return saleEntryStore.billsReceivable.filter((entry) => {
    if (!query) return true

    return [
      entry.candidate_name,
      entry.passport_no,
      entry.income_head_name,
      entry.income_category_name,
      entry.particular,
      entry.job_title,
      entry.job_code,
      entry.voucher_no,
      entry.entry_no,
      entry.party_account_label,
      entry.payer_type,
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()
      .includes(query)
  })
})

const paginatedRows = computed(() => {
  const start = (page.value - 1) * perPage.value
  return filteredRows.value.slice(start, start + perPage.value)
})

watch(
  [filteredRows, page, perPage],
  () => {
    const total = filteredRows.value.length
    const start = (page.value - 1) * perPage.value
    showing.value = Math.min(perPage.value, Math.max(total - start, 0))
    links.value = []
  },
  { immediate: true }
)

watch(
  () => filters.search,
  () => {
    page.value = 1
  }
)

function setPage(nextPage) {
  page.value = nextPage
}

function setPerPage(nextPerPage) {
  perPage.value = nextPerPage
  page.value = 1
}

function openReceive(entry) {
  router.push({
    name: 'Bill Receivable Receive',
    params: { id: entry.id ?? entry.application_id },
  })
}

onMounted(() => {
  saleEntryStore.fetchReceivableBills(true)
})
</script>
