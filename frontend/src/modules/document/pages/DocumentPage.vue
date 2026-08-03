<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ store.moduleName }} Management</PageTitle>
        <p class="mt-1 text-sm text-gray-500">Upload and manage company documents</p>
      </div>
      <BaseButton v-can="'document.create'" class="bg-primary text-white hover:opacity-90" @click="store.handleToggleModal('add')">
        <i class="fa fa-plus mr-1"></i> Add Document
      </BaseButton>
    </PageHeader>

    <div class="mb-4 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <BaseButton
          v-if="selectedIds.length"
          v-can="'document.delete'"
          class="bg-red-600 text-white hover:bg-red-700"
          :disabled="removeItemsLoading"
          @click="bulkDelete"
        >
          <span v-if="removeItemsLoading">Deleting...</span>
          <span v-else>Delete Selected ({{ selectedIds.length }})</span>
        </BaseButton>
      </div>

      <TableFilters :filters="filters" :has-active-filters="hasActiveFilters" @reset="resetFilters">
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">Category</label>
          <select
            v-model="filters.category"
            class="rounded-md border border-gray-300 px-3 py-1.5 text-sm"
          >
            <option value="">All Categories</option>
            <option v-for="category in documentCategories" :key="category" :value="category">
              {{ category }}
            </option>
          </select>
        </div>
      </TableFilters>
    </div>

    <div class="rounded-lg bg-white shadow-sm">
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
        <template #cell-category="{ row }">
          <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">
            {{ row.category }}
          </span>
        </template>

        <template #actions="{ row }">
          <BaseTableButton
            v-can="'document.view'"
            icon="fa fa-eye"
            variant="info"
            title="View"
            @click="onView(row)"
          />
          <BaseTableButton
            v-can="'document.download'"
            icon="fa fa-download"
            variant="primary"
            title="Download"
            @click="handleDownload(row)"
          />
          <BaseTableButton
            v-can="'document.edit'"
            icon="fa fa-pencil"
            variant="success"
            title="Edit"
            @click="onEdit(row)"
          />
          <BaseTableButton
            v-can="'document.delete'"
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
    </div>

    <AddModal v-can="'document.create'" />
    <EditModal v-can="'document.edit'" />
    <ViewModal v-can="'document.view'" />
    <DeleteModal v-can="'document.delete'" />
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import PageTitle from '@/shared/components/ui/PageTitle.vue'
import BaseButton from '@/shared/components/base/BaseButton.vue'
import BaseTable from '@/shared/components/base/BaseTable.vue'
import BaseTableSkeleton from '@/shared/components/base/BaseTableSkeleton.vue'
import BaseTableButton from '@/shared/components/base/BaseTableButton.vue'
import BasePagination from '@/shared/components/base/BasePagination.vue'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import { useDocumentsQuery } from '../queries/useDocumentsQuery'
import { useDocumentStore } from '../store/documentStore'
import { usePagination } from '@/shared/composables/usePagination'
import { useDocumentMutations } from '../queries/useDocumentMutations'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { documentCategories } from '../data/documentCategories'
import { downloadDocument } from '../services/documentService'
import { toast } from '@/shared/config/toastConfig'

const AddModal = defineAsyncComponent(() => import('./components/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./components/EditModal.vue'))
const ViewModal = defineAsyncComponent(() => import('./components/ViewModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./components/DeleteModal.vue'))

const store = useDocumentStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  category: '',
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = useDocumentsQuery(page, perPage, filters)
pagination.bindMeta(data)

const { remove, removeItems, removeItemsLoading } = useDocumentMutations(store.moduleName)
const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems, {
  confirmText: 'Are you sure you want to delete selected documents?',
})
const { confirmDelete } = useDeleteWithConfirm(remove)

const { columns, onView, onEdit } = useCrudTable(store, [
  { key: 'document_no', label: 'Document No' },
  { key: 'name', label: 'Name' },
  { key: 'category', label: 'Category' },
  { key: 'file_name', label: 'File' },
  { key: 'created_by', label: 'Uploaded By' },
  { key: 'created_at', label: 'Created At' },
])

const rows = computed(() => data.value?.data?.data ?? [])

async function handleDownload(row) {
  try {
    await downloadDocument(row.id, row.file_name || row.name)
  } catch (error) {
    console.error(error)
    toast.error('Failed to download document.')
  }
}
</script>
