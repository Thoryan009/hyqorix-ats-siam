<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <!-- Responsive Heading -->
        <PageTitle>{{t('application.rejected_list')}}</PageTitle>
      </div>
    </PageHeader>
    <!-- Bulk Delete & All Kinds Of Filters  -->
    <div class="flex justify-between items-center my-4">
      <div></div>

      <!-- FILTERS -->
      <TableFilters :filters="filters" :has-active-filters="hasActiveFilters" @reset="resetFilters">
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{t('shared.labels.job')}}</label>
          <BaseSearchSelect
            v-model="filters.job_id"
            :options="jobs"
            :placeholder="t('application.search_by_job_name_or_job_code')"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterJobList"
            @update:model-value="selectedIds = []"
          />
        </div>
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{t('shared.labels.client')}}</label>
          <BaseSearchSelect
            v-model="filters.client_id"
            :options="clients"
            :placeholder="t('application.search_by_client_name')"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterClientList"
            @update:model-value="selectedIds = []"
          />
        </div>
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{t('shared.labels.agent')}}</label>
          <BaseSearchSelect
            v-model="filters.agent_id"
            :options="agents"
            :placeholder="t('application.search_by_agent_name')"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterAgentList"
            @update:model-value="selectedIds = []"
          />
        </div>
      </TableFilters>
    </div>

    <!-- Content Card -->
    <div class="rounded-lg bg-white shadow-sm">
      <div>
        <BaseTable
          v-if="!isLoading"
          :columns="columns"
          :rows="rows"
          :show-serial="false"
          :current-page="page"
          :per-page="perPage"
          show-actions
          selectable
          :selected-ids="selectedIds"
          @toggleAll="(checked) => toggleAll(rows, checked)"
          @toggleRow="toggleRow"
        >
          <template #cell-worker_image_url="{ row }">
            <img
              v-if="row.worker_image_url"
              :src="row.worker_image_url"
              class="w-9 h-9 rounded-full object-cover border border-gray-200"
              alt="worker"
            />
            <div v-else class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center">
              <i class="fa fa-user text-gray-400 text-sm"></i>
            </div>
          </template>
          <template #actions="{ row }">
            <BaseTableButton :show-serial="false" icon="fa fa-eye" variant="primary" title="View" @click="onView(row)" />
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

        <ViewModal />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { useApplicationStore } from '../store/applicationStore'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import { useRejectedListDataQuery, useApplicationsQuery } from '../queries/useApplicationsQuery'
import { useApplicationMutations } from '../queries/useApplicationMutations'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { usePagination } from '@/shared/composables/usePagination'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useAuthStore } from '@/modules/auth/store/authStore'
import { useTranslate } from '@/shared/composables/useTranslate'
import getTableColumns from '../data/applicationTableData'

const ViewModal = defineAsyncComponent(() => import('./components/ViewModal.vue'))

const store = useApplicationStore()
const authStore = useAuthStore()
const { t } = useTranslate()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  job_id: null,
  work_order_id: null,
  client_id: null,
  agent_id: null,
  application_status: 'rejected_list',
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

pagination.setPerPage(100)

const { data, isLoading } = useApplicationsQuery(page, perPage, filters)
const { data: applicationData } = useRejectedListDataQuery()

pagination.bindMeta(data)

const { removeItems } = useApplicationMutations(store.moduleName)

const { selectedIds, toggleAll, toggleRow } = useBulkDelete(removeItems)

const tableData = computed(() => getTableColumns(t, authStore.userType))

const { columns, onView } = useCrudTable(store, tableData, {
  timestamps: false,
  trackUser: false,
})

const rows = computed(() => data.value?.data?.data ?? [])
const jobs = computed(() => applicationData?.value?.data?.data?.jobList ?? [])
const clients = computed(() => applicationData?.value?.data?.data?.clients ?? [])
const agents = computed(() => applicationData?.value?.data?.data?.agents ?? [])

const filterJobList = (option, query) => {
  const jobName = (option.job_name ?? '').toLowerCase()
  const jobCode = (option.job_code ?? '').toLowerCase()
  const displayName = (option.name ?? '').toLowerCase()

  return jobName.includes(query) || jobCode.includes(query) || displayName.includes(query)
}
</script>
