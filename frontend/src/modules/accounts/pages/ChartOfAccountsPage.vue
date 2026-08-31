<template>
  <SectionHeader>
    <PageHeader>
      <div>
        <PageTitle>{{ t('accounts.management') }}</PageTitle>
      </div>
      <BaseButton @click="store.handleToggleModal('add')">
        {{ t('shared.actions.add') }} {{ t('accounts.module') }}
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
          <label class="text-gray-800 text-sm sm:text-[15px] mb-1">{{ t('accounts.type') }}</label>
          <BaseSelect
            v-model="filters.type"
            :options="accountTypeOptions"
            :placeholder="t('accounts.all_types')"
          />
        </div>
        <div class="flex flex-col w-full sm:w-auto sm:min-w-[180px]">
          <label class="text-gray-800 text-sm sm:text-[15px] mb-1">{{ t('accounts.financial_statement') }}</label>
          <BaseSelect
            v-model="filters.financial_statement"
            :options="financialStatementOptions"
            :placeholder="t('accounts.all_statements')"
          />
        </div>
        <div class="flex flex-col w-full sm:w-auto sm:min-w-[150px]">
          <label class="text-gray-800 text-sm sm:text-[15px] mb-1">{{ t('accounts.normal_balance') }}</label>
          <BaseSelect
            v-model="filters.normal_balance"
            :options="normalBalanceOptions"
            :placeholder="t('accounts.all_balances')"
          />
        </div>
        <div class="flex flex-col w-full sm:w-auto sm:min-w-[140px]">
          <label class="text-gray-800 text-sm sm:text-[15px] mb-1">{{ t('shared.labels.status') }}</label>
          <BaseSelect
            v-model="filters.status"
            :options="statusOptions"
            :placeholder="t('accounts.all_statuses')"
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
        <ViewModal />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { useChartOfAccountsQuery } from '../queries/useChartOfAccountsQuery'
import { useChartOfAccountMutations } from '../queries/useChartOfAccountMutations'
import { useChartOfAccountStore } from '../store/chartOfAccountStore'
import { usePagination } from '@/shared/composables/usePagination'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useTranslate } from '@/shared/composables/useTranslate'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import {
  accountTypeOptions,
  financialStatementOptions,
  normalBalanceOptions,
  statusOptions,
} from '../data/chartOfAccountOptions'

const { t } = useTranslate()

const ViewModal = defineAsyncComponent(() => import('./components/ViewModal.vue'))
const FormModal = defineAsyncComponent(() => import('./components/FormModal.vue'))

const store = useChartOfAccountStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  type: '',
  financial_statement: '',
  normal_balance: '',
  status: '',
})

const pagination = usePagination({ perPage: 25 })
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = useChartOfAccountsQuery(page, perPage, filters)
pagination.bindMeta(data)

const { remove, removeItems, removeItemsLoading } = useChartOfAccountMutations(store.moduleName)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems, {
  confirmText: t('shared.messages.delete_confirmation'),
})

const { confirmDelete } = useDeleteWithConfirm(remove)

const columnsTemp = computed(() => [
  { key: 'code', label: t('accounts.code') },
  { key: 'name', label: t('accounts.account_name') },
  { key: 'type', label: t('accounts.type') },
  { key: 'financial_statement', label: t('accounts.financial_statement') },
  { key: 'normal_balance', label: t('accounts.normal_balance') },
  { key: 'status', label: t('shared.labels.status') },
])

const { columns, onView, onEdit } = useCrudTable(store, columnsTemp)

const rows = computed(() => data.value?.data?.data ?? [])
</script>
