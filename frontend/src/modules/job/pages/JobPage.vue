<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <!-- Responsive Heading -->
        <PageTitle>{{ t('job.management') }}</PageTitle>
      </div>
      <BaseButton v-can="'job.create'" @click="store.handleToggleModal('add')"
        >{{ t('job.add') }}</BaseButton
      >
    </PageHeader>

    <!-- Bulk Delete & All Kinds of Filters -->
    <div class="flex justify-between items-center my-4">
      <!-- BULK DELETE -->
      <div v-if="role != 'client'">
        <BaseButton
          v-can="'job.delete'"
          v-if="selectedIds.length"
          class="bg-red-600 text-white hover:bg-red-700"
          @click="bulkDelete"
          :disabled="removeItemsLoading"
        >
          <span v-if="removeItemsLoading">{{t('shared.messages.deleting')}}</span>
          <span v-else>{{ t('shared.messages.delete_selected', { count: selectedIds.length }) }}</span></BaseButton
        >
      </div>

      <!-- FILTERS -->
      <TableFilters :filters="filters" :has-active-filters="hasActiveFilters" @reset="resetFilters">
        <div v-if="authStore.userType != 'agent'" class="flex flex-col sm:min-w-[220px]">
          <label class="text-gray-800 text-[15px]">{{t('shared.labels.demand_letter')}}</label>
          <BaseSearchSelect
            v-model="filters.work_order_id"
            :options="workOrderOptions"
            :placeholder="t('application.search_by_demand_letter_or_client')"
            option-label="name"
            option-value="id"
            :filter-fn="filterWorkOrder"
          />
        </div>
        <div v-if="authStore.userType != 'client'" class="flex flex-col sm:min-w-[200px]">
          <label class="text-gray-800 text-[15px]">{{t('shared.labels.client')}}</label>
          <BaseSearchSelect
            v-model="filters.client_id"
            :options="clientOptions"
            :placeholder="t('application.search_by_client_name')"
            option-label="name"
            option-value="id"
            :filter-fn="filterBySearchName"
          />
        </div>
        <div v-if="authStore.userType != 'client'" class="flex flex-col sm:min-w-[200px]">
          <label class="text-gray-800 text-[15px]">{{t('shared.labels.principal')}}</label>
          <BaseSearchSelect
            v-model="filters.principal_id"
            :options="principalOptions"
            :placeholder="t('application.search_by_principal_name')"
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
            <router-link
              v-can="'job.price_details'"
              :to="`/jobs/${row.id}/details`"
              class="text-yellow-600 cursor-pointer"
            >
              <i class="fa fa-usd"></i>
            </router-link>
            <BaseTableButton
              icon="fa fa-eye"
              variant="primary"
              title="View"
              @click="onView(row)"
              v-can="'job.view'"
            />
            <BaseTableButton
              icon="fa fa-pencil"
              variant="success"
              title="Edit"
              @click="onEdit(row)"
              v-can="'job.edit'"
            />
            <BaseTableButton
              icon="fa fa-trash"
              variant="danger"
              title="Delete"
              @click="confirmDelete(row.id)"
              v-can="'job.delete'"
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

        <!-- <pre>{{ principals }}</pre> -->

        <AddModal :workOrders="work_orders" :principals="principals" v-can="'job.create'" />
        <EditModal :workOrders="work_orders" :principals="principals" v-can="'job.edit'" />
        <ViewModal :principals="principals" v-can="'job.view'" />
        <DeleteModal v-can="'job.delete'" />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { useJobsQuery, useJobListDataQuery } from '../queries/useJobsQuery'
import { useJobStore } from '../store/jobStore'
import { usePagination } from '@/shared/composables/usePagination'
import { useJobMutations } from '../queries/useJobMutations'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useAuthStore } from '@/modules/auth/store/authStore'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('job')

const ViewModal = defineAsyncComponent(() => import('./components/ViewModal.vue'))
const AddModal = defineAsyncComponent(() => import('./components/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./components/EditModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./components/DeleteModal.vue'))

const store = useJobStore()
const authStore = useAuthStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  work_order_id: null,
  client_id: null,
  principal_id: null,
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = useJobsQuery(page, perPage, filters)
const { data: jobsData } = useJobListDataQuery()

pagination.bindMeta(data)

const { remove, removeItems, removeItemsLoading } = useJobMutations(store.moduleName)

const { confirmDelete } = useDeleteWithConfirm(remove)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems, {
  confirmText: `Are you sure to Delete selected records?`,
})
const columnTemp  = computed(() => {
  return [
    { key: 'job_code', label: t('shared.labels.job_code') },
    { key: 'work_order', label: t('shared.labels.demand_letter') },
    { key: 'name', label: t('shared.labels.job_name') },
    { key: 'client_name', label: t('shared.labels.client_name') },
    { key: 'price', label: t('shared.labels.price_taka') },
    { key: 'client_commission_per_candidate', label: 'Client Commission (BDT)' },
    { key: 'salary', label: t('shared.labels.salary') },
    { key: 'status', label: t('shared.labels.status') },
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
const work_orders = computed(() => jobsData.value?.data?.data?.work_orders ?? [])
const principals = computed(() => jobsData.value?.data?.data?.principals ?? [])

const truncateName = (name, maxLength = 20) => {
  const value = name ?? 'N/A'
  if (value.length <= maxLength) return value
  return `${value.slice(0, maxLength - 3)}...`
}

const workOrderOptions = computed(() => {
  const list = jobsData.value?.data?.data?.filter_work_orders ?? []

  return list.map((workOrder) => ({
    ...workOrder,
    name: `${workOrder.work_order_id} (${truncateName(workOrder.client_name)} (${workOrder.job_lists_count ?? 0}))`,
    search_name: `${workOrder.work_order_id} ${workOrder.client_name}`,
  }))
})

const clientOptions = computed(() => {
  const list = jobsData.value?.data?.data?.filter_clients ?? []

  return list.map((client) => ({
    ...client,
    name: `${truncateName(client.name)} (${client.job_lists_count ?? 0})`,
    search_name: client.name,
  }))
})

const principalOptions = computed(() => {
  const list = jobsData.value?.data?.data?.filter_principals ?? []

  return list.map((principal) => ({
    ...principal,
    name: `${principal.name ?? 'N/A'} (${principal.job_lists_count ?? 0})`,
    search_name: principal.name,
  }))
})

const filterBySearchName = (option, query) => {
  return (option.search_name ?? option.name ?? '').toLowerCase().includes(query)
}

const filterWorkOrder = (option, query) => {
  return (option.search_name ?? '').toLowerCase().includes(query)
}
</script>
