<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="capitalize">
        <PageTitle>{{ t('employees.management') }}</PageTitle>
      </div>

      <div class="flex items-center gap-2">
        <div class="flex items-center rounded-lg border border-gray-200 bg-white overflow-hidden">
          <button
            @click="viewMode = 'table'"
            :class="viewMode === 'table' ? 'bg-primary text-white' : 'text-gray-500 hover:bg-gray-50'"
            class="flex h-9 w-9 items-center justify-center transition-colors"
            :title="t('employees.table_view')"
          >
            <i class="fa fa-table text-sm"></i>
          </button>
          <button
            @click="viewMode = 'grid'"
            :class="viewMode === 'grid' ? 'bg-primary text-white' : 'text-gray-500 hover:bg-gray-50'"
            class="flex h-9 w-9 items-center justify-center transition-colors"
            :title="t('employees.grid_view')"
          >
            <i class="fa fa-th-large text-sm"></i>
          </button>
        </div>

        <BaseButton v-can="'employee.create'" @click="store.handleToggleModal('add')">
          {{ t('employees.add') }}
        </BaseButton>
      </div>
    </div>
    <router-link to="/employee-performance" class="mb-4 inline-flex no-underline w-fit">
      <div class="px-4 py-2 rounded-xl border cursor-pointer transition-all bg-white text-gray-700 shadow-sm hover:shadow-md hover:-translate-y-0.5">
        {{ t('employees.performance') }}
      </div>
    </router-link>
    <!-- Bulk Delete & Filters -->
    <div class="flex justify-between items-center my-4">
      <div>
        <BaseButton
          v-if="selectedIds.length"
          v-can="'employee.delete'"
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
        :available-filters="availableFilters"
        @reset="resetFilters"
      />
    </div>

    <!-- Content Card -->
    <div class="rounded-lg bg-white shadow-sm">
      <div>
        <div v-if="viewMode === 'table'">
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
            <template #cell-image_url="{ row }">
              <img
                v-if="row.image_url"
                :src="row.image_url"
                class="w-9 h-9 rounded-full object-cover border border-gray-200"
                :alt="t('employees.module')"
              />
              <div v-else class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center">
                <i class="fa fa-user text-gray-400 text-sm"></i>
              </div>
            </template>
            <template #actions="{ row }">
              <button v-can="'employee.view'" @click="onView(row)" class="text-blue-600 cursor-pointer" :title="t('shared.actions.view')">
                <i class="fa fa-eye"></i>
              </button>
              <button v-can="'employee.edit'" @click="onEdit(row)" class="text-green-600 cursor-pointer" :title="t('shared.actions.edit')">
                <i class="fa fa-pencil"></i>
              </button>
              <button v-can="'employee.delete'" @click="confirmDelete(row.id)" class="text-red-600 cursor-pointer" :title="t('shared.actions.delete')">
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
        </div>

        <EmployeeGridView
          v-else
          :rows="rows"
          :is-loading="isLoading"
          :selected-ids="selectedIds"
          :total="total"
          :showing="showing"
          :links="links"
          :per-page="perPage"
          @onView="onView"
          @onEdit="onEdit"
          @onDelete="confirmDelete"
          @toggleRow="toggleRow"
          @update:page="setPage"
          @update:perPage="setPerPage"
        />

        <FormModal
          v-if="can('employee.create') || can('employee.edit')"
          :extra-data="extraData"
        />
        <ViewModal v-if="can('employee.view')" :store="store" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useEmployeeStore } from '../stores/employeeStore'
import { useTranslate } from '@/shared/composables/useTranslate'
import { usePermission } from '@/shared/composables/usePermission'

import { usePagination } from '@/shared/composables/usePagination'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'

import TableFilters from '@/shared/components/ui/TableFilters.vue'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useCrudMutations } from '@/shared/composables/useCrudMutations'
import { useCrudQuery } from '@/shared/composables/useCrudQuery'
import { useSyncDynamicFilters } from '@/shared/composables/useSyncDynamicFilters'
import FormModal from './components/FormModal.vue'
import ViewModal from './components/ViewModal.vue'
import EmployeeGridView from './components/EmployeeGridView.vue'

const { t } = useTranslate()
const { can } = usePermission()
const store = useEmployeeStore()
const viewMode = ref('table')

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, rows, extraData, tableColumns, availableFilters, isLoading } = useCrudQuery(
  store.moduleName,
  page,
  perPage,
  filters,
)

pagination.bindMeta(data)
useSyncDynamicFilters(availableFilters, filters)

const { remove, removeItems, removeItemsLoading } = useCrudMutations(store.moduleName)
const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems)

const computedFields = computed(() => {
  const cols = tableColumns.value
  return Array.isArray(cols) ? cols : []
})

const { confirmDelete } = useDeleteWithConfirm(remove)
const { columns, onView, onEdit } = useCrudTable(store, computedFields)
</script>
