<template>
  <SectionHeader>
    <!-- Page Header -->
    <PageHeader>
      <div>
        <!-- Responsive Heading -->
        <PageTitle>{{ t('application.management') }}</PageTitle>
      </div>

      <div class="flex gap-2">
        <BaseButton
          v-can="'application.create'"
          @click="store.handleToggleModal('add')"
        >{{ t('application.add') }}</BaseButton>
        <BaseButton
          v-can="'application.bulk_upload'"
          class="bg-yellow-500 text-white hover:bg-yellow-600"
          @click="store.handleToggleModalBulkUpload"
        >{{ t('application.bulk_upload') }}</BaseButton>
      </div>
    </PageHeader>

    <div
      v-if="pageMessage?.message"
      class="rounded-lg p-3 text-base font-medium mb-4 flex justify-between items-center"
      :class="
        pageMessage.type === 'success'
          ? 'bg-green-500/10 border border-green-500/20 text-green-700'
          : 'bg-red-500/10 border border-red-500/20 text-red-500'
      "
    >
      <div class="flex items-center gap-2">
        <i
          :class="
            pageMessage.type === 'success'
              ? 'fa fa-check-circle'
              : 'fa fa-exclamation-triangle'
          "
        ></i>
        <span>{{ pageMessage.message }}</span>
      </div>
      <BaseButton
        class="text-white"
        :class="pageMessage.type === 'success' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-500 hover:bg-red-600'"
        @click="clearPageMessage"
      >
        <i class="fa fa-times"></i>
      </BaseButton>
    </div>

    <!-- Bulk Delete & All Kinds Of Filters  -->
    <div>
      <!-- BULK DELETE -->
      <div class="flex gap-3 items-center">
        <BaseButton
          v-can="'application.delete'"
          v-if="selectedIds.length"
          class="bg-red-600 text-white hover:bg-red-700 mr-2"
          @click="bulkDelete"
          :disabled="removeItemsLoading"
        >
          <span v-if="removeItemsLoading">{{ t('shared.messages.deleting') }}</span>
          <span v-else>{{ t('shared.messages.delete_selected', { count: selectedIds.length }) }}</span>
        </BaseButton>

        <template v-if="selectedIds.length">
          <BaseButton
            v-can="'application.bulk_status_update'"
            :className="'bg-green-600 text-white hover:bg-green-700'"
            @click="handleBulkStatusUpdate('hiring_list')"
            :disabled="bulkStatusUpdateLoading"
          >{{ t('application.hiring_list') }} ({{ selectedIds.length }})</BaseButton>
          <BaseButton
            v-can="'application.bulk_status_update'"
            :className="'bg-yellow-500 text-white hover:bg-yellow-600'"
            @click="handleBulkStatusUpdate('waiting_list')"
            :disabled="bulkStatusUpdateLoading"
          >{{ t('application.waiting_list') }} ({{ selectedIds.length }})</BaseButton>
          <BaseButton
            v-can="'application.bulk_status_update'"
            :className="'bg-orange-500 text-white hover:bg-orange-600'"
            @click="handleBulkStatusUpdate('rejected_list')"
            :disabled="bulkStatusUpdateLoading"
          >{{ t('application.rejected_list') }} ({{ selectedIds.length }})</BaseButton>
          <BaseButton
            v-can="'application.bulk_status_update'"
            :className="'bg-blue-500 text-white hover:bg-blue-600'"
            @click="handleBulkStatusUpdate('application_list')"
            :disabled="bulkStatusUpdateLoading"
          >{{ t('application.application_list') }} ({{ selectedIds.length }})</BaseButton>
          <BaseButton
            v-can="'application.bulk_status_update'"
            :className="'bg-purple-500 text-white hover:bg-purple-600'"
            @click="handleBulkStatusUpdate('short_list')"
            :disabled="bulkStatusUpdateLoading"
          >{{ t('application.short_list') }} ({{ selectedIds.length }})</BaseButton>
        </template>
      </div>
    </div>
    <div class="flex  flex-wrap justify-between items-center my-4">
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
          <label class="text-gray-800 text-[15px]">{{t('shared.labels.process')}}</label>
          <BaseSearchSelect
            v-model="filters.process_id"
            :options="processData"
            :placeholder="t('application.search_by_process_name')"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterByName"
            @update:model-value="selectedIds = []"
          />
        </div>
        <div class="flex flex-col" v-if="authStore.userType !== 'agent'">
          <label class="text-gray-800 text-[15px]">{{t('shared.labels.demand_letter')}}</label>
          <BaseSearchSelect
            v-model="filters.work_order_id"
            :options="filterWorkOrders"
            :placeholder="t('application.search_by_demand_letter_or_client')"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterBySearchName"
            @update:model-value="selectedIds = []"
          />
        </div>
        <div class="flex flex-col" v-if="authStore.userType !== 'client'">
          <label class="text-gray-800 text-[15px]">{{t('shared.labels.client')}}</label>
          <BaseSearchSelect
            v-model="filters.client_id"
            :options="filterClients"
            :placeholder="t('application.search_by_client_name')"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterBySearchName"
            @update:model-value="selectedIds = []"
          />
        </div>
        <div
          class="flex flex-col"
          v-if="authStore.userType !== 'client' && authStore.userType !== 'agent'"
        >
          <label class="text-gray-800 text-[15px]">{{t('shared.labels.agent')}}</label>
          <BaseSearchSelect
            v-model="filters.agent_id"
            :options="filterAgents"
            placeholder="Search by agent name"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterBySearchName"
            @update:model-value="selectedIds = []"
          />
        </div>
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{t('shared.labels.status')}}</label>
          <BaseSelect
            @change="selectedIds = []"
            v-model="filters.application_status"
            :options="statusOptions"
            placeholder="Select"
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
        <BaseTableSkeleton v-if="isLoading" :columns="columns" selectable show-actions :show-serial="false" />

        <BaseTable
          v-else
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
            <div @click="handleMergeDocuments(row)" class="cursor-pointer">
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

          <template #actions="{ row }">
            <BaseTableButton icon="fa fa-eye" variant="primary" title="View" @click="onView(row)" />
            <BaseTableButton
              v-can="'application.edit'"
              icon="fa fa-pencil"
              variant="success"
              title="Edit"
              @click="onEdit(row)"
            />
            <BaseTableButton
              v-can="'application.generate_biodata'"
              icon="fa fa-file-text"
              variant="info"
              title="Generate Biodata"
              @click="onGenerateBiodata(row)"
            />
            <BaseTableButton
              v-can="'application.delete'"
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

        <AddModal
          :jobs="allJobs"
          :agents="allAgents"
          :subjects="subjects"
          :qualifications="qualifications"
          v-can="'application.create'"
        />
        <EditModal
          :jobs="allJobs"
          :agents="allAgents"
          :subjects="subjects"
          :qualifications="qualifications"
          v-can="'application.edit'"
        />
        <ViewModal v-can="'application.view'" />
        <DeleteModal v-can="'application.delete'" />

        <BulkUploadApplicationModal
          :jobs="allJobs"
          :agents="allAgents"
          v-can="'application.bulk_upload'"
        />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, defineAsyncComponent, provide, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useApplicationStore } from '../store/applicationStore'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import {
  useApplicationDataQuery,
  useApplicationsQuery,
} from '../queries/useApplicationsQuery'
import { useApplicationMutations } from '../queries/useApplicationMutations'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import { usePagination } from '@/shared/composables/usePagination'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import BulkUploadApplicationModal from './components/BulkUploadApplicationModal.vue'
import PageHeader from '@/shared/components/ui/PageHeader.vue'
import { useAuthStore } from '@/modules/auth/store/authStore'
import { useDeleteWithConfirm } from '@/shared/composables/useDeleteWithConfirm'
import { showConfirmDialog } from '@/shared/utils/sweetAlertUtils'
import getTableColumns from '../data/applicationTableData'
import MergeDocumentModal from './components/MergeDocumentModal.vue'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const ViewModal = defineAsyncComponent(() => import('./components/ViewModal.vue'))
const AddModal = defineAsyncComponent(() => import('./components/AddModal.vue'))
const EditModal = defineAsyncComponent(() => import('./components/EditModal.vue'))
const DeleteModal = defineAsyncComponent(() => import('./components/DeleteModal.vue'))

