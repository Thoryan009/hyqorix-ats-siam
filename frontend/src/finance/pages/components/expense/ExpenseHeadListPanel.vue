<template>
  <div>
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
      <div class="flex flex-1 flex-col sm:min-w-[220px]">
        <label class="mb-1 text-sm text-gray-700">Search</label>
        <BaseInput v-model="filters.search" placeholder="Search expense heads..." />
      </div>

      <div class="flex flex-col sm:min-w-[200px]">
        <label class="mb-1 text-sm text-gray-700">Expense Category</label>
        <BaseSelect
          v-model="filters.categoryId"
          :options="categoryFilterOptions"
          placeholder="All Categories"
        />
      </div>

      <div class="flex flex-col sm:min-w-[160px]">
        <label class="mb-1 text-sm text-gray-700">Status</label>
        <BaseSelect
          v-model="filters.status"
          :options="statusOptions"
          placeholder="All Status"
        />
      </div>

      <BaseButton
        v-if="hasActiveFilters"
        class="bg-gray-600 text-white hover:bg-gray-700"
        @click="resetFilters"
      >
        Reset
      </BaseButton>
    </div>

    <BaseTable
      :columns="columns"
      :rows="tableRows"
      :current-page="page"
      :per-page="perPage"
      show-actions
    >
      <template #cell-category_name="{ row }">
        <span class="font-medium text-gray-800">{{ row.category_name }}</span>
      </template>

      <template #cell-name="{ row }">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
            <i class="fa fa-list-alt text-emerald-600"></i>
          </div>
          <div>
            <p class="font-medium text-gray-900">{{ row.name }}</p>
            <p
              v-if="row.is_bills_receivable_link"
              class="mt-0.5 text-xs font-medium text-emerald-600"
            >
              Bills receivable link
            </p>
          </div>
        </div>
      </template>

      <template #cell-base_price="{ row }">
        <span class="font-semibold text-gray-900">{{ formatCurrency(row.base_price) }}</span>
      </template>

      <template #cell-linked_accounts="{ row }">
        <div v-if="row.linked_accounts?.length" class="flex flex-wrap gap-1">
          <span
            v-for="(link, index) in row.linked_accounts"
            :key="`${row.id}-${index}-${link.account_category}`"
            class="rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700"
          >
            {{ formatLinkedAccountLabel(link) }}
          </span>
        </div>
        <span v-else class="text-sm text-gray-400">Not linked</span>
      </template>

      <template #cell-status="{ row }">
        <span
          class="rounded-full px-3 py-1 text-xs font-semibold"
          :class="
            row.status === 'Active'
              ? 'bg-green-100 text-green-700'
              : 'bg-red-100 text-red-700'
          "
        >
          {{ row.status }}
        </span>
      </template>

      <template #actions="{ row }">
        <BaseTableButton
          v-can="'account_setup.view'"
          icon="fa fa-file-text-o"
          variant="primary"
          title="Expense Entry"
          @click="goToBillEntry(row)"
        />
        <BaseTableButton
          v-can="'account_setup.edit'"
          icon="fa fa-pencil"
          variant="success"
          title="Edit"
          @click="onEdit(row)"
        />
      </template>
    </BaseTable>

    <BasePagination
      :total="paginationTotal"
      :showing="showing"
      :links="links"
      :per-page="perPage"
      @update:page="setPage"
      @update:perPage="setPerPage"
    />

    <ExpenseHeadAddModal
      v-can="'transaction.view'"
      :default-category-id="filters.categoryId"
    />
    <ExpenseHeadEditModal v-can="'transaction.view'" />
  </div>
</template>

<script setup>
import { computed, defineAsyncComponent, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useExpenseHeadStore } from '@/finance/store/expenseHeadStore'
import { useExpenseCategoryStore } from '@/finance/store/expenseCategoryStore'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { formatCurrency } from '@/finance/utils/billUtils'
import { getAccountCategoryLabel } from '@/finance/data/expenseHeadAccountLinkData'

const props = defineProps({
  initialCategoryId: {
    type: [String, Number],
    default: '',
  },
})

