<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div class="flex flex-col">
        <!-- Responsive Heading -->
        <PageTitle>{{ t('job_price.title') }}</PageTitle>
        <!-- <OrderQuickLinks /> -->
      </div>
      <BaseButton v-can="'job_detail.create'" @click="store.handleToggleModal('add')">{{ t('job_price.add') }}</BaseButton>
    </PageHeader>
    <!-- Bulk Delete & All Kinds of Filters -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 my-4">
      <!-- BULK DELETE -->
      <div class="order-2 sm:order-1">
        <BaseButton
          v-can="'job_detail.delete'"
          v-if="selectedIds.length"
          class="bg-red-600 text-white hover:bg-red-700 w-full sm:w-auto"
          @click="bulkDelete"
          :disabled="removeItemsLoading"
        >
          <span v-if="removeItemsLoading">{{ t('shared.messages.deleting') }}</span>
          <span v-else>{{ t('shared.messages.delete_selected', { count: selectedIds.length }) }}</span>
        </BaseButton>
      </div>
      <!-- FILTERS -->
      <div class="order-1 sm:order-2 w-full sm:w-auto flex justify-end">
        <TableFilters
          :filters="filters"
          :has-active-filters="hasActiveFilters"
          @reset="resetFilters"
        >
          <!-- MODULE-SPECIFIC FILTER -->
        </TableFilters>
      </div>
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
            <BaseTableButton icon="fa fa-eye" variant="primary" title="View" @click="onView(row)" v-can="'job_detail.view'" />
            <BaseTableButton
              v-can="'job_detail.edit'"
              icon="fa fa-pencil"
              variant="success"
              title="Edit"
              @click="onEdit(row)"
            />
            <BaseTableButton
              v-can="'job_detail.delete'"
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

        <AddModal :jobId="jobId" v-can="'job_detail.create'" />
        <EditModal :jobId="jobId" v-can="'job_detail.edit'" />
        <ViewModal v-can="'job_detail.view'" />
        <DeleteModal v-can="'job_detail.delete'" />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { usePagination } from '@/shared/composables/usePagination'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import { useRoute } from 'vue-router'
import { useJobDetailsStore } from '../store/jobDetailsStore'
import { useJobDetailsQuery } from '../queries/useJobDetailsQuery'
import { useJobDetailsMutations } from '../queries/useJobDetailsMutations'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('job')
const ViewModal = defineAsyncComponent(() => import('./jobDetailsParts/ViewModal.vue'))
const AddModal = defineAsyncComponent(() => import('./jobDetailsParts/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./jobDetailsParts/EditModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./jobDetailsParts/DeleteModal.vue'))

const store = useJobDetailsStore()

const route = useRoute()

const jobId = route.params.id

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  job_id: jobId,
  searchQuery: '',
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = useJobDetailsQuery(page, perPage, filters)

pagination.bindMeta(data)

const {remove, removeItems, removeItemsLoading } = useJobDetailsMutations(store.moduleName)

const { confirmDelete } = useDeleteWithConfirm(remove)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems, {
  confirmText: `Are you sure to Delete selected records?`,
})


const columnTemp = computed(() => {
  return [
    { key: 'fee_category', label: t('job_price.fee_category') },
    { key: 'fee_name', label: t('job_price.fee_name') },
    { key: 'amount', label: t('job_price.amount') },
    { key: 'amount_usd', label: t('job_price.amount_usd') },
    { key: 'job_name', label: t('shared.labels.job_name') },
  ]
})
const { columns, onView, onEdit } = useCrudTable(store, columnTemp, {
  timestamps: false,
  trackUser: false,
})

const rows = computed(() => data.value?.data?.data ?? [])
</script>