const router = useRouter()
const store = useApplicationStore()
const authStore = useAuthStore()
const pageMessage = ref(null)

const setPageMessage = (type, message) => {
  pageMessage.value = { type, message }
}

const clearPageMessage = () => {
  pageMessage.value = null
}

provide('applicationPageFeedback', { setPageMessage, clearPageMessage })

const mergeDocUrl = ref('') // For debugging URL generation

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  job_id: null,
  work_order_id: null,
  process_id: null,
  client_id: null,
  agent_id: null,
  application_status: null,
  from_date: null,
  to_date: null,
})


const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = useApplicationsQuery(page, perPage, filters)
const { data: applicationData } = useApplicationDataQuery()

pagination.bindMeta(data)

const { remove, removeItems, removeItemsLoading } = useApplicationMutations(store.moduleName)

const { selectedIds, toggleAll, toggleRow, bulkDelete } = useBulkDelete(removeItems)
const {
  bulkStatusUpdateMutation,
  bulkStatusUpdateLoading,
  mergeDocumentsMutation,
  mergeDocumentsLoading,
} = useApplicationMutations(store.moduleName, {
  onSuccess(data) {
    selectedIds.value = []
    if (data?.data?.file) {
      mergeDocUrl.value = data.data.file
    }
  },
})

const handleBulkStatusUpdate = async (status) => {
  if (!selectedIds.value.length) return

  // check if any of selected ids has raw_application_staus as 'ATS'
  const hasAts = selectedIds.value.some((id) => rows.value.find((row) => row.id === id)?.raw_application_status === 'ATS')
  if (hasAts) {
    setPageMessage('error', 'ATS applications cannot be moved to any other status')
    return
  }

  const statusLabel = status
    .split('_')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')


  const result = await showConfirmDialog({
    title: 'Confirm Candidate Move',
    text: `Move ${selectedIds.value.length} application(s) to ${statusLabel}?`,
    icon: 'question',
    confirmButtonText: 'Yes, move now',
    cancelButtonText: 'Cancel',
  })

  if (!result.isConfirmed) return

  try {
    await bulkStatusUpdateMutation.mutateAsync({ ids: selectedIds.value, status })
  } catch (err) {
    console.error('Bulk status update failed:', err)
  }
}

