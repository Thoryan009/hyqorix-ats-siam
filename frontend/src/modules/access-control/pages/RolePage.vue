<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <PageTitle>{{t('role.management')}}</PageTitle>
      </div>
      <BaseButton v-can="'role.create'" @click="store.handleToggleModal('add')">{{t('role.add')}}</BaseButton>
    </div>

    <!-- Bulk Delete & Filters -->
    <div class="flex justify-between items-center my-4">
      <!-- BULK DELETE -->
      <div>
        <BaseButton
          v-can="'role.delete'"
          v-if="selectedIds.length"
          class="bg-red-600 text-white hover:bg-red-700"
          @click="bulkDelete"
          :disabled="removeItemsLoading"
        >
          <span v-if="removeItemsLoading">{{t('shared.messages.deleting')}}</span>
          <span v-else>{{t('shared.messages.delete_selected', { count: selectedIds.length })}}</span>
        </BaseButton>
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
      <BaseTableSkeleton v-if="isLoading" :columns="columns.length" :rows="perPage" />
      <BaseTable
        v-else
        :columns="columns"
        :rows="rows"
        show-actions
        selectable
        :selected-ids="selectedIds"
        @toggleAll="(checked) => handleToggleAll(rows, checked)"
        @toggleRow="handleToggleRow"
      >
        <template #actions="{ row }">
          <button v-can="'role.view'" @click="onView(row)" class="text-blue-600 cursor-pointer">
            <i class="fa fa-eye"></i>
          </button>

          <button v-can="'role.edit'" @click="onEdit(row)" class="text-green-600 cursor-pointer">
            <i class="fa fa-pencil"></i>
          </button>

          <button
            v-if="!isProtectedRole(row)"
            v-can="'role.delete'"
            @click="confirmDelete(row.id)"
            class="text-red-600 cursor-pointer"
            title="Delete"
          >
            <i class="fa fa-trash"></i>
          </button>
          <span
            v-else
            class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-700"
            title="Admin role cannot be deleted"
          >
            Protected
          </span>
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

      <AddModal v-can="'role.create'" />
      <EditModal v-can="'role.edit'" />
      <ViewModal :rows="rows" v-can="'role.view'" />
      <DeleteModal v-can="'role.delete'" />
    </div>
  </div>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { useRoleStore } from '@/modules/access-control/stores/RoleStore'
import { useRoleQuery } from '@/modules/access-control/queries/useRoleQuery'
import { useRoleMutations } from '@/modules/access-control/queries/useRoleMutations'

import { usePagination } from '@/shared/composables/usePagination'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'

import TableFilters from '@/shared/components/ui/TableFilters.vue'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import {useTranslate} from '@/shared/composables/useTranslate'
const { t } = useTranslate()

const ViewModal = defineAsyncComponent(() => import('./components/ViewModal.vue'))
const AddModal = defineAsyncComponent(() => import('./components/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./components/EditModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./components/DeleteModal.vue'))

const store = useRoleStore()

/* ---------------- Filters ---------------- */
const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  from_date: null,
  to_date: null,
})

/* ---------------- Pagination ---------------- */
const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

/* ---------------- Query ---------------- */
const { data, rows, isLoading } = useRoleQuery(page, perPage, filters)
pagination.bindMeta(data)

/* ---------------- Mutations ---------------- */
const { remove, removeItems, removeItemsLoading } = useRoleMutations(store.moduleName)
/* ---------------- Bulk Delete ---------------- */
const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems, {
  confirmText: 'Are you sure to delete selected records?',
})

function isProtectedRole(row) {
  return Boolean(row?.is_protected) || String(row?.slug || '').toLowerCase() === 'admin'
}

function handleToggleAll(currentRows, checked) {
  const deletableRows = (currentRows || []).filter((row) => !isProtectedRole(row))
  toggleAll(deletableRows, checked)
}

function handleToggleRow(id) {
  const row = (rows.value || []).find((item) => Number(item.id) === Number(id))
  if (row && isProtectedRole(row)) return
  toggleRow(id)
}

/* ---------------- Table ---------------- */

const { confirmDelete: confirmDeleteRaw } = useDeleteWithConfirm(remove)

function confirmDelete(id) {
  const row = (rows.value || []).find((item) => Number(item.id) === Number(id))
  if (row && isProtectedRole(row)) return
  confirmDeleteRaw(id)
}


const columnTemp = computed(() => {
  return [
    { key: 'name', label: t('role.role_name') },
  ]
})
const { columns, onView, onEdit } = useCrudTable(
  store,
  columnTemp,
  { timestamps: false, trackUser: false }
)
</script>
