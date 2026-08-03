<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <!-- Responsive Heading -->
        <PageTitle> {{ t('client.management') }}</PageTitle>
      </div>
      <BaseButton
        v-can="'client.create'"
        @click="store.handleToggleModal('add')"
      >{{ t('client.add') }}</BaseButton>
    </PageHeader>

    <!-- Bulk Delete & All Kinds Of Filters  -->
    <div class="flex justify-start md:justify-between items-center my-4">
      <!-- BULK DELETE -->
      <div>
        <BaseButton
          v-can="'client.delete'"
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
      <!-- FILTERS -->
      <TableFilters :filters="filters" :has-active-filters="hasActiveFilters" @reset="resetFilters">
        <!-- MODULE-SPECIFIC FILTER -->
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{ t('shared.labels.country') }}</label>
          <BaseSearchSelect
            v-model="filters.country_id"
            :options="countryOptions"
            :placeholder="t('shared.placeholders.country_search')"
            option-label="name"
            option-value="id"
            :filter-fn="filterCountryByName"
          />
        </div>
      </TableFilters>
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
          <template #cell-client_image_url="{ row }">
            <img
              v-if="row.client_image_url"
              :src="row.client_image_url"
              class="w-9 h-9 rounded-full object-cover border border-gray-200"
              alt="client image"
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
              v-can="'client.view'"
            />
            <BaseTableButton
              v-can="'client.edit'"
              icon="fa fa-pencil"
              variant="success"
              title="Edit"
              @click="onEdit(row)"
            />
            <BaseTableButton
              v-can="'client.delete'"
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

        <AddModal v-can="'client.create'" :clientData="clientData" />
        <EditModal v-can="'client.edit'" :clientData="clientData" />
        <ViewModal v-can="'client.view'" />
        <DeleteModal v-can="'client.delete'" />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { useClientDataQuery, useClientsQuery } from '../queries/useClientsQuery'

import { useClientStore } from '../store/clientStore'
import { usePagination } from '@/shared/composables/usePagination'
import { useClientMutations } from '../queries/useClientMutations'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('client')

const ViewModal = defineAsyncComponent(() => import('./components/ViewModal.vue'))
const AddModal = defineAsyncComponent(() => import('./components/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./components/EditModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./components/DeleteModal.vue'))

const store = useClientStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  country_id: null,
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = useClientsQuery(page, perPage, filters)
const { data: clientData } = useClientDataQuery()

pagination.bindMeta(data)

const { remove, removeItems, removeItemsLoading } = useClientMutations(store.moduleName)
const { confirmDelete } = useDeleteWithConfirm(remove)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems)

const rows = computed(() => data.value?.data?.data ?? [])

const countryOptions = computed(() => {
  const countries = clientData.value?.data?.data?.filter_countries ?? []

  return countries.map((country) => ({
    ...country,
    name: `${country.name} (${country.clients_count})`,
    search_name: country.name,
  }))
})

const filterCountryByName = (option, query) => {
  return (option.search_name ?? option.name ?? '').toLowerCase().includes(query)
}
const columnTemp = computed(() => [
  { key: 'client_image_url', label: t('shared.labels.image') },
  { key: 'name', label: t('shared.labels.client') },
  { key: 'email', label: t('shared.labels.email') },
  { key: 'role', label: t('shared.labels.role') },
  { key: 'phone', label: t('shared.labels.phone') },
  { key: 'whatsapp_no', label: t('shared.labels.whatsapp_no') },
  { key: 'client_id', label: t('shared.labels.client_id') },
  { key: 'country', label: t('shared.labels.country') },
  { key: 'status_formatted', label: t('shared.labels.status') },
])
const { columns, onView, onEdit } = useCrudTable(
  store,
 columnTemp,
  { timestamps: false, trackUser: false }
)
</script>
