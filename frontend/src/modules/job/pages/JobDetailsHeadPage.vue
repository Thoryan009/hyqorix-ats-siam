<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <!-- Responsive Heading -->
        <PageTitle>{{t('job_price.fee_head_management')}}</PageTitle>
        <!-- <OrderQuickLinks /> -->
      </div>
      <BaseButton @click="store.handleToggleModal('add')">{{t('job_price.add_fee_head')}}</BaseButton>
    </PageHeader>

    <!-- Bulk Delete & All Kinds Of Filters  -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 my-4">
      <!-- BULK DELETE -->
      <div class="order-2 sm:order-1">
        <BaseButton
          v-if="selectedIds.length"
          class="bg-red-600 text-white hover:bg-red-700 w-full sm:w-auto"
          @click="bulkDelete"
          :disabled="removeItemsLoading"
        >
          <span v-if="removeItemsLoading">{{t('shared.messages.deleting')}}</span>
          <span v-else>{{t('shared.messages.delete_selected', { count: selectedIds.length })}}</span>
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
          <div class="flex flex-col">
            <label class="text-gray-800 text-[15px]">{{t('job_price.head_category')}}</label>
            <BaseSelect
              v-model="filters.category_id"
              :options="categories"
              :placeholder="t('shared.placeholders.select')"
            />
          </div>
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
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { usePagination } from '@/shared/composables/usePagination'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useJobDetailsHeadStore } from '../store/jobDetailsHeadStore'
import {
  useJobDetailsHeadCategoriesQuery,
  useJobDetailsHeadsQuery,
} from '../queries/useJobDetailsHeadQuery'
import { useJobDetailsHeadMutations } from '../queries/useJobDetailsHeadMutations'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useTranslate } from '@/shared/composables/useTranslate'
const ViewModal = defineAsyncComponent(() => import('./headParts/ViewModal.vue'))
const AddModal = defineAsyncComponent(() => import('./headParts/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./headParts/EditModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./headParts/DeleteModal.vue'))

const { t } = useTranslate()
const store = useJobDetailsHeadStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  category_id: null,
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = useJobDetailsHeadsQuery(page, perPage, filters)
const { data: categoriesData } = useJobDetailsHeadCategoriesQuery()

pagination.bindMeta(data)

const {remove, removeItems, removeItemsLoading } = useJobDetailsHeadMutations(store.moduleName)

const { confirmDelete } = useDeleteWithConfirm(remove)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems)

const columnTemp = computed(() => [
  { key: 'name', label: t('job_price.price_head_name') },
  { key: 'amount', label: t('job_price.amount') },
  { key: 'amount_usd', label: t('job_price.amount_usd') },
  { key: 'category', label: t('job_price.price_category') },
])
const { columns, onView, onEdit } = useCrudTable(
  store,
  columnTemp,
  {
    timestamps: false,
    trackUser: false,
  },
)

const rows = computed(() => data.value?.data?.data ?? [])
const categories = computed(() => categoriesData.value?.data?.data ?? [])
</script>
