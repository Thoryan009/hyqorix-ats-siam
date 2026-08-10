<template>
  <div>
    <div class="mb-4 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <BaseButton
          v-can="'vendor.delete'"
          v-if="selectedIds.length"
          class="bg-red-600 text-white hover:bg-red-700"
          @click="bulkDelete"
          :disabled="removeItemsLoading"
        >
          <span v-if="removeItemsLoading">{{ t('shared.messages.deleting') }}</span>
          <span v-else>{{ t('shared.messages.delete_selected', { count: selectedIds.length }) }}</span>
        </BaseButton>
      </div>
      <div></div>
      <TableFilters :filters="filters" :has-active-filters="hasActiveFilters" @reset="resetFilters" />
    </div>

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
      <template #cell-status_formatted="{ row }">
        <span
          class="rounded-full px-3 py-1 text-xs font-semibold"
          :class="
            row.status === 'active'
              ? 'bg-green-100 text-green-700'
              : 'bg-red-100 text-red-700'
          "
        >
          {{ row.status_formatted }}
        </span>
      </template>

      <template #actions="{ row }">
        <BaseTableButton
          icon="fa fa-eye"
          variant="primary"
          title="View"
          @click="onView(row)"
          v-can="'vendor.view'"
        />
        <BaseTableButton
          v-can="'vendor.edit'"
          icon="fa fa-pencil"
          variant="success"
          title="Edit"
          @click="onEdit(row)"
        />
        <BaseTableButton
          v-can="'vendor.delete'"
          icon="fa fa-trash"
          variant="danger"
          title="Delete"
          @click="confirmDelete(row.id)"
        />
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

    <AddModal v-can="'vendor.create'" />
    <EditModal v-can="'vendor.edit'" />
    <ViewModal v-can="'vendor.view'" />
    <DeleteModal v-can="'vendor.delete'" />
  </div>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { usePagination } from '@/shared/composables/usePagination'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useTranslate } from '@/shared/composables/useTranslate'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import { useVendorTypeMutations } from '@/modules/vendor/queries/useVendorTypeMutations'
import { useVendorTypesQuery } from '@/modules/vendor/queries/useVendorTypesQuery'
import { useVendorTypeStore } from '@/modules/vendor/store/vendorTypeStore'

const { t } = useTranslate('vendor')

const AddModal = defineAsyncComponent(() => import('./AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./EditModal.vue'))
const ViewModal = defineAsyncComponent(() => import('./ViewModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./DeleteModal.vue'))

const store = useVendorTypeStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = useVendorTypesQuery(page, perPage, filters)
pagination.bindMeta(data)

const { remove, removeItems, removeItemsLoading } = useVendorTypeMutations(store.moduleName)
const { confirmDelete } = useDeleteWithConfirm(remove)
const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems)

const rows = computed(() => data.value?.data?.data ?? [])

const columnTemp = computed(() => [
  { key: 'name', label: t('vendor.vendor_type') },
  { key: 'code', label: 'Code' },
  { key: 'status_formatted', label: t('shared.labels.status') },
])

const { columns, onView, onEdit } = useCrudTable(store, columnTemp, {
  timestamps: false,
  trackUser: false,
})
</script>