const ExpenseHeadAddModal = defineAsyncComponent(() => import('./ExpenseHeadAddModal.vue'))
const ExpenseHeadEditModal = defineAsyncComponent(() => import('./ExpenseHeadEditModal.vue'))

const headStore = useExpenseHeadStore()
const categoryStore = useExpenseCategoryStore()
const router = useRouter()

const goToBillEntry = (row) => {
  router.push({
    path: '/finance/bills-and-purchases',
    query: {
      category_id: String(row.category_id),
      head_id: String(row.id),
    },
  })
}

const filters = reactive({
  search: '',
  categoryId: props.initialCategoryId ? String(props.initialCategoryId) : '',
  status: 'Active',
})

const statusOptions = [
  { id: 'all', name: 'All Status' },
  { id: 'Active', name: 'Active' },
  { id: 'Inactive', name: 'Inactive' },
]

const categoryFilterOptions = computed(() =>
  categoryStore.categories.map((category) => ({
    id: category.id,
    name: category.name,
  }))
)

const categoryMap = computed(() =>
  Object.fromEntries(categoryStore.categories.map((category) => [category.id, category.name]))
)

const { onEdit } = useCrudTable(headStore, [])

const columns = [
  { key: 'name', label: 'Expense Head' },
  { key: 'base_price', label: 'Base Price' },
  { key: 'category_name', label: 'Expense Category' },
  { key: 'linked_accounts', label: 'Linked Accounts' },
  { key: 'status', label: 'Status' },
]

function formatLinkedAccountLabel(link) {
  return getAccountCategoryLabel(link.account_category)
}

const page = ref(1)
const perPage = ref(10)
const showing = ref(0)
const links = ref([])

const tableRows = computed(() =>
  headStore.heads.map((head) => ({
    ...head,
    category_name: head.category_name || categoryMap.value[head.category_id] || '—',
  }))
)

const paginationTotal = computed(() => headStore.paginationMeta.total ?? 0)

const hasActiveFilters = computed(() =>
  Boolean(
    filters.search ||
      filters.categoryId ||
      (filters.status && filters.status !== 'Active')
  )
)

function buildListFilters() {
  const apiFilters = {}

  if (filters.search.trim()) {
    apiFilters.search = filters.search.trim()
  }

  if (filters.categoryId) {
    apiFilters.category_id = filters.categoryId
  }

  if (filters.status && filters.status !== 'all') {
    apiFilters.status = filters.status
  }

  return apiFilters
}

async function loadHeads() {
  await headStore.fetchHeads({
    force: true,
    page: page.value,
    perPage: perPage.value,
    filters: buildListFilters(),
  })
}

const resetFilters = () => {
  filters.search = ''
  filters.categoryId = ''
  filters.status = 'Active'
  page.value = 1
}

watch(
  () => props.initialCategoryId,
  (categoryId) => {
    filters.categoryId = categoryId ? String(categoryId) : ''
    page.value = 1
  }
)

const updatePagination = () => {
  const meta = headStore.paginationMeta
  showing.value = Number(meta.to) || 0
  links.value = Array.isArray(meta.links) ? meta.links : []

  const lastPage = Math.max(1, Number(meta.last_page) || 1)
  if (page.value > lastPage) {
    page.value = lastPage
  }
}

watch(
  () => [
    headStore.paginationMeta.total,
    headStore.paginationMeta.to,
    headStore.paginationMeta.last_page,
    headStore.paginationMeta.links,
  ],
  updatePagination,
  { immediate: true, deep: true }
)

let reloadTimer = null
function scheduleReload(resetPage = false) {
  if (resetPage && page.value !== 1) {
    page.value = 1
    return
  }

  clearTimeout(reloadTimer)
  reloadTimer = setTimeout(() => {
    loadHeads()
  }, 250)
}

watch(
  () => [filters.search, filters.categoryId, filters.status],
  () => scheduleReload(true)
)

watch([page, perPage], () => scheduleReload(false))

const setPage = (value) => {
  if (value && value !== page.value) {
    page.value = value
  }
}

const setPerPage = (value) => {
  perPage.value = Number(value)
  page.value = 1
}

onMounted(async () => {
  await categoryStore.fetchCategories()
  await loadHeads()
})
</script>
