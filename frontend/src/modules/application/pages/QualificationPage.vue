<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <!-- Responsive Heading -->
        <PageTitle>{{ t('job.qualification') + ' ' + t('shared.titles.management') }}</PageTitle>
      </div>
      <BaseButton v-can="'qualification.create'" @click="store.handleToggleModal('add')">{{t('shared.actions.add')}} {{ t('job.qualification') }}</BaseButton>
    </PageHeader>

    <!-- Bulk Delete & All Kinds of Filters -->

    <div class="mb-4 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
      <!-- BULK DELETE -->
      <div>
        <BaseButton
          v-if="selectedIds.length"
          v-can="'qualification.delete'"
          class="bg-red-600 text-white hover:bg-red-700"
          @click="bulkDelete"
          :disabled="removeItemsLoading"
        >
          <span v-if="removeItemsLoading">{{t('shared.messages.deleting')}}</span>
          <span v-else>{{t('shared.messages.delete_selected', { count: selectedIds.length })}}</span>
        </BaseButton>
      </div>
      <div></div>
      <!-- FILTERS -->
      <TableFilters
        :filters="filters"
        :has-active-filters="hasActiveFilters"
        @reset="resetFilters"
      />
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
            <BaseTableButton icon="fa fa-eye" variant="primary" title="View" @click="onView(row)" v-can="'qualification.view'" />

            <BaseTableButton
              v-can="'qualification.edit'"
              icon="fa fa-pencil"
              variant="success"
              title="Edit"
              @click="onEdit(row)"
            />

            <BaseTableButton
              v-can="'qualification.delete'"
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

        <AddModal v-can="'qualification.create'" />
        <EditModal v-can="'qualification.edit'" />
        <ViewModal v-can="'qualification.view'" />
        <DeleteModal v-can="'qualification.delete'" />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { useQualificationsQuery } from '../queries/useQualificationsQuery'
import { useQualificationStore } from '../store/qualificationStore'
import { usePagination } from '@/shared/composables/usePagination'
import { useQualificationMutations } from '../queries/useQualificationMutations'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const ViewModal = defineAsyncComponent(() => import('./qualificationParts/ViewModal.vue'))
const AddModal = defineAsyncComponent(() => import('./qualificationParts/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./qualificationParts/EditModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./qualificationParts/DeleteModal.vue'))

const store = useQualificationStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = useQualificationsQuery(page, perPage, filters)

pagination.bindMeta(data)

const {remove, removeItems, removeItemsLoading } = useQualificationMutations(store.moduleName)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems, {
  confirmText: `Are you sure to Delete selected records?`,
})

const { confirmDelete } = useDeleteWithConfirm(remove)

const columnTemp = computed (() => [
  { key: 'name', label: t('job.qualification')},

])
const { columns, onView, onEdit } = useCrudTable(
  store,
  columnTemp,
  { timestamps: false },
)

const rows = computed(() => data.value?.data?.data ?? [])
</script>
