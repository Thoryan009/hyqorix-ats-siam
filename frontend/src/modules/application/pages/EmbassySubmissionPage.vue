<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <!-- Responsive Heading -->
        <PageTitle>{{t('embassy.title')}}</PageTitle>
        <p class="text-gray-600 text-sm my-2">
          {{t('embassy.sub_title')}}
        </p>
      </div>
      <div>
        <router-link :to="{ name: 'Embassy List' }">
          <BaseButton class="bg-slate-800 text-white hover:bg-slate-900">{{t('embassy.embassy_list')}}</BaseButton>
        </router-link>
      </div>
    </PageHeader>

    <template v-if="selectedIds.length">
      <div class="flex flex-col gap-40 sm:flex-row sm:items-center">
        <div>
          <BaseButton
            v-can="'visa_processing.edit'"
            v-if="filters.status !== 'pending' && filters.status !== null"
            :className="'bg-yellow-600 text-white hover:bg-yellow-700 mr-5'"
            @click="handleBulkStatusUpdate()"
            :disabled="bulkStatusUpdateLoading"
            >{{ t('tasheer.pending') }} ({{ selectedIds.length }})</BaseButton
          >

          <BaseButton
            v-can="'visa_processing.edit'"
            v-if="filters.status !== 'done'"
            :className="'bg-green-600 text-white hover:bg-green-700 mr-5'"
            @click="handleBulkStatusUpdate('done')"
            :disabled="bulkStatusUpdateLoading"
            >{{ t('tasheer.done') }} ({{ selectedIds.length }})</BaseButton
          >

          <BaseButton
            v-can="'visa_processing.edit'"
            v-if="filters.status !== 'cancel'"
            :className="'bg-red-500 text-white hover:bg-red-600'"
            @click="handleBulkStatusUpdate('cancel')"
            :disabled="bulkStatusUpdateLoading"
            >{{ t('tasheer.cancel') }} ({{ selectedIds.length }})</BaseButton
          >
        </div>
      </div>
    </template>

    <div class="flex justify-between items-center my-4">
      <!-- FILTERS -->
      <TableFilters :filters="filters" :has-active-filters="hasActiveFilters" @reset="resetFilters">
        <!-- <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">Job</label>
          <BaseSelect
            @change="selectedIds = []"
            v-model="filters.job_id"
            :options="jobs"
            placeholder="Select"
          />
        </div> -->

        <!-- job FILTER -->
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{ t('shared.labels.job') }}</label>
          <BaseSearchSelect
            v-model="filters.job_id"
            :options="jobs"
            :placeholder="t('application.search_by_job_name_or_job_code')"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterByName"
          />
        </div>

        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{ t('shared.labels.agent') }}</label>
          <BaseSearchSelect
            v-model="filters.agent_id"
            :options="agents"
            :placeholder="t('application.search_by_agent_name')"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterByName"
          />
        </div>
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{ t('shared.labels.client') }}</label>
          <BaseSearchSelect
            v-model="filters.client_id"
            :options="clients"
            :placeholder="t('application.search_by_client_name')"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterByName"
          />
        </div>
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{ t('shared.labels.demand_letter') }}</label>
          <BaseSearchSelect
            v-model="filters.work_order_id"
            :options="workOrders"
            :placeholder="t('application.search_by_d_letter')"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterByName"
          />
        </div>

        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{ t('embassy.visa_status') }}</label>
          <BaseSelect
            @change="selectedIds = []"
            v-model="filters.status"
            :options="statuses"
             :placeholder="t('shared.placeholders.select')"
          />
        </div>

        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{ t('embassy.has_visa_info') }}</label>
          <BaseSelect
            @change="selectedIds = []"
            v-model="filters.has_embassy_submission"
            :options="hasEmbassySubmission"
            :placeholder="t('shared.placeholders.select')"
          />
        </div>
      </TableFilters>
    </div>
    <!-- Merged Document Preview Modal -->
    <MergeDocumentModal
      :isVisible="mergeDocumentsLoading || !!mergeDocUrl"
      :mergeDocumentsLoading="mergeDocumentsLoading"
      :fileUrl="mergeDocUrl"
      @close="mergeDocUrl = ''"
    />
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
          <template #cell-worker_image_url="{ row }">
            <div class="cursor-pointer">
              <img
                v-if="row.worker_image_url"
                :src="row.worker_image_url"
                class="w-9 h-9 rounded-full object-cover border border-gray-200"
                alt="worker"
              />
              <div v-else class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center">
                <i class="fa fa-user text-gray-400 text-sm"></i>
              </div>
            </div>
          </template>

          <template #cell-has_embassy_submission="{ row }">
            <div class="flex items-center justify-center">
              <i
                v-if="
                  row.has_embassy_submission === 'yes' ||
                  row.has_embassy_submission === 1 ||
                  row.has_embassy_submission === true
                "
                class="fa fa-check text-green-500"
                aria-hidden="true"
              ></i>
              <i v-else class="fa fa-times text-red-500" aria-hidden="true"></i>
            </div>
          </template>

          <template #actions="{ row }">
            <router-link :to="{ name: 'Embassy Report', params: { applicationId: row.id } }">
              <BaseTableButton
                v-can="'visa_processing.view_report'"
                icon="fa fa-file-text"
                variant="primary"
                title="mofa"
              />
            </router-link>
            <BaseTableButton
              v-can="'visa_processing.view_details'"
              icon="fa fa-eye"
              variant="primary"
              title="View"
              @click="onView(row)"
            />
            <BaseTableButton
              v-can="'visa_processing.edit'"
              icon="fa fa-pencil"
              variant="success"
              title="Edit"
              @click="onEdit(row)"
            />

            <BaseTableButton
              v-if="row.has_embassy_submission === 'yes'"
              v-can="'visa_processing.delete'"
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

        <EditModal v-can="'visa_processing.edit'" />
        <ViewModal v-can="'visa_processing.view_details'" />
        <DeleteModal v-can="'visa_processing.delete'" />

        <BulkUploadApplicationModal
          :jobs="jobs"
          :agents="agents"
          v-can="'application.bulk_upload'"
        />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'
