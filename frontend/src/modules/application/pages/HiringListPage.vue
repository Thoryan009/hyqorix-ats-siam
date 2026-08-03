<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <PageTitle>{{ t('application.hiring_list') }}</PageTitle>
      </div>
    </PageHeader>

    <div
      v-if="canShowOfferExtend && selectedIds.length > 0"
      v-can="'application.offer_extend'"
      class="mb-4 flex flex-wrap items-center justify-start gap-2"
    >
      <BaseButton
        v-if="selectedIds.length === 1"
        class="bg-green-600 text-white hover:bg-green-700"
        @click="handleOfferExtend"
      >
        Offer Extend
      </BaseButton>

      <BaseButton
        v-else
        class="bg-green-600 text-white hover:bg-green-700"
        @click="handleOfferExtend"
      >
        Bulk Offer Extend ({{ selectedIds.length }})
      </BaseButton>
    </div>

    <div class="mb-4">
      <TableFilters :filters="filters" :has-active-filters="hasActiveFilters" @reset="resetFilters">
        <div class="flex flex-col sm:min-w-40">
          <label class="mb-1 text-[15px] text-gray-800">{{ t('shared.labels.job') }}</label>
          <BaseSelect
            v-model="filters.job_id"
            :options="jobs"
            :placeholder="t('shared.placeholders.select')"
            @change="selectedIds = []"
          />
        </div>
        <div class="flex flex-col sm:min-w-48">
          <label class="mb-1 text-[15px] text-gray-800">{{ t('shared.labels.agent') }}</label>
          <BaseSearchSelect
            v-model="filters.agent_id"
            :options="agents"
            :placeholder="t('application.search_by_agent_name')"
            optionLabel="name"
            optionValue="id"
            @update:model-value="selectedIds = []"
          />
        </div>
        <div class="flex flex-col sm:min-w-48">
          <label class="mb-1 text-[15px] text-gray-800">{{ t('shared.labels.client') }}</label>
          <BaseSearchSelect
            v-model="filters.client_id"
            :options="clients"
            :placeholder="t('application.search_by_client_name')"
            optionLabel="name"
            optionValue="id"
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
          :current-page="page"
          :per-page="perPage"
          show-actions
           :show-serial="false"
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
            <BaseTableButton icon="fa fa-eye" variant="primary" :show-serial="false" title="View" @click="onView(row)" />
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
import {
  useApplicationProcessesQuery,
  useApplicationsQuery,
  useHiringListDataQuery,
} from '../queries/useApplicationsQuery'
import { useApplicationMutations } from '../queries/useApplicationMutations'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { usePagination } from '@/shared/composables/usePagination'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useAuthStore } from '@/modules/auth/store/authStore'
import { showConfirmDialog } from '@/shared/utils/sweetAlertUtils'
import { useTranslate } from '@/shared/composables/useTranslate'
import getTableColumns from '../data/applicationTableData'

const ViewModal = defineAsyncComponent(() => import('./components/ViewModal.vue'))

const store = useApplicationStore()
const authStore = useAuthStore()
const { t } = useTranslate()

const { data: processesData } = useApplicationProcessesQuery()

const processData = computed(() => processesData.value?.data?.data ?? [])

const hiringListProcessId = computed(() => {
  const hiringListProcess = processData.value.find((p) => p.name == 'hiring_list')
  return hiringListProcess ? hiringListProcess.id : null
})

const { filters, resetFilters } = useTableFilters({
  searchQuery: '',
  job_id: null,
  work_order_id: null,
  client_id: null,
  agent_id: null,
  application_status: 'hiring_list',
  from_date: null,
  to_date: null,
})

// Ignore permanent list status so Reset only appears for user-applied filters.
const hasActiveFilters = computed(() => {
  const f = filters.value || {}
  const ignoredKeys = new Set(['application_status'])

  return Object.entries(f).some(([key, value]) => {
    if (ignoredKeys.has(key)) return false
    if (value === null || value === undefined) return false
    if (typeof value === 'string') return value.trim() !== ''
    return true
  })
})

const canShowOfferExtend = computed(() => {
  const jobId = filters.value?.job_id
  // BaseSelect emits "" for placeholder — treat only real job selection as active.
  return jobId !== null && jobId !== undefined && String(jobId).trim() !== ''
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

pagination.setPerPage(100)

const { data, isLoading } = useApplicationsQuery(page, perPage, filters)
const { data: applicationData } = useHiringListDataQuery()

pagination.bindMeta(data)

const { removeItems } = useApplicationMutations(store.moduleName)

const { selectedIds, toggleAll, toggleRow } = useBulkDelete(removeItems)

const tableData = computed(() =>
  getTableColumns(t, authStore.userType)
)

const { columns, onView } = useCrudTable(store, tableData, {
  timestamps: false,
  trackUser: false,
})

const { bulkOfferExtend } = useApplicationMutations(store.moduleName, {
  onSuccess() {
    selectedIds.value = []
    resetFilters()
  },
  onError(error) {
    console.log('Error:', error)
  },
})

const handleOfferExtend = async () => {
  if (!selectedIds.value || selectedIds.value.length === 0) return

  const jobId = filters.value.job_id // unwrap ref
  const ids = selectedIds.value.map((id) => id) // unwrap array if reactive
  if (!jobId) {
    alert('Job ID not selected')
    return
  }

  const result = await showConfirmDialog({
    title: 'Confirm Offer Extend',
    text: `Offer extension for ${ids.length} application(s)?`,
    icon: 'question',
    confirmButtonText: 'Yes, extend now',
    cancelButtonText: 'Cancel',
  })

  if (!result.isConfirmed) return

  try {
    await bulkOfferExtend.mutateAsync({
      jobId,
      processId: hiringListProcessId.value,
      ids,
    })
  } catch (err) {
    console.error('Offer extend failed:', err)
    alert('Something went wrong. Check console.')
  }
}

const rows = computed(() => data.value?.data?.data ?? [])
const jobs = computed(() => applicationData.value?.data?.data?.jobList ?? [])
const clients = computed(() => applicationData.value?.data?.data?.clients ?? [])
const agents = computed(() => applicationData.value?.data?.data?.agents ?? [])
</script>
