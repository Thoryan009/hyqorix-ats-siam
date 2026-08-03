<template>
  <div :class="embedded ? '' : 'rounded-lg bg-white shadow-sm'">
    <div
      v-if="!embedded"
      class="flex flex-col items-start justify-between gap-4 border-b border-gray-100 p-4 sm:flex-row sm:items-center"
    >
      <div>
        <h3 class="text-base font-semibold text-gray-900">Expense Category Management</h3>
        <p class="mt-1 text-sm text-gray-500">
          Manage expense categories from the server
        </p>
      </div>
    </div>

    <div :class="embedded ? '' : 'p-4'">
      <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="flex flex-1 flex-col sm:min-w-[220px]">
          <label class="mb-1 text-sm text-gray-700">Search</label>
          <BaseInput v-model="filters.search" placeholder="Search categories..." />
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
        <template #cell-name="{ row }">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/10">
              <i class="fa fa-tags text-primary"></i>
            </div>
            <div>
              <p class="font-medium text-gray-900">{{ row.name }}</p>
              <p v-if="row.description" class="text-xs text-gray-500">{{ row.description }}</p>
            </div>
          </div>
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
    </div>

    <ExpenseCategoryEditModal v-can="'account_setup.edit'" />
  </div>
</template>

<script setup>
import { computed, defineAsyncComponent, onMounted, reactive, ref, watch } from 'vue'
import { useExpenseCategoryStore } from '@/finance/store/expenseCategoryStore'
import { useCrudTable } from '@/shared/composables/useCrudTable'

defineProps({
  embedded: {
    type: Boolean,
    default: false,
  },
})

const ExpenseCategoryEditModal = defineAsyncComponent(
  () => import('./ExpenseCategoryEditModal.vue')
)

const store = useExpenseCategoryStore()

const filters = reactive({
  search: '',
  status: '',
})

const statusOptions = [
  { id: 'Active', name: 'Active' },
  { id: 'Inactive', name: 'Inactive' },
]

const { onEdit } = useCrudTable(store, [])

const columns = [
  { key: 'name', label: 'Category Name' },
  { key: 'status', label: 'Status' },
]

const page = ref(1)
const perPage = ref(10)
const showing = ref(0)
const links = ref([])

const filteredRows = computed(() => {
  const query = filters.search.trim().toLowerCase()

  return store.categories.filter((category) => {
    const matchesSearch =
      !query ||
      category.name.toLowerCase().includes(query) ||
      String(category.description ?? '')
        .toLowerCase()
        .includes(query)

    const matchesStatus = !filters.status || category.status === filters.status

    return matchesSearch && matchesStatus
  })
})

const paginatedRows = computed(() => {
  const start = (page.value - 1) * perPage.value
  return filteredRows.value.slice(start, start + perPage.value)
})

const hasActiveFilters = computed(() => Boolean(filters.search || filters.status))

const resetFilters = () => {
  filters.search = ''
  filters.status = ''
  page.value = 1
}

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

watch([filteredRows, page, perPage, () => store.categories.length], updatePagination, {
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
  await store.fetchCategories()
})
</script>
