<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div class="flex flex-col">
        <!-- Responsive Heading -->
        <PageTitle> {{ t('demand_letter.management') }}</PageTitle>
        <!-- <OrderQuickLinks /> -->
      </div>
      <BaseButton v-can="'demand_letter.create'" @click="store.handleToggleModal('add')"> {{ t('demand_letter.add') }}</BaseButton>
    </PageHeader>
    <!-- Bulk Delete & All Kinds of Filters -->
    <div class="flex justify-start md:justify-between items-center my-4">
      <!-- BULK DELETE -->
      <div>
        <BaseButton
          v-can="'demand_letter.delete'"
          v-if="selectedIds.length"
          class="bg-red-600 text-white hover:bg-red-700"
          @click="bulkDelete"
          :disabled="removeItemsLoading"
        >
          <span v-if="removeItemsLoading">{{t('shared.messages.deleting')}}</span>
          <span v-else>{{ t('shared.messages.delete_selected', { count: selectedIds.length }) }}</span>
        </BaseButton>
      </div>
      <div></div>
      <!-- FILTERS -->
      <TableFilters
        :filters="filters"
        :has-active-filters="hasActiveFilters"
        @reset="resetFilters"
      >
        <div
          class="flex flex-col sm:min-w-50"
          v-if="authStore.userType != 'client' && authStore.userType != 'agent'"
        >
          <label class="text-gray-800 text-sm sm:text-[15px] mb-1">{{t('shared.labels.client')}}</label>
          <BaseSearchSelect
            v-model="filters.client_id"
            :options="clientOptions"
            :placeholder="t('application.search_by_client_name')"
            option-label="name"
            option-value="id"
            :filter-fn="filterBySearchName"
          />
        </div>
        <div
          class="flex flex-col sm:min-w-50"
          v-if="authStore.userType != 'client' && authStore.userType != 'agent'"
        >
          <label class="text-gray-800 text-sm sm:text-[15px] mb-1">{{t('demand_letter.assign_to')}}</label>
          <BaseSearchSelect
            v-model="filters.employee_id"
            :options="employeeOptions"
            :placeholder="t('application.search_by_assigner_name')"
            option-label="name"
            option-value="id"
            :filter-fn="filterBySearchName"
          />
        </div>
      </TableFilters>
    </div>



    <!-- Content Card -->
    <div class="rounded-lg bg-white shadow-sm">
      <div>
        <BaseTableSkeleton v-if="isLoading" :columns="columns" selectable show-actions />
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
        >
          <template #actions="{ row }">
            <BaseTableButton icon="fa fa-eye" variant="primary" title="View" @click="onView(row)" v-can="'demand_letter.view'" />
            <BaseTableButton
              v-can="'demand_letter.edit'"
              icon="fa fa-pencil"
              variant="success"
              title="Edit"
              @click="onEdit(row)"
            />
            <BaseTableButton
              v-can="'demand_letter.delete'"
              icon="fa fa-trash"
              variant="danger"
              title="Delete"
              @click="confirmDelete(row.id)"
            />
          </template>
        </BaseTable>

        <!-- Pagination, Per Page & Showing Records -->
        <BasePagination
          v-if="!isLoading"
          :total="total"
          :showing="showing"
          :links="links"
          :per-page="perPage"
          @update:page="setPage"
          @update:perPage="setPerPage"
        />

        <AddModal :employees="employees" v-can="'demand_letter.create'" />
        <EditModal :employees="employees" v-can="'demand_letter.edit'" />
        <ViewModal v-can="'demand_letter.view'" />
        <DeleteModal v-can="'demand_letter.delete'" />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import {
  useWorkOrderClientsQuery,
  useWorkOrderDataQuery,
  useWorkOrdersQuery,
} from '../queries/useWorkOrdersQuery'
import { useWorkOrderStore } from '../store/workOrderStore'
import { usePagination } from '@/shared/composables/usePagination'
import { useWorkOrderMutations } from '../queries/useWorkOrderMutations'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useAuthStore } from '@/modules/auth/store/authStore'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('work_order')

const ViewModal = defineAsyncComponent(() => import('./components/ViewModal.vue'))
const AddModal = defineAsyncComponent(() => import('./components/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./components/EditModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./components/DeleteModal.vue'))

const store = useWorkOrderStore()
const authStore = useAuthStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  client_id: null,
  employee_id: null,
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = useWorkOrdersQuery(page, perPage, filters)
const { data: clientsData } = useWorkOrderClientsQuery()
const { data: workOrderData } = useWorkOrderDataQuery()

pagination.bindMeta(data)

const {remove, removeItems, removeItemsLoading } = useWorkOrderMutations(store.moduleName)

const { confirmDelete } = useDeleteWithConfirm(remove)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems, {
  confirmText: `Are you sure to Delete selected records?`,
})

const columnTemp = computed(() => {
  return [
    { key: 'work_order_id', label: t('shared.labels.demand_letter_id') },
    { key: 'candidates', label: t('shared.labels.candidates') },
    { key: 'visa_issue_number', label: t('demand_letter.visa_issue_number') },
    { key: 'sponsor_id', label: t('demand_letter.sponsor_id') },
    { key: 'end_date_formatted', label: t('demand_letter.end_date') },
    { key: 'client', label: t('shared.labels.client') },
    { key: 'employee', label: t('demand_letter.assign_to') },
  ]
})
const { columns, onView, onEdit } = useCrudTable(
  store,
  columnTemp,
  {
    trackUser: false,
    timestamps: false,
  },
)

const rows = computed(() => data.value?.data?.data ?? [])
const employees = computed(() => workOrderData.value?.data?.data?.employees ?? [])

const truncateName = (name, maxLength = 20) => {
  const value = name ?? 'N/A'
  if (value.length <= maxLength) return value
  return `${value.slice(0, maxLength - 3)}...`
}

const clientOptions = computed(() => {
  const clients = clientsData.value?.data?.data ?? []

  return clients.map((client) => ({
    ...client,
    name: `${truncateName(client.name)} (${client.work_orders_count ?? 0})`,
    search_name: client.name,
  }))
})

const employeeOptions = computed(() => {
  const assignees = workOrderData.value?.data?.data?.filter_employees ?? []

  return assignees.map((employee) => ({
    ...employee,
    name: `${employee.name ?? 'N/A'} (${employee.work_orders_count ?? 0})`,
    search_name: employee.name,
  }))
})

const filterBySearchName = (option, query) => {
  return (option.search_name ?? option.name ?? '').toLowerCase().includes(query)
}
</script>
