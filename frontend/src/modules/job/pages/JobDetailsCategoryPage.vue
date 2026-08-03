<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <!-- Responsive Heading -->
        <PageTitle>{{t('job_price.fee_category_management')}}</PageTitle>
        <!-- <OrderQuickLinks /> -->
      </div>
      <BaseButton @click="store.handleToggleModal('add')">{{t('job_price.add_fee_category')}}</BaseButton>
    </PageHeader>

    <!-- Bulk Delete & All Kinds of Filters -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 my-4">
      <!-- BULK DELETE -->
      <div class="order-2 sm:order-1">
        <BaseButton
          v-if="selectedIds.length"
          class="bg-red-600 text-white hover:bg-red-700 w-full sm:w-auto"
          @click="bulkDelete"
          :disabled="removeItemsLoading"
          >Delete Selected ({{ selectedIds.length }})</BaseButton
        >
      </div>
      <!-- FILTERS -->
      <div class="order-1 sm:order-2 w-full sm:w-auto flex justify-start">
        <TableFilters
          :filters="filters"
          :has-active-filters="hasActiveFilters"
          @reset="resetFilters"
        />
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
            <BaseTableButton icon="fa fa-eye" variant="primary" title="View" @click="onView(row)" />
            <BaseTableButton
              icon="fa fa-pencil"
              variant="success"
              title="Edit"
              @click="onEdit(row)"
            />
            <BaseTableButton
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

        <AddModal />
        <EditModal />
        <ViewModal />
        <DeleteModal />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { useJobDetailsCategoryStore } from '../store/jobDetailsCategoryStore'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { usePagination } from '@/shared/composables/usePagination'
import { useJobDetailsCategoriesQuery } from '../queries/useJobDetailsCategoriesQuery'
import { useJobDetailsCategoryMutations } from '../queries/useJobDetailsCategoryMutations'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('job')
const ViewModal = defineAsyncComponent(() => import('./categoryParts/ViewModal.vue'))
const AddModal = defineAsyncComponent(() => import('./categoryParts/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./categoryParts/EditModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./categoryParts/DeleteModal.vue'))

const store = useJobDetailsCategoryStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

// Pass pagination refs into query
const { data, isLoading } = useJobDetailsCategoriesQuery(page, perPage, filters)

pagination.bindMeta(data)

const {remove, removeItems, removeItemsLoading } = useJobDetailsCategoryMutations(store.moduleName)

const { confirmDelete } = useDeleteWithConfirm(remove)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems, {
  confirmText: `Are you sure to Delete selected records?`,
})

const columnTemp = computed(() => {
  return [
    { key: 'name', label: t('job_price.category') },
  ]
})
const { columns, onView, onEdit } = useCrudTable(store, columnTemp)

const rows = computed(() => data.value?.data?.data ?? [])
</script>
