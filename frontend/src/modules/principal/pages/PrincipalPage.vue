<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <!-- Responsive Heading -->
        <PageTitle>{{ t('principal.management') }}</PageTitle>
      </div>
      <BaseButton v-can="'principal.create'" @click="store.handleToggleModal('add')"
        >{{ t('principal.add') }}</BaseButton
      >
    </PageHeader>

    <!-- Bulk Delete & All Kinds Of Filters  -->
    <div class="flex justify-start md:justify-between items-center my-4">
      <!-- <pre>{{ rows }}</pre> -->
      <!-- BULK DELETE -->
      <div>
        <BaseButton
          v-can="'principal.delete'"
          
          v-if="selectedIds.length"
          class="bg-red-600 text-white hover:bg-red-700"
          @click="bulkDelete"
          :disabled="removeItemsLoading"
        >
          <span v-if="removeItemsLoading">{{t('shared.messages.deleting')}}</span>
          <span v-else>{{ t('shared.messages.delete_selected', { count: selectedIds.length }) }}</span>
        </BaseButton>
      </div>
      <div></div>
      <!-- FILTERS -->
      <TableFilters :filters="filters" :has-active-filters="hasActiveFilters" @reset="resetFilters" />
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
            <BaseTableButton
              icon="fa fa-eye"
              variant="primary"
              title="View"
              @click="onView(row)"
              v-can="'principal.view'"
            />
            <BaseTableButton
              v-can="'principal.edit'"
              icon="fa fa-pencil"
              variant="success"
              title="Edit"
              @click="onEdit(row)"
            />
            <BaseTableButton
              v-can="'principal.delete'"
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

        <AddModal :roles="roles" :clientData="clientData" v-can="'principal.create'" />
        <EditModal :clientData="clientData" v-can="'principal.edit'" />
        <ViewModal :roles="roles" v-can="'principal.view'" />
        <DeleteModal v-can="'principal.delete'" />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'

import { usePrincipalStore } from '../store/principalStore'
import { usePagination } from '@/shared/composables/usePagination'

import { usePrincipalsQuery, usePrincipalDataQuery } from '../queries/usePrincipalsQuery'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { usePrincipalMutations } from '../queries/usePrincipalMutations'
import { useClientDataQuery } from '@/modules/client/queries/useClientsQuery'
import {useTranslate} from '@/shared/composables/useTranslate'

const {t} = useTranslate()

const ViewModal = defineAsyncComponent(() => import('./components/ViewModal.vue'))
const AddModal = defineAsyncComponent(() => import('./components/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./components/EditModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./components/DeleteModal.vue'))

const store = usePrincipalStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = usePrincipalsQuery(page, perPage, filters)
const { data: principalData } = usePrincipalDataQuery()
const { data: clientData } = useClientDataQuery()

const roles = computed(() => principalData.value?.data?.roles ?? [])

pagination.bindMeta(data)

const { remove, removeItems, removeItemsLoading } = usePrincipalMutations(store.moduleName)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems)

const { confirmDelete } = useDeleteWithConfirm(remove)


const columnTemp = computed (() => {
  return [
    { key: 'principal_id', label: t('principal.principal_id') },
    { key: 'organization_name', label: t('principal.organization_name') },
    { key: 'designation', label: t('principal.designation') },
    { key: 'email', label: t('shared.labels.email') },
    { key: 'role', label: t('shared.labels.role') },
    { key: 'contact_no', label: t('shared.labels.phone') },
    { key: 'whatsapp_no', label: t('shared.labels.whatsapp_no') },
    { key: 'country', label: t('shared.labels.country') },
    { key: 'contact_person_name', label: t('principal.contact_person_name') },

  ]
})

const { columns, onView, onEdit } = useCrudTable(
  store,
  columnTemp,
  {
    timestamps: false,
    trackUser: false,
  },
)

const rows = computed(() => data.value?.data?.data ?? [])
</script>
