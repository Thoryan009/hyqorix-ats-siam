<template>
  <div class="min-h-screen bg-gray-50">
    <!-- <pre>{{ rows }}</pre> -->
    <!-- Dashboard Stats Cards -->

    <!-- <EmployeeDashboardCards :data="dashboardData" /> -->

    <!-- Page Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="capitalize">
        <PageTitle>{{ store.moduleName }} Management</PageTitle>
      </div>



      <div class="flex items-center gap-2">
        <div class="flex items-center rounded-lg border border-gray-200 bg-white overflow-hidden">
          <button
            @click="viewMode = 'table'"
            :class="viewMode === 'table' ? 'bg-primary text-white' : 'text-gray-500 hover:bg-gray-50'"
            class="flex h-9 w-9 items-center justify-center transition-colors"
            title="Table View"
          >
            <i class="fa fa-table text-sm"></i>
          </button>
          <button
            @click="viewMode = 'grid'"
            :class="viewMode === 'grid' ? 'bg-primary text-white' : 'text-gray-500 hover:bg-gray-50'"
            class="flex h-9 w-9 items-center justify-center transition-colors"
            title="Grid View"
          >
            <i class="fa fa-th-large text-sm"></i>
          </button>
        </div>

        <BaseButton v-can="'employee.create'" @click="store.handleToggleModal('add')">
          Add {{ store.moduleName }}
        </BaseButton>
      </div>
    </div>
      <router-link to="/employee-performance" class="no-underline">
        <div class="px-4 py-2 rounded-xl border cursor-pointer transition-all bg-white text-gray-700 w-fit shadow-sm hover:shadow-md hover:-translate-y-0.5">
          Employee Performance
        </div>
      </router-link>
    <!-- Bulk Delete & Filters -->
    <div class="flex justify-between items-center my-4">
      <!-- BULK DELETE -->

      <div>
        <BaseButton
          v-if="selectedIds.length"
          v-can="'employee.delete'"
          class="bg-red-600 text-white hover:bg-red-700"
          @click="bulkDelete"
          :disabled="removeItemsLoading"
        >
          <span v-if="removeItemsLoading">Deleting...</span>
          <span v-else>Delete Selected ({{ selectedIds.length }})</span>
        </BaseButton>
      </div>

      <!-- FILTERS -->
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
        <!-- Table View -->
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
              alt="worker"
            />
            <div v-else class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center">
              <i class="fa fa-user text-gray-400 text-sm"></i>
            </div>
          </template>
            <template #actions="{ row }">
              <button v-can="'employee.view'" @click="onView(row)" class="text-blue-600 cursor-pointer">
                <i class="fa fa-eye"></i>
              </button>
              <button v-can="'employee.edit'" @click="onEdit(row)" class="text-green-600 cursor-pointer">
                <i class="fa fa-pencil"></i>
              </button>
              <button v-can="'employee.delete'" @click="confirmDelete(row.id)" class="text-red-600 cursor-pointer">
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
        </div>

        <!-- Grid View -->
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

        <!-- Modals -->
        <FormModal :extraData="extraData" :store="store" v-can="['employee.create', 'employee.edit']" />
        <ViewModal :store="store" v-can="'employee.view'" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, defineAsyncComponent, ref } from 'vue'
import { useEmployeeStore } from '../stores/employeeStore'

import { usePagination } from '@/shared/composables/usePagination'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'

import TableFilters from '@/shared/components/ui/TableFilters.vue'
import EmployeeDashboardCards from './components/viewParts/EmployeeDashboardCards.vue'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useCrudMutations } from '@/shared/composables/useCrudMutations'
import { useCrudQuery } from '@/shared/composables/useCrudQuery'
import { useSyncDynamicFilters } from '@/shared/composables/useSyncDynamicFilters'

// Lazy Loaded Modals
const ViewModal = defineAsyncComponent(() => import('./components/ViewModal.vue'))
const FormModal = defineAsyncComponent(() => import('./components/FormModal.vue'))
import EmployeeGridView from './components/EmployeeGridView.vue'
import router from '@/router'

// Store
const store = useEmployeeStore()

const viewMode = ref('table')

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
const { data, rows, extraData, tableColumns, dashboardData, availableFilters, isLoading } = useCrudQuery(
  store.moduleName,
  page,
  perPage,
  filters,
)

pagination.bindMeta(data)
useSyncDynamicFilters(availableFilters, filters)

/* ---------------- Mutations ---------------- */
const { remove, removeItems, removeItemsLoading } = useCrudMutations(store.moduleName)

/* ---------------- Bulk Delete ---------------- */
const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems)

/* ---------------- Table ---------------- */

const computedFields = computed(() => {
  const cols = tableColumns.value
  return Array.isArray(cols) ? cols : []
})

const { confirmDelete } = useDeleteWithConfirm(remove)

const { columns, onView, onEdit } = useCrudTable(store, computedFields)
</script>
