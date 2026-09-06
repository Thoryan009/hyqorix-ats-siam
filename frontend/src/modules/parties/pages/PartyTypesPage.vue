<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ t('party_types.management') }}</PageTitle>
      </div>
      <BaseButton v-can="'party_type.create'" @click="store.handleToggleModal('add')">
        {{ t('shared.actions.add') }} {{ t('party_types.module') }}
      </BaseButton>
    </PageHeader>

    <div class="mb-4 flex flex-col items-start justify-between gap-4 underline sm:flex-row sm:items-center">
      <div>
        <BaseButton
          v-if="selectedIds.length"
          class="bg-red-600 text-white hover:bg-red-700"
          :disabled="removeItemsLoading"
          @click="bulkDelete"
        >
          <span v-can="'party_type.delete'" v-if="removeItemsLoading">{{ t('shared.messages.deleting') }}</span>
          <span v-can="'party_type.delete'" v-else>{{ t('shared.messages.delete_selected', { count: selectedIds.length }) }}</span>
        </BaseButton>
      </div>
      <div></div>
      <TableFilters
        :filters="filters"
        :has-active-filters="hasActiveFilters"
        :from-date="false"
        :to-date="false"
        @reset="resetFilters"
      >
        <div class="flex flex-col w-full sm:w-auto sm:min-w-[140px]">
          <label class="text-gray-800 text-sm sm:text-[15px] mb-1">{{ t('shared.labels.status') }}</label>
          <BaseSelect
            v-model="filters.status"
            :options="statusOptions"
            :placeholder="t('parties.all_statuses')"
          />
        </div>
      </TableFilters>
    </div>

    <div class="rounded-lg bg-white shadow-sm">
      <div>
        <BaseTableSkeleton v-if="isLoading" :columns="columns" selectable show-actions :show-serial="false" />

        <BaseTable
          v-else
          :columns="columns"
          :rows="rows"
          :current-page="page"
          :per-page="perPage"
          show-actions
          selectable
          :show-serial="false"
          :selected-ids="selectedIds"
          @toggleAll="(checked) => toggleAll(rows, checked)"
          @toggleRow="toggleRow"
        >
          <template #actions="{ row }">
            <BaseTableButton
              v-can="'party_type.view'"
              icon="fa fa-eye"
              variant="primary"
              title="View"
              @click="onView(row)"
            />
            <BaseTableButton
              v-can="'party_type.edit'"
              icon="fa fa-pencil"
              variant="success"
              title="Edit"
              @click="onEdit(row)"
            />
            <BaseTableButton
              v-can="'party_type.delete'"
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
        
        <PartyTypeFormModal v-can="'party_type.create'"/>
        <PartyTypeViewModal v-can="'party_type.view'"/>
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { usePartyTypesQuery } from '../queries/usePartyTypesQuery'
import { usePartyTypeMutations } from '../queries/usePartyTypeMutations'
import { usePartyTypeStore } from '../store/partyTypeStore'
import { usePagination } from '@/shared/composables/usePagination'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useTranslate } from '@/shared/composables/useTranslate'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { statusOptions } from '../data/partyOptions'

const { t } = useTranslate()

const PartyTypeFormModal = defineAsyncComponent(() => import('./components/PartyTypeFormModal.vue'))
const PartyTypeViewModal = defineAsyncComponent(() => import('./components/PartyTypeViewModal.vue'))

const store = usePartyTypeStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  status: '',
})

const pagination = usePagination({ perPage: 25 })
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = usePartyTypesQuery(page, perPage, filters)
pagination.bindMeta(data)

const { remove, removeItems, removeItemsLoading } = usePartyTypeMutations(store.moduleName)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems, {
  confirmText: t('shared.messages.delete_confirmation'),
})

const { confirmDelete } = useDeleteWithConfirm(remove)

const columnsTemp = computed(() => [
  { key: 'code', label: t('party_types.code') },
  { key: 'name', label: t('party_types.name') },
  { key: 'sort_order', label: t('party_types.sort_order') },
  { key: 'apply_job_filter_label', label: t('party_types.apply_job_filter') },
  { key: 'status', label: t('shared.labels.status') },
])

const { columns, onView, onEdit } = useCrudTable(store, columnsTemp)

const rows = computed(() => data.value?.data?.data ?? [])
</script>
