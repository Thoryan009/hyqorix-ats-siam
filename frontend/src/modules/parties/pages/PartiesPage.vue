<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ t('parties.management') }}</PageTitle>
      </div>
      <div class="flex flex-wrap gap-2">
        <BaseButton @click="store.handleToggleModal('add')">
          {{ t('shared.actions.add') }} {{ t('parties.module') }}
        </BaseButton>
        <BaseButton
          className="border border-indigo-300 bg-white text-indigo-700 hover:bg-indigo-50"
          @click="store.handleOpenBulkModal()"
        >
          {{ t('parties.add_multiple') }}
        </BaseButton>
      </div>
    </PageHeader>

    <div class="mb-4 flex flex-col items-start justify-between gap-4 underline sm:flex-row sm:items-center">
      <div>
        <BaseButton
          v-if="selectedIds.length"
          class="bg-red-600 text-white hover:bg-red-700"
          :disabled="removeItemsLoading"
          @click="bulkDelete"
        >
          <span v-if="removeItemsLoading">{{ t('shared.messages.deleting') }}</span>
          <span v-else>{{ t('shared.messages.delete_selected', { count: selectedIds.length }) }}</span>
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
        <div class="flex flex-col w-full sm:w-auto sm:min-w-[160px]">
          <label class="text-gray-800 text-sm sm:text-[15px] mb-1">{{ t('parties.party_type') }}</label>
          <BaseSelect
            v-model="filters.type"
            :options="partyTypeOptions"
            :placeholder="t('parties.all_types')"
          />
        </div>
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
              icon="fa fa-eye"
              variant="primary"
              title="View"
              @click="onView(row)"
            />
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

        <BasePagination
          v-if="!isLoading"
          :total="total"
          :showing="showing"
          :links="links"
          :per-page="perPage"
          @update:page="setPage"
          @update:perPage="setPerPage"
        />

        <FormModal />
        <BulkAddPartyModal />
        <ViewModal />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { usePartiesQuery } from '../queries/usePartiesQuery'
import { usePartyMutations } from '../queries/usePartyMutations'
import { usePartyStore } from '../store/partyStore'
import { usePagination } from '@/shared/composables/usePagination'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useTranslate } from '@/shared/composables/useTranslate'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { partyTypeOptions, statusOptions } from '../data/partyOptions'

const { t } = useTranslate()

const ViewModal = defineAsyncComponent(() => import('./components/ViewModal.vue'))
const FormModal = defineAsyncComponent(() => import('./components/FormModal.vue'))
const BulkAddPartyModal = defineAsyncComponent(() => import('./components/BulkAddPartyModal.vue'))

const store = usePartyStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  type: '',
  status: '',
})

const pagination = usePagination({ perPage: 25 })
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = usePartiesQuery(page, perPage, filters)
pagination.bindMeta(data)

const { remove, removeItems, removeItemsLoading } = usePartyMutations(store.moduleName)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems, {
  confirmText: t('shared.messages.delete_confirmation'),
})

const { confirmDelete } = useDeleteWithConfirm(remove)

const columnsTemp = computed(() => [
  { key: 'code', label: t('parties.party_id') },
  { key: 'type', label: t('parties.party_type') },
  { key: 'name', label: t('parties.name') },
  { key: 'opening_debit', label: t('parties.opening_debit') },
  { key: 'opening_credit', label: t('parties.opening_credit') },
  { key: 'remarks', label: t('parties.remarks') },
  { key: 'status', label: t('shared.labels.status') },
])

const { columns, onView, onEdit } = useCrudTable(store, columnsTemp)

const rows = computed(() => data.value?.data?.data ?? [])
</script>
