<template>
  <div class="min-h-screen bg-gray-50">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="capitalize">
        <PageTitle>{{ t('permission.management') }}</PageTitle>
      </div>
      <BaseButton v-can="'permission.create'" @click="store.handleToggleModal('add')">
        {{ t('permission.add_by_module') }}
      </BaseButton>
    </div>

    <div class="flex justify-between items-center my-4">
      <div>
        <BaseButton
          v-can="'permission.delete'"
          v-if="selectedIds.length"
          class="bg-red-600 text-white hover:bg-red-700"
          @click="bulkDelete"
          :disabled="removeItemsLoading"
        >
          <span v-if="removeItemsLoading">{{ t('shared.messages.deleting') }}</span>
          <span v-else>{{ t('shared.messages.delete_selected', { count: selectedIds.length }) }}</span>
        </BaseButton>
      </div>

      <TableFilters
        :filters="filters"
        :has-active-filters="hasActiveFilters"
        @reset="resetFilters"
      />
    </div>

    <div class="rounded-lg bg-white shadow-sm">
      <BaseTableSkeleton v-if="isLoading" :columns="columns.length" :rows="perPage" />
      <BaseTable
        v-else
        :columns="columns"
        :rows="rows"
        show-actions
        selectable
        :selected-ids="selectedIds"
        @toggleAll="(checked) => toggleAll(rows, checked)"
        @toggleRow="toggleRow"
      >
        <template #actions="{ row }">
          <button
            v-can="'permission.view'"
            @click="onView(row)"
            class="text-blue-600 cursor-pointer"
            :title="t('shared.actions.view')"
          >
            <i class="fa fa-eye"></i>
          </button>

          <button
            v-can="'permission.edit'"
            @click="onEdit(row)"
            class="text-green-600 cursor-pointer"
            :title="t('shared.actions.edit')"
          >
            <i class="fa fa-pencil"></i>
          </button>

          <button
            v-can="'permission.delete'"
            @click="confirmDelete(row.id)"
            class="text-red-600 cursor-pointer"
            :title="t('shared.actions.delete')"
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

      <AddModal v-can="'permission.create'" />
      <EditModal v-can="'permission.edit'" />
      <ViewModal v-can="'permission.view'" />
    </div>
  </div>
</template>

<script setup>
import { defineAsyncComponent } from 'vue'
import { usepermissionStore } from '@/modules/access-control/stores/permissionStore'
import { usePermissionQuery } from '@/modules/access-control/queries/usePermissionQuery'
import { usePermissionMutations } from '@/modules/access-control/queries/usePermissionMutations'
import { useTranslate } from '@/shared/composables/useTranslate'

import { usePagination } from '@/shared/composables/usePagination'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'

import TableFilters from '@/shared/components/ui/TableFilters.vue'

const ViewModal = defineAsyncComponent(() => import('./permissionParts/ViewModal.vue'))
const AddModal = defineAsyncComponent(() => import('./permissionParts/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./permissionParts/EditModal.vue'))

const { t } = useTranslate()
const store = usepermissionStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, rows, isLoading } = usePermissionQuery(page, perPage, filters)
pagination.bindMeta(data)

const { remove, removeItems, removeItemsLoading } = usePermissionMutations(store.moduleName)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems, {
  confirmText: t('permission.bulk_delete_confirm'),
})

const { confirmDelete } = useDeleteWithConfirm(remove)

const { columns, onView, onEdit } = useCrudTable(
  store,
  [
    { key: 'name', label: t('permission.permission') },
    { key: 'slug', label: t('permission.slug') },
  ],
  {
    timestamps: false,
    trackUser: false,
  }
)
</script>
