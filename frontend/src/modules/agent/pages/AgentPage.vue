<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <!-- Responsive Heading -->
        <PageTitle>{{ t('agent.management') }}</PageTitle>
      </div>
      <BaseButton v-can="'agent.create'" @click="store.handleToggleModal('add')"
        >{{ t('agent.add') }}</BaseButton
      >
    </PageHeader>
  <router-link to="/agent-performance" class="no-underline">
        <div class="px-4 py-2 rounded-xl border cursor-pointer transition-all bg-white text-gray-700 w-fit shadow-sm hover:shadow-md hover:-translate-y-0.5">
          {{t('agent.performance')}}
        </div>
      </router-link>
    <!-- Bulk Delete & All Kinds Of Filters  -->
    <div class="flex justify-start md:justify-between items-center my-4">
      <!-- BULK DELETE -->
      <div>
        <BaseButton
          v-can="'agent.delete'"
          v-if="selectedIds.length"
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
      <TableFilters :filters="filters" :has-active-filters="hasActiveFilters" @reset="resetFilters">
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{t('shared.labels.status')}}</label>
          <BaseSelect v-model="filters.status" :options="statusOptions" placeholder="Select" />
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

         <template #cell-agent_image_url="{ row }">
            <img
              v-if="row.agent_image_url"
              :src="row.agent_image_url"
              class="w-9 h-9 rounded-full object-cover border border-gray-200"
              alt="agent image"
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
              v-can="'agent.view'"
            />
            <BaseTableButton
              v-can="'agent.edit'"
              icon="fa fa-pencil"
              variant="success"
              title="Edit"
              @click="onEdit(row)"
            />
            <BaseTableButton
              v-can="'agent.delete'"
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

        <AddModal :roles="roles" v-can="'agent.create'" />
        <EditModal v-can="'agent.edit'" />
        <ViewModal :roles="roles" v-can="'agent.view'" />
        <DeleteModal v-can="'agent.delete'" />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { useAgentsQuery, useAgentDataQuery } from '../queries/useAgentsQuery'
import { useAgentStore } from '../store/agentStore'
import { usePagination } from '@/shared/composables/usePagination'
import { useAgentMutations } from '../queries/useAgentMutations'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const ViewModal = defineAsyncComponent(() => import('./components/ViewModal.vue'))
const AddModal = defineAsyncComponent(() => import('./components/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./components/EditModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./components/DeleteModal.vue'))

const store = useAgentStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  status: null,
  from_date: null,
  to_date: null,
})

const statusOptions = [
  { id: '1', name: 'Active' },
  { id: '0', name: 'Inactive' },
]

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = useAgentsQuery(page, perPage, filters)
const { data: agentData } = useAgentDataQuery()

const roles = computed(() => agentData.value?.data?.roles ?? [])

pagination.bindMeta(data)

const { remove, removeItems, removeItemsLoading } = useAgentMutations(store.moduleName)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems)

const { confirmDelete } = useDeleteWithConfirm(remove)

const columnTemp = computed(() => {
  return [
    { key: 'agent_image_url', label: t('shared.labels.image') },
    { key: 'name', label: t('shared.labels.name') },
    { key: 'email', label: t('shared.labels.email') },
    { key: 'phone', label: t('shared.labels.phone') },
    { key: 'whatsapp_no', label: t('shared.labels.whatsapp') },
    { key: 'manager_name', label: t('shared.labels.manager_name') },
    { key: 'phone2', label: t('shared.labels.phone2') },
    { key: 'stuff_name', label: t('shared.labels.staff_name') },
    { key: 'stuff_phone', label: t('shared.labels.staff_phone') },
    { key: 'role', label: t('shared.labels.role') },
    { key: 'agent_id', label: t('shared.labels.agent_id') },
    { key: 'nid_no', label: t('shared.labels.nid_no') },
    { key: 'address', label: t('shared.labels.address') },
    { key: 'status_formatted', label: t('shared.labels.status') },
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
