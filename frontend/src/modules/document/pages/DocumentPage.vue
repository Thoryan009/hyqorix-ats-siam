<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ t('documents.management') }}</PageTitle>
        <p class="mt-1 text-sm text-gray-500">{{ t('documents.subtitle') }}</p>
      </div>
      <BaseButton v-can="'document.create'" class="bg-primary text-white hover:opacity-90" @click="store.handleToggleModal('add')">
        <i class="fa fa-plus mr-1"></i> {{ t('documents.add') }}
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
          <span v-if="removeItemsLoading">{{ t('shared.messages.deleting') }}</span>
          <span v-else>{{ t('shared.messages.delete_selected', { count: selectedIds.length }) }}</span>
        </BaseButton>
      </div>

      <TableFilters :filters="filters" :has-active-filters="hasActiveFilters" @reset="resetFilters">
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{ t('documents.category') }}</label>
          <select
            v-model="filters.category"
            class="rounded-md border border-gray-300 px-3 py-1.5 text-sm"
          >
            <option value="">{{ t('documents.all_categories') }}</option>
            <option v-for="category in documentCategories" :key="category" :value="category">
              {{ categoryLabel(category) }}
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
            {{ categoryLabel(row.category) }}
          </span>
        </template>

        <template #actions="{ row }">
          <BaseTableButton
            v-can="'document.view'"
            icon="fa fa-eye"
            variant="info"
            :title="t('shared.actions.view')"
            @click="onView(row)"
          />
          <BaseTableButton
            v-can="'document.download'"
            icon="fa fa-download"
            variant="primary"
            :title="t('shared.actions.download')"
            @click="handleDownload(row)"
          />
          <BaseTableButton
            v-can="'document.edit'"
            icon="fa fa-pencil"
            variant="success"
            :title="t('shared.actions.edit')"
            @click="onEdit(row)"
          />
          <BaseTableButton
            v-can="'document.delete'"
            icon="fa fa-trash"
            variant="danger"
            :title="t('shared.actions.delete')"
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
import { useTranslate } from '@/shared/composables/useTranslate'
import { documentCategories } from '../data/documentCategories'
import { downloadDocument } from '../services/documentService'
import { toast } from '@/shared/config/toastConfig'

const AddModal = defineAsyncComponent(() => import('./components/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./components/EditModal.vue'))
const ViewModal = defineAsyncComponent(() => import('./components/ViewModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./components/DeleteModal.vue'))

const { t } = useTranslate()
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
  confirmText: t('documents.bulk_delete_confirm'),
})
const { confirmDelete } = useDeleteWithConfirm(remove)

const { columns, onView, onEdit } = useCrudTable(store, [
  { key: 'document_no', label: t('documents.document_no') },
  { key: 'name', label: t('shared.labels.name') },
  { key: 'category', label: t('documents.category') },
  { key: 'file_name', label: t('documents.file') },
  { key: 'created_by', label: t('documents.uploaded_by') },
  { key: 'created_at', label: t('shared.labels.created_at') },
])

const rows = computed(() => data.value?.data?.data ?? [])

function categoryLabel(category) {
  if (!category) return '—'
  const key = `documents.categories.${category}`
  const translated = t(key)
  return translated === key ? category : translated
}

async function handleDownload(row) {
  try {
    await downloadDocument(row.id, row.file_name || row.name)
  } catch (error) {
    console.error(error)
    toast.error(t('documents.download_failed'))
  }
}
</script>
