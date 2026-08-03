<template>
  <div class="space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
      <div class="flex flex-1 flex-col sm:min-w-[220px]">
        <label class="mb-1 text-sm text-gray-700">Search</label>
        <BaseInput
          v-model="filters.search"
          placeholder="Category, head, client, job, particular, voucher..."
        />
      </div>
      <div class="flex flex-col sm:min-w-[160px]">
        <label class="mb-1 text-sm text-gray-700">From Date</label>
        <BaseInput v-model="filters.from_date" type="date" />
      </div>
      <div class="flex flex-col sm:min-w-[160px]">
        <label class="mb-1 text-sm text-gray-700">To Date</label>
        <BaseInput v-model="filters.to_date" type="date" />
      </div>
      <BaseButton
        v-if="hasActiveFilters"
        :className="'border border-gray-300 bg-white text-gray-800 hover:bg-gray-50'"
        @click="resetFilters"
      >
        Reset
      </BaseButton>
    </div>

    <div v-if="loading" class="py-8 text-center text-gray-500">Loading income...</div>

    <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="w-full border-collapse text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Date</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Voucher / Ref</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Category</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Income Head</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Client</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Job / Candidates</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Particular</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Receive Account</th>
            <th class="border-b border-gray-200 px-3 py-2 text-left">Linked Account</th>
            <th class="border-b border-gray-200 px-3 py-2 text-right">Amount</th>
            <th class="border-b border-gray-200 px-3 py-2 text-center">Actions</th>
          </tr>
        </thead>
        <tbody v-if="!paginatedRows.length">
          <tr>
            <td colspan="11" class="px-3 py-6 text-center text-gray-500">No income found.</td>
          </tr>
        </tbody>
        <tbody v-for="row in paginatedRows" :key="row.id">
          <tr class="border-b border-gray-100 hover:bg-gray-50">
            <td class="px-3 py-2 whitespace-nowrap">{{ row.collection_date || '—' }}</td>
            <td class="px-3 py-2">
              <div class="font-medium">{{ row.voucher_no || '—' }}</div>
              <div v-if="row.reference_no" class="text-xs text-gray-500">{{ row.reference_no }}</div>
            </td>
            <td class="px-3 py-2">{{ row.category_name || '—' }}</td>
            <td class="px-3 py-2">{{ row.head_name || '—' }}</td>
            <td class="px-3 py-2">{{ row.client_name || '—' }}</td>
            <td class="px-3 py-2">
              <div v-if="row.job_code || row.job_title" class="font-medium">
                {{ row.job_code || '—' }}
                <span v-if="row.job_title" class="text-gray-600"> — {{ row.job_title }}</span>
              </div>
              <div v-if="row.candidates?.length" class="text-xs text-gray-500">
                {{ row.candidates.length }} candidate(s)
              </div>
              <span v-if="!row.job_code && !row.job_title && !row.candidates?.length">—</span>
            </td>
            <td class="px-3 py-2">{{ row.particular || '—' }}</td>
            <td class="px-3 py-2">{{ row.receive_account_name || '—' }}</td>
            <td class="px-3 py-2">{{ row.linked_account_name || '—' }}</td>
            <td class="px-3 py-2 text-right font-semibold text-emerald-700">
              {{ formatCurrency(row.amount) }}
            </td>
            <td class="px-3 py-2 text-center">
              <button
                v-if="hasCandidateDetails(row)"
                type="button"
                class="text-primary hover:underline"
                @click="toggleExpanded(row.id)"
              >
                {{ expandedId === row.id ? 'Hide' : 'Details' }}
              </button>
              <span v-else class="text-gray-400">—</span>
            </td>
          </tr>
          <tr v-if="expandedId === row.id && hasCandidateDetails(row)" class="bg-gray-50">
            <td colspan="11" class="px-4 py-3">
              <div class="mb-2 flex flex-wrap gap-x-6 gap-y-1 text-xs text-gray-600">
                <span v-if="row.client_name">
                  Client:
                  <span class="font-semibold text-gray-800">{{ row.client_name }}</span>
                </span>
                <span v-if="row.job_code || row.job_title">
                  Job:
                  <span class="font-semibold text-gray-800">
                    {{ row.job_code || '—' }}
                    <template v-if="row.job_title"> — {{ row.job_title }}</template>
                  </span>
                </span>
              </div>

              <div class="overflow-x-auto rounded border border-gray-200 bg-white">
                <table class="w-full border-collapse text-sm">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="border-b border-gray-200 px-3 py-2 text-left">Passport</th>
                      <th class="border-b border-gray-200 px-3 py-2 text-left">Candidate</th>
                      <th class="border-b border-gray-200 px-3 py-2 text-right">
                        Client Commission
                      </th>
                      <th class="border-b border-gray-200 px-3 py-2 text-right">Paid Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(candidate, index) in row.candidates"
                      :key="candidate.application_id || candidate.candidate_id || index"
                      class="border-b border-gray-100"
                    >
                      <td class="px-3 py-2 font-mono text-[13px]">
                        {{ candidate.passport_no || '—' }}
                      </td>
                      <td class="px-3 py-2">{{ candidate.candidate_name || '—' }}</td>
                      <td class="px-3 py-2 text-right">
                        {{ formatCurrency(candidate.sale_price) }}
                      </td>
                      <td class="px-3 py-2 text-right font-semibold">
                        {{ formatCurrency(candidate.amount) }}
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="bg-gray-50 font-semibold">
                    <tr>
                      <td colspan="2" class="px-3 py-2 text-right text-gray-700">Total</td>
                      <td class="px-3 py-2 text-right text-gray-800">
                        {{ formatCurrency(sumCandidateField(row.candidates, 'sale_price')) }}
                      </td>
                      <td class="px-3 py-2 text-right text-emerald-700">
                        {{ formatCurrency(sumCandidateField(row.candidates, 'amount')) }}
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>

              <p v-if="row.remarks" class="mt-2 text-xs text-gray-500">
                Remarks: {{ row.remarks }}
              </p>
            </td>
          </tr>
        </tbody>
        <tfoot v-if="filteredRows.length" class="bg-gray-50 font-semibold">
          <tr>
            <td colspan="9" class="px-3 py-2 text-right text-gray-700">
              Total ({{ filteredRows.length }})
            </td>
            <td class="px-3 py-2 text-right text-emerald-700">
              {{ formatCurrency(filteredTotal) }}
            </td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <BasePagination
      class="mt-4"
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
import { useIncomeCollectionStore } from '@/finance/store/incomeCollectionStore'
import { formatCurrency } from '@/finance/utils/billUtils'
import { CLIENT_INCOME_CATEGORY_CODE } from '@/finance/data/incomeCategoryCodes'