const { confirmDelete } = useDeleteWithConfirm(remove)

// let tableData = [...applicationTableData]
const tableData = computed(() =>
  getTableColumns(t, authStore.userType)
)
const { columns, onView, onEdit } = useCrudTable(store, tableData, {
  timestamps: false,
  trackUser: false,
})




const onGenerateBiodata = (row) => {
  const routeData = router.resolve({
    name: 'Generate BioData',
    query: { id: row.id },
  })
  window.open(routeData.href, '_blank')
}

const handleMergeDocuments = async (row) => {
  if (!row.id) return
  try {
    await mergeDocumentsMutation.mutateAsync(row.id)
  } catch (err) {
    console.error('Merge failed:', err)
  }
}

const filterJobList = (option, query) => {
  const jobName = (option.job_name ?? '').toLowerCase()
  const jobCode = (option.job_code ?? '').toLowerCase()
  const displayName = (option.name ?? '').toLowerCase()

  return jobName.includes(query) || jobCode.includes(query) || displayName.includes(query)
}

const filterByName = (option, query) => {
  return (option.name ?? '').toLowerCase().includes(query)
}

const filterBySearchName = (option, query) => {
  return (option.search_name ?? option.name ?? '').toLowerCase().includes(query)
}

const rows = computed(() => data.value?.data?.data ?? [])
const filterClients = computed(() => applicationData.value?.data?.data?.filter_clients ?? [])
const allAgents = computed(() => applicationData.value?.data?.data?.agents ?? [])
const filterAgents = computed(() => applicationData.value?.data?.data?.filter_agents ?? [])
const jobs = computed(() => applicationData.value?.data?.data?.filter_job_lists ?? [])
const allJobs = computed(() => applicationData.value?.data?.data?.job_lists ?? [])
const subjects = computed(() => applicationData.value?.data?.data?.subjects ?? [])
const qualifications = computed(() => applicationData.value?.data?.data?.qualifications ?? [])
const filterWorkOrders = computed(() => applicationData.value?.data?.data?.filter_work_orders ?? [])
const processData = computed(() => applicationData.value?.data?.data?.filter_processes ?? [])
const statusOptions = computed(() => applicationData.value?.data?.data?.filter_statuses ?? [])
</script>