import { useApplicationStore } from '../store/applicationStore'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import {
  useEmbassySubmissionDataQuery,
  useEmbassySubmissionsQuery,
} from '../queries/useApplicationsQuery'
import { useApplicationMutations } from '../queries/useApplicationMutations'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { usePagination } from '@/shared/composables/usePagination'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import BulkUploadApplicationModal from './components/BulkUploadApplicationModal.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useAuthStore } from '@/modules/auth/store/authStore'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { useTranslate } from '@/shared/composables/useTranslate'
import getTableColumns from '../data/applicationTableData'

const ViewModal = defineAsyncComponent(() => import('./components/ViewEmbassyModal.vue'))
const EditModal = defineAsyncComponent(() => import('./components/EditEmbassyModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./components/DeleteModal.vue'))
import MergeDocumentModal from './components/MergeDocumentModal.vue'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'

const store = useApplicationStore()
const authStore = useAuthStore()
const { t } = useTranslate()
const { selectedIds, toggleAll, toggleRow } = useBulkDelete()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  job_id: null,
  work_order_id: null,
  process_id: null,
  client_id: null,
  agent_id: null,
  status: null,
  has_embassy_submission: null,
  from_date: null,
  to_date: null,
})

const statuses = [
  { label: 'Pending', value: 'pending' },
  { label: 'Done', value: 'done' },
  { label: 'Cancelled', value: 'cancel' },
]

const hasEmbassySubmission = [
  { label: 'Yes', value: 'yes' },
  { label: 'No', value: 'no' },
]

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = useEmbassySubmissionsQuery(page, perPage, filters)
const { data: embassyData } = useEmbassySubmissionDataQuery()

pagination.bindMeta(data)

const { bulkStatusUpdateEmbassySubmissionMutation, deleteEmbassySubmissionMutation } =
  useApplicationMutations(store.moduleName)

const { confirmDelete } = useDeleteWithConfirm(deleteEmbassySubmissionMutation)
const handleBulkStatusUpdate = async (status = null) => {
  if (!selectedIds.value.length) return

  try {
    await bulkStatusUpdateEmbassySubmissionMutation.mutateAsync({ ids: selectedIds.value, status })
    selectedIds.value = []
  } catch (err) {
    console.error('Bulk status update failed:', err)
  }
}
const tableData = computed(() => getTableColumns(t, authStore.user?.permissions ?? []))

const hasEmbassy = {
  key: 'has_embassy_submission',
  label: 'Visa Info',
}

const visa_status = {
  key: 'visa_status',
  label: 'Visa Status',
}

tableData.value.push(hasEmbassy, visa_status) // add before process column

const { columns, onView, onEdit } = useCrudTable(store, tableData, {
  timestamps: false,
  trackUser: false,
})

const rows = computed(() => data.value?.data?.data ?? [])
const clients = computed(() => embassyData.value?.data?.data?.clients ?? [])
const agents = computed(() => embassyData.value?.data?.data?.agents ?? [])
const jobs = computed(() => embassyData.value?.data?.data?.job_lists ?? [])
const workOrders = computed(() => embassyData.value?.data?.data?.work_orders ?? [])
</script>