const collectionStore = useIncomeCollectionStore()
const expandedId = ref(null)
const page = ref(1)
const perPage = ref(10)
const showing = ref(0)
const links = ref([])

const filters = reactive({
  search: '',
  from_date: '',
  to_date: '',
})

const loading = computed(() => collectionStore.isLoading)

const hasActiveFilters = computed(
  () => Boolean(filters.search || filters.from_date || filters.to_date)
)

const filteredRows = computed(() => {
  const query = filters.search.trim().toLowerCase()

  return collectionStore.collections.filter((row) => {
    const dateRaw = row.collection_date_raw || row.collection_date
    if (filters.from_date && dateRaw && dateRaw < filters.from_date) return false
    if (filters.to_date && dateRaw && dateRaw > filters.to_date) return false

    if (!query) return true

    const candidateText = (row.candidates || [])
      .flatMap((candidate) => [candidate.candidate_name, candidate.passport_no])
      .filter(Boolean)
      .join(' ')

    return [
      row.category_name,
      row.head_name,
      row.particular,
      row.voucher_no,
      row.reference_no,
      row.receive_account_name,
      row.linked_account_name,
      row.collected_by_name,
      row.job_code,
      row.job_title,
      row.client_name,
      candidateText,
    ]
      .filter(Boolean)
      .some((value) => String(value).toLowerCase().includes(query))
  })
})

const filteredTotal = computed(() =>
  filteredRows.value.reduce((sum, row) => sum + Number(row.amount || 0), 0)
)

const paginatedRows = computed(() => {
  const start = (page.value - 1) * perPage.value
  return filteredRows.value.slice(start, start + perPage.value)
})

function hasCandidateDetails(row) {
  if (!Array.isArray(row?.candidates) || !row.candidates.length) return false
  return (
    row.category_code === CLIENT_INCOME_CATEGORY_CODE ||
    Boolean(row.job_code || row.job_title || row.client_name)
  )
}

function sumCandidateField(candidates, field) {
  return (candidates || []).reduce((sum, candidate) => sum + (Number(candidate?.[field]) || 0), 0)
}

function toggleExpanded(rowId) {
  expandedId.value = expandedId.value === rowId ? null : rowId
}

function resetFilters() {
  filters.search = ''
  filters.from_date = ''
  filters.to_date = ''
  page.value = 1
  expandedId.value = null
}

function updatePagination() {
  const count = filteredRows.value.length
  const lastPage = Math.max(1, Math.ceil(count / perPage.value))
  const to = Math.min(page.value * perPage.value, count)

  showing.value = to
  links.value = Array.from({ length: lastPage }, (_, index) => ({
    label: String(index + 1),
    active: page.value === index + 1,
    url: page.value === index + 1 ? null : '#',
  }))

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

watch([filteredRows, page, perPage, () => collectionStore.collections.length], updatePagination, {
  immediate: true,
})

watch(
  () => [filters.search, filters.from_date, filters.to_date],
  () => {
    page.value = 1
    expandedId.value = null
  }
)

onMounted(() => {
  collectionStore.fetchCollections(true)
})
</script>
