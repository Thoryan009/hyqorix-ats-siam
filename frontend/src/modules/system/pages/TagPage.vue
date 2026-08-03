<template>
  <div class="min-h-screen bg-gray-50">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="capitalize">
        <PageTitle>{{ store.moduleName }} Management</PageTitle>
      </div>
      <BaseButton
        v-can="'tag.create'"
        @click="store.handleToggleModal('add')"
      >Add {{ store.moduleName }}</BaseButton>
    </div>

    <div class="flex justify-between items-center my-4">
      <div>
        <BaseButton
          v-if="selectedIds.length"
          v-can="'tag.delete'"
          class="bg-red-600 text-white hover:bg-red-700"
          @click="bulkDelete"
          :disabled="removeItemsLoading"
        >
          <span v-if="removeItemsLoading">Deleting...</span>
          <span v-else>Delete Selected ({{ selectedIds.length }})</span>
        </BaseButton>
      </div>

      <TableFilters
        :filters="filters"
        :has-active-filters="hasActiveFilters"
        :show-date-filters="false"
        @reset="resetFilters"
      />
    </div>

    <div class="rounded-lg bg-white shadow-sm overflow-hidden">
      <BaseTableSkeleton v-if="isLoading" :columns="columns.length" :rows="perPage" />
      <BaseTable
        v-else
        :columns="columns"
        :rows="rows"
        :current-page="page"
        :per-page="perPage"
        show-actions
        selectable
        :selected-ids="selectedIds"
        @toggleAll="(checked) => toggleAll(rows, checked)"
        @toggleRow="toggleRow"
        @onRowClick="onView"
      >
        <template #cell-name="{ row }">
          <div class="flex items-center gap-2">
            <span
              class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 font-bold text-xs"
            >{{ (row.name || '').slice(0, 2).toUpperCase() }}</span>
            <span class="font-medium text-gray-700">{{ row.name }}</span>
          </div>
        </template>

        <template #actions="{ row }">
          <button v-can="'tag.view'" @click="onView(row)" class="text-blue-600 cursor-pointer">
            <i class="fa fa-eye"></i>
          </button>
          <button v-can="'tag.edit'" @click="onEdit(row)" class="text-green-600 cursor-pointer">
            <i class="fa fa-pencil"></i>
          </button>
          <button
            v-can="'tag.delete'"
            @click="confirmDelete(row.id)"
            class="text-red-600 cursor-pointer"
          >
            <i class="fa fa-trash"></i>
          </button>
        </template>
      </BaseTable>

      <BasePagination
        v-if="!isLoading"
        :total="total"
        :showing="showing"
        :links="links"
        :per-page="perPage"
        @update:page="setPage"
        @update:perPage="setPerPage"
      />

      <FormModal v-can="['tag.create', 'tag.edit']" :store="store" />
      <ViewModal v-can="'tag.view'" :store="store" />
    </div>
  </div>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { useTagStore } from '@/modules/system/stores/TagStore'
import { usePagination } from '@/shared/composables/usePagination'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useCrudMutations } from '@/shared/composables/useCrudMutations'
import { useCrudQuery } from '@/shared/composables/useCrudQuery'

import TableFilters from '@/shared/components/ui/TableFilters.vue'

const FormModal = defineAsyncComponent(() => import('./TagParts/FormModal.vue'))
const ViewModal = defineAsyncComponent(() => import('./TagParts/ViewModal.vue'))

const store = useTagStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({ searchQuery: '' })

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, rows, tableColumns, isLoading } = useCrudQuery(
  store.moduleName,
  page,
  perPage,
  filters
)
pagination.bindMeta(data)

const { remove, removeItems, removeItemsLoading } = useCrudMutations(store.moduleName)
const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems)

const computedFields = computed(() => {
  const cols = tableColumns.value
  return Array.isArray(cols) ? cols : []
})

const { confirmDelete } = useDeleteWithConfirm(remove)
const { columns, onView, onEdit } = useCrudTable(store, computedFields, { timestamps: true })
</script>
