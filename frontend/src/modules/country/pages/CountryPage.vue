<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <!-- Responsive Heading -->
        <!-- <PageTitle>{{ store.moduleName }} Management</PageTitle> -->
        <PageTitle>{{ t('country.module') }} {{ t('shared.titles.management') }}</PageTitle>
      </div>
      <BaseButton v-can="'country.create'" @click="store.handleToggleModal('add')"
        >{{ t('shared.actions.add') }} {{ t('country.module') }}</BaseButton
      >
    </PageHeader>

    <!-- Bulk Delete & All Kinds of Filters -->

    <div class="mb-4 underline flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">

      <div>
        <BaseButton
          v-if="selectedIds.length"
          v-can="'country.delete'"
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
<!-- <pre>{{ selectedIds }}</pre> -->
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
          <template #cell-country_image_url="{ row }">
            <img
              v-if="row.country_image_url"
              :src="row.country_image_url"
              class="w-9 h-9 rounded object-cover border border-gray-200"
              alt="country image"
            />
            <div v-else class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center">
              <i class="fa fa-user text-gray-400 text-sm"></i>
            </div>
          </template>
          <template #actions="{ row }">
            <BaseTableButton
              icon="fa fa-eye"
              variant="primary"
              title="View"
              @click="onView(row)"
              v-can="'country.view'"
            />

            <BaseTableButton
              v-can="'country.edit'"
              icon="fa fa-pencil"
              variant="success"
              title="Edit"
              @click="onEdit(row)"
            />

            <BaseTableButton
              v-can="'country.delete'"
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

        <AddModal v-can="'country.create'" />
        <EditModal v-can="'country.edit'" />
        <ViewModal v-can="'country.view'" />
        <DeleteModal v-can="'country.delete'" />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { useCountriesQuery } from '../queries/useCountriesQuery'
import { useCountryStore } from '../store/countryStore'
import { usePagination } from '@/shared/composables/usePagination'
import { useCountryMutations } from '../queries/useCountryMutations'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()

const ViewModal = defineAsyncComponent(() => import('./components/ViewModal.vue'))
const AddModal = defineAsyncComponent(() => import('./components/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./components/EditModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./components/DeleteModal.vue'))

const store = useCountryStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = useCountriesQuery(page, perPage, filters)

pagination.bindMeta(data)

const { remove, removeItems, removeItemsLoading } = useCountryMutations(store.moduleName)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems, {
  confirmText: t('shared.messages.delete_confirmation'),
})

const { confirmDelete } = useDeleteWithConfirm(remove)

const columnsTemp = computed(() => [
  { key: 'country_image_url', label: t('country.image') },
  { key: 'name', label: t('shared.labels.name') },
])

const { columns, onView, onEdit } = useCrudTable(store, columnsTemp)

const rows = computed(() => data.value?.data?.data ?? [])
</script>
