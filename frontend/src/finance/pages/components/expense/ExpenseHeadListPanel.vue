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
      :rows="paginatedRows"
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
      :total="filteredRows.length"
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

const enrichedHeads = computed(() =>
  headStore.heads.map((head) => ({
    ...head,
    category_name: categoryMap.value[head.category_id] ?? '—',
  }))
)

const filteredRows = computed(() => {
  const query = filters.search.trim().toLowerCase()

  return enrichedHeads.value.filter((head) => {
    const matchesSearch =
      !query ||
      head.name.toLowerCase().includes(query) ||
      head.category_name.toLowerCase().includes(query)

    const matchesCategory =
      !filters.categoryId || Number(head.category_id) === Number(filters.categoryId)

    const matchesStatus =
      !filters.status ||
      filters.status === 'all' ||
      head.status === filters.status

    return matchesSearch && matchesCategory && matchesStatus
  })
})

const paginatedRows = computed(() => {
  const start = (page.value - 1) * perPage.value
  return filteredRows.value.slice(start, start + perPage.value)
})

const hasActiveFilters = computed(() =>
  Boolean(
    filters.search ||
      filters.categoryId ||
      (filters.status && filters.status !== 'Active')
  )
)

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

watch([filteredRows, page, perPage, () => headStore.heads.length], updatePagination, {
  immediate: true,
})

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
  await Promise.all([categoryStore.fetchCategories(), headStore.fetchHeads()])
})
</script>
