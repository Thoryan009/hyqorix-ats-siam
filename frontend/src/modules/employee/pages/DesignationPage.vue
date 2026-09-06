<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <PageTitle>{{ t('designations.management') }}</PageTitle>
      </div>
      <BaseButton v-can="'designation.create'" @click="store.handleToggleModal('add')"
        >{{ t('designations.add') }}</BaseButton
      >
    </div>

    <!-- Bulk Delete & Filters -->
    <div class="flex justify-between items-center my-4">
      <!-- BULK DELETE -->
      <div>
        <BaseButton
          v-if="selectedIds.length"
          v-can="'designation.delete'"
          class="bg-red-600 text-white hover:bg-red-700"
          @click="bulkDelete"
          >{{ t('shared.messages.delete_selected', { count: selectedIds.length }) }}</BaseButton
        >
      </div>

      <!-- FILTERS -->
      <TableFilters
        :filters="filters"
        :has-active-filters="hasActiveFilters"
        @reset="resetFilters"
      />
    </div>

    <!-- Content Card -->
    <div class="rounded-lg bg-white shadow-sm">
      <div>
        <!-- Table -->
        <BaseTable
          v-if="!isLoading"
          :columns="columns"
          :rows="rows"
          :current-page="page"
          :per-page="perPage"
          show-actions
          selectable
          :selected-ids="selectedIds"
          @toggleAll="(checked) => toggleAll(rows, checked)"
          @toggleRow="toggleRow"
        >
          <template #actions="{ row }">
            <button
              v-can="'designation.view'"
              @click="onView(row)"
              class="text-blue-600 cursor-pointer"
            >
              <i class="fa fa-eye"></i>
            </button>

            <button
              v-can="'designation.edit'"
              @click="onEdit(row)"
              class="text-green-600 cursor-pointer"
            >
              <i class="fa fa-pencil"></i>
            </button>

            <button
              v-can="'designation.delete'"
              @click="confirmDelete(row.id)"
              class="text-red-600 cursor-pointer"
            >
              <i class="fa fa-trash"></i>
            </button>
          </template>
        </BaseTable>

        <!-- Pagination -->
        <BasePagination
          v-if="!isLoading"
          :total="total"
          :showing="showing"
          :links="links"
          :per-page="perPage"
          @update:page="setPage"
          @update:perPage="setPerPage"
        />

        <!-- Modals -->
        <AddModal v-can="'designation.create'" />
        <EditModal v-can="'designation.edit'" />
        <ViewModal v-can="'designation.view'" />
        <DeleteModal v-can="'designation.delete'" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'

// Queries & Store
import { useDesignationsQuery } from '../queries/useDesignationsQuery'
import { useDesignationStore } from '../stores/designationStore'
import { useDesignationMutations } from '../queries/useDesignationMutations'

// Shared Composables
import { usePagination } from '@/shared/composables/usePagination'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'

// Components
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'

// Lazy Loaded Modals
const ViewModal = defineAsyncComponent(() => import('./designationParts/ViewModal.vue'))
const AddModal = defineAsyncComponent(() => import('./designationParts/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./designationParts/EditModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./designationParts/DeleteModal.vue'))

import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()

// Store
const store = useDesignationStore()

// Filters
const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  from_date: null,
  to_date: null,
})

// Pagination
const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

// Query
const { data, isLoading } = useDesignationsQuery(page, perPage, filters)

// Bind Meta
pagination.bindMeta(data)

// Mutations
const { remove, removeItems } = useDesignationMutations(store.moduleName)

// Delete with Confirmation
const { confirmDelete } = useDeleteWithConfirm(remove)

// Bulk Delete
const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems, {
  confirmText: t('designations.bulk_delete_confirm'),
})

// Table Columns & Actions
const { columns, onView, onEdit } = useCrudTable(store, [
  { key: 'name', label: t('designations.module') },
  { key: 'employees_count', label: t('designations.employees') },
])

// Rows
const rows = computed(() => data.value?.data?.data ?? [])
</script>
