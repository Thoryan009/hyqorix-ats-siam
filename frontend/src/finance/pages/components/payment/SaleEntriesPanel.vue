<template>
  <div class="space-y-4">
    <div class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="w-full border-collapse text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Entry No</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Date</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Agent</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Job</th>
            <th class="border-b border-gray-200 px-3 py-2 text-center">Candidates</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Payer</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Receive Method</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Main Account</th>
            <th class="border-b border-gray-200 px-3 py-2 text-right">Amount</th>
            <th class="border-b border-gray-200 px-3 py-2 text-center">Actions</th>
          </tr>
        </thead>
        <tbody v-if="saleEntryStore.entriesLoading">
          <tr>
            <td colspan="10" class="px-3 py-6 text-center text-gray-500">Loading payment collections...</td>
          </tr>
        </tbody>
        <tbody v-else-if="!entries.length">
          <tr>
            <td colspan="10" class="px-3 py-6 text-center text-gray-500">No payment collections yet.</td>
          </tr>
        </tbody>
        <template v-else>
          <tbody v-for="entry in entries" :key="entry.id">
            <tr class="border-b border-gray-100 hover:bg-gray-50">
              <td class="px-3 py-2 font-medium">{{ entry.entry_no }}</td>
              <td class="px-3 py-2 whitespace-nowrap">{{ formatDisplayDate(entry.entry_date) }}</td>
              <td class="px-3 py-2">
                <div>{{ entry.agent_name || '-' }}</div>
                <div v-if="entry.agent_code" class="text-xs text-gray-500">{{ entry.agent_code }}</div>
              </td>
              <td class="px-3 py-2">
                <div>{{ entry.job_title }}</div>
                <div class="text-xs text-gray-500">{{ entry.job_code }}</div>
              </td>
              <td class="px-3 py-2 text-center">{{ entry.candidates.length }}</td>
              <td class="px-3 py-2">{{ formatPayer(entry) }}</td>
              <td class="px-3 py-2">
                <span class="rounded-full px-2 py-1 text-xs font-semibold" :class="methodClass(entry.payment_method)">
                  {{ methodLabel(entry.payment_method) }}
                </span>
              </td>
              <td class="px-3 py-2">{{ entry.main_account_name || '-' }}</td>
              <td class="px-3 py-2 text-right font-semibold text-green-700">
                {{ formatCurrency(entry.total_amount) }}
              </td>
              <td class="px-3 py-2 text-center">
                <button
                  type="button"
                  class="mr-2 text-primary hover:underline"
                  @click="toggleExpanded(entry.id)"
                >
                  {{ expandedId === entry.id ? 'Hide' : 'Details' }}
                </button>
              </td>
            </tr>
            <tr v-if="expandedId === entry.id" class="bg-gray-50">
              <td colspan="10" class="px-4 py-3">
                <div class="overflow-x-auto rounded border border-gray-200 bg-white">
                  <table class="w-full border-collapse text-sm">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="border-b border-gray-200 px-3 py-2 text-left">Passport</th>
                        <th class="border-b border-gray-200 px-3 py-2 text-left">Candidate</th>
                        <th class="border-b border-gray-200 px-3 py-2 text-right">Sale Price</th>
                        <th class="border-b border-gray-200 px-3 py-2 text-right">Paid Amount</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="candidate in entry.candidates"
                        :key="candidate.candidate_id"
                        class="border-b border-gray-100"
                      >
                        <td class="px-3 py-2">{{ candidate.passport_no }}</td>
                        <td class="px-3 py-2">{{ candidate.candidate_name }}</td>
                        <td class="px-3 py-2 text-right">{{ formatCurrency(candidate.sale_price) }}</td>
                        <td class="px-3 py-2 text-right font-semibold">
                          {{ formatCurrency(candidate.amount) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <p v-if="entry.remarks" class="mt-2 text-xs text-gray-500">
                  Remarks: {{ entry.remarks }}
                </p>
              </td>
            </tr>
          </tbody>
        </template>
      </table>
    </div>

    <BasePagination
      class="mt-4"
      :total="paginationTotal"
      :showing="showing"
      :links="links"
      :per-page="perPage"
      @update:page="setPage"
      @update:perPage="setPerPage"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useSaleEntryStore } from '@/finance/store/saleEntryStore'
import { formatCurrency, formatDisplayDate } from '@/finance/utils/billUtils'

const saleEntryStore = useSaleEntryStore()
const expandedId = ref(null)
const page = ref(1)
const perPage = ref(10)
const showing = ref(0)
const links = ref([])

const entries = computed(() => saleEntryStore.entries)
const paginationTotal = computed(() => saleEntryStore.paginationMeta.total ?? 0)

function formatPayer(entry) {
  const typeLabel = {
    agent: 'Agent',
    candidate: 'Candidate',
    client: 'Client',
  }

  const label = typeLabel[entry.payer_type] ?? entry.payer_type

  if (entry.payer_type === 'candidate') {
    const names = entry.candidates.map((item) => item.candidate_name).filter(Boolean)
    if (!names.length) return 'Candidate'
    if (names.length <= 2) return `${label}: ${names.join(', ')}`
    return `${label}: ${names.slice(0, 2).join(', ')} +${names.length - 2}`
  }

  if (entry.payer_name) {
    return `${label}: ${entry.payer_name}`
  }

  return label || '-'
}

function methodLabel(method) {
  if (method === 'balance') return 'Adjust Balance'
  if (method === 'bank') return 'Bank'
  if (method === 'due') return 'Due'
  if (method === 'expense_link') return 'Expense Link'
  if (method === 'adjustment') return 'Adjustment'
  return 'Cash'
}

function methodClass(method) {
  if (method === 'balance' || method === 'due') return 'bg-amber-100 text-amber-700'
  if (method === 'bank') return 'bg-sky-100 text-sky-700'
  return 'bg-emerald-100 text-emerald-700'
}

function toggleExpanded(entryId) {
  expandedId.value = expandedId.value === entryId ? null : entryId
}

async function loadEntries() {
  await saleEntryStore.fetchSaleEntries({
    force: true,
    page: page.value,
    perPage: perPage.value,
  })
}

function updatePagination() {
  const meta = saleEntryStore.paginationMeta
  showing.value = Number(meta.to) || 0
  links.value = Array.isArray(meta.links) ? meta.links : []

  const lastPage = Math.max(1, Number(meta.last_page) || 1)
  if (page.value > lastPage) {
    page.value = lastPage
  }
}

function setPage(value) {
  if (value && value !== page.value) {
    page.value = value
    expandedId.value = null
  }
}

function setPerPage(value) {
  perPage.value = Number(value)
  page.value = 1
  expandedId.value = null
}

watch(
  () => [
    saleEntryStore.paginationMeta.total,
    saleEntryStore.paginationMeta.to,
    saleEntryStore.paginationMeta.last_page,
    saleEntryStore.paginationMeta.links,
  ],
  updatePagination,
  { immediate: true, deep: true }
)

watch([page, perPage], () => {
  loadEntries()
})

onMounted(async () => {
  await loadEntries()
})
</script>
