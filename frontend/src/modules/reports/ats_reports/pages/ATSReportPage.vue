  <template>
  <SectionHeader>
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <!-- Responsive Heading -->
        <PageTitle className="capitalize">{{ t('report.all_process_reports') }}</PageTitle>
      </div>
    </div>
    <!-- Report Cards -->
    <div
      class="flex flex-col md:flex-row justify-start md:justify-between items-start md:items-center mb-10 md:mb-0"
    >
      <AtsReportNavigation />
      <div class="flex gap-2">
        <BaseButton
          className="bg-blue-500 text-white hover:bg-blue-600 cursor-pointer"
          @click="goToPrint"
        >
          <i class="fa fa-print"></i> {{t('report.print_ats_report')}}</BaseButton
        >
        <!-- Export CSV -->
        <BaseButton @click="exportCSV"> <i class="fa fa-table"></i> {{t('report.export_csv')}}</BaseButton>
      </div>
    </div>
    <!-- FILTERS -->
   <div class="flex flex-col my-4 gap-3">

  <!-- Tabs -->
  <div class="flex flex-wrap gap-2">
    <button
      v-for="tab in filterTabs"
      :key="tab.id"
      type="button"
      class="rounded-xl border px-4 py-2 text-sm font-semibold transition-all shadow-sm"
      :class="
        activeFilterTab === tab.id
          ? 'border-primary bg-primary-light! text-primary ring-1 ring-primary'
          : 'border-gray-200 bg-white text-gray-700 hover:border-primary hover:bg-primary-light!'
      "
      @click="activeFilterTab = tab.id"
    >
      {{ tab.label }}
    </button>
  </div>

  <TableFilters
    class="w-full sm:flex-wrap sm:justify-end"
    :show-search="false"
    :filters="filters"
    :has-active-filters="hasActiveFilters"
    @reset="resetFilters"
  >

    <!-- BASIC FILTER -->
    <template v-if="showBasicFilters">

      <div class="flex flex-col">
        <label class="text-gray-800 text-[15px]">{{t('shared.labels.demand_letter')}}</label>
        <BaseSearchSelect
          v-model="filters.work_order_id"
          :options="workOrderOptions"
          :placeholder="t('application.search_by_demand_letter_or_client')"
          optionLabel="name"
          optionValue="id"
          :filter-fn="filterByName"
        />
      </div>

      <div class="flex flex-col">
        <label class="text-gray-800 text-[15px]">{{t('shared.labels.client')}}</label>
        <BaseSearchSelect
          v-model="filters.client_id"
          :options="clientOptions"
          :placeholder="t('application.search_by_client_name')"
          optionLabel="name"
          optionValue="id"
          :filter-fn="filterByName"
        />
      </div>

      <div class="flex flex-col">
        <label class="text-gray-800 text-[15px]">{{t('shared.labels.job')}}</label>
        <BaseSearchSelect
          v-model="filters.job_id"
          :options="jobOptions"
          :placeholder="t('application.search_by_job_name_or_job_code')"
          optionLabel="name"
          optionValue="id"
          :filter-fn="filterJobList"
        />
      </div>

    </template>

    <!-- Recruitment Status -->
    <template v-if="showRecruitmentFilters">

      <div class="w-full basis-full">
        <label class="text-gray-800 text-[15px] mb-2 block font-medium">
          {{t('report.recruitment_status')}}
        </label>

        <div
          class="rounded-xl border border-indigo-100 bg-gradient-to-br from-indigo-50/40 via-white to-slate-50 p-3 shadow-sm"
        >
          <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-6">
            <label
              v-for="status in statuses"
              :key="status.id"
              class="cursor-pointer rounded-lg border bg-white p-3 py-1 transition-all duration-150"
              :class="
                (filters.application_status || []).includes(status.id)
                  ? 'border-primary bg-primary-light! ring-1 ring-primary'
                  : 'border-gray-200 hover:border-primary hover:bg-primary-light! hover:ring-1 hover:ring-primary'
              "
            >
              <div class="flex items-start gap-3">
                <input
                  type="checkbox"
                  class="mt-0.5 h-4 w-4"
                  :checked="(filters.application_status || []).includes(status.id)"
                  @change="toggleStatusFilter(status.id, $event.target.checked)"
                />

                <span
                  class="block text-xs font-medium leading-snug sm:text-sm"
                >
                  {{ t(`application.${status.id}`) }}
                </span>
              </div>
            </label>
          </div>
        </div>
      </div>

    </template>

    <!-- ATS PROCESS -->
    <template v-if="showAtsProcessFilters">

      <div class="w-full basis-full">
        <label class="text-gray-800 text-[15px] mb-2 block font-medium">
          {{t('report.ats_process')}}
        </label>

        <div
          class="rounded-xl border border-indigo-100 bg-gradient-to-br from-indigo-50/40 via-white to-slate-50 p-3 shadow-sm"
        >
          <div
            class="grid grid-cols-1 gap-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6"
          >
            <label
              v-for="process in processOptions"
              :key="process.id"
              class="cursor-pointer rounded-lg border bg-white p-3 py-1 transition-all duration-150"
              :class="
                isProcessSelected(process.id)
                  ? 'border-primary bg-primary-light! ring-1 ring-primary'
                  : 'border-gray-200 hover:border-primary hover:bg-primary-light! hover:ring-1 hover:ring-primary'
              "
            >
              <div class="flex items-start gap-3">
                <input
                  type="checkbox"
                  class="mt-0.5 h-4 w-4"
                  :checked="isProcessSelected(process.id)"
                  @change="toggleProcessFilter(process.id, $event.target.checked)"
                />

                <span
                  class="block text-xs font-medium capitalize leading-snug sm:text-sm"
                >
                  <!-- {{ t(`dashboard.pipeline.${process.name}`) ? t(`dashboard.pipeline.${process.name}`) : t(`shared.labels.${process.name}`) }} -->
                  {{  process.name === 'rejected'  || process.name === 'declined' || process.name === 'deployed' ? t(`shared.labels.${process.name}`) :  t(`dashboard.pipeline.${process.name}`)}}
                </span>
              </div>
            </label>
          </div>
        </div>
      </div>

    </template>

  </TableFilters>

</div>

        <!-- Content Card -->
    <div class="rounded-lg bg-white shadow-sm">
      <div>
        <BaseTable :columns="columns" :rows="rows" :current-page="page"
          :per-page="perPage"/>
        <BasePagination
          v-if="!isLoading"
          :total="total"
          :showing="showing"
          :links="links"
          :per-page="perPage"
          @update:page="setPage"
          @update:perPage="setPerPage"
        />
      </div>
    </div>
  </SectionHeader>
</template>

<script setup>
import { computed, ref  } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useAtsReportStore } from '../store/atsReportStore'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import {
  useReportClientsQuery,
  useReportJobsQuery,
  useReportProcessQuery,
  useReportWorkOrdersQuery,
} from '../../queries/useReportsQuery'
import { removeEmptyKeys } from '@/shared/helpers/objectHelper'
import { useAtsReportQuery, useExportCSVQuery } from '../queries/useAtsReportsQuery'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import AtsReportNavigation from './components/AtsReportNavigation.vue'
import PageTitle from '@/shared/components/ui/PageTitle.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import { usePagination } from '@/shared/composables/usePagination'
import { useTranslate } from '@/shared/composables/useTranslate'
const {t} = useTranslate()
const route = useRoute()
const router = useRouter()
const reportType = computed(() => route.params.type)
const store = useAtsReportStore()

const { filters, resetFilters } = useTableFilters({
  process_id: null,
  application_status: null,
  work_order_id: null,
  client_id: null,
  job_id: null,
  type: reportType,
  from_date: null,
  to_date: null,
})


const statuses = [
  { id: 'hiring_list', name: 'Hiring List' },
  { id: 'ats', name: 'ATS' },
  { id: 'waiting_list', name: 'Waiting List' },
  { id: 'rejected_list', name: 'Rejected List' },
  { id: 'application_list', name: 'Application List' },
  { id: 'short_list', name: 'Short List' },
]



const activeFilterTab = ref('basic')

const filterTabs = computed(() => [
  { id: 'basic', label: t('report.basic_filter') },
  { id: 'recruitment', label: t('report.recruitment_status') },
  { id: 'ats_process', label: t('report.ats_process') },
  { id: 'all', label: t('report.show_all') },
])

const showBasicFilters = computed(() => {
  return activeFilterTab.value === 'basic' || activeFilterTab.value === 'all'
})

const showRecruitmentFilters = computed(() => {
  return activeFilterTab.value === 'recruitment' || activeFilterTab.value === 'all'
})

const showAtsProcessFilters = computed(() => {
  return activeFilterTab.value === 'ats_process' || activeFilterTab.value === 'all'
})


const isProcessSelected = (processId) => {
  return (filters.value.process_id || []).includes(processId)
}
const formatProcessLabel = (name) => {
  return (name ?? '').replace(/_/g, ' ')
}
const toggleStatusFilter = (statusId, checked) => {
  const current = Array.isArray(filters.value.application_status)
    ? [...filters.value.application_status]
    : []

  const next = checked
    ? Array.from(new Set([...current, statusId]))
    : current.filter((item) => item !== statusId)

  filters.value.application_status = next.length ? next : null
}

const toggleProcessFilter = (processId, checked) => {
  const current = Array.isArray(filters.value.process_id)
    ? [...filters.value.process_id]
    : []

  const next = checked
    ? Array.from(new Set([...current, processId]))
    : current.filter((item) => item !== processId)

  filters.value.process_id = next.length ? next : null
}

const hasActiveFilters = computed(() => {
  return Object.entries(filters.value).some(([key, value]) => key !== 'type' && value)
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination
const { data } = useAtsReportQuery(page, perPage, filters)
pagination.bindMeta(data)
const rows = computed(() => data.value?.data?.data ?? [])

const columnTemp = computed(() => {
  return [
    { key: 'job_name', label: t('shared.labels.job_name') },
    { key: 'passport_no', label: t('shared.labels.passport_no') },
    { key: 'client', label: t('shared.labels.client') },
    { key: 'candidate', label:  t('shared.labels.candidate') },
    { key: 'phone', label: t('shared.labels.phone') },
    { key: 'medical_test', label: t(`dashboard.pipeline.${'medical_test'}`) },
    { key: 'police_clearance', label: t(`dashboard.pipeline.${'police_clearance'}`) },
    { key: 'trade_test', label: t(`dashboard.pipeline.${'trade_test'}`) },
    { key: 'biometric_enrollment', label: t(`dashboard.pipeline.${'biometric_enrollment'}`) },
    { key: 'immigration_clearance', label: t(`dashboard.pipeline.${'immigration_clearance'}`) },
    { key: 'visa_endorsement', label: t(`report.${'visa_endorsement'}`) },
    { key: 'visa_expiry', label: t(`ats.${'visa_expiry_date'}`) },
    { key: 'flight_date', label: t(`report.${'flight_date'}`) },
    { key: 'status', label: t('shared.labels.status') },
    { key: 'days', label: t('report.days') },
    { key: 'total_process_days', label: t('report.total_process_days') },
    { key: 'remarks', label: t('application.remarks') },
  ]})

const { columns } = useCrudTable(
  store,
  columnTemp,
  { timestamps: false, trackUser: false },
)

const goToPrint = () => {
  const filtered = removeEmptyKeys(filters.value)

  filtered.jobName = filtered.job_id ? getJobNameById(filtered.job_id) : ''

  filtered.workOrderId = filtered.work_order_id ? getWorkOrderIdById(filtered.work_order_id) : ''

  filtered.processName = filtered.process_id ? getProcessNameById(filtered.process_id) : ''

  filtered.clientId = filtered.client_id ? getClientNameById(filtered.client_id) : ''

  // ADD DATES
  filtered.from_date = filtered.from_date ? filtered.from_date : ''

  filtered.to_date = filtered.to_date ? filtered.to_date : ''

  const filteredFinal = removeEmptyKeys(filtered)

  // create route URL
  const routeData = router.resolve({
    name: 'ATS Report Print',
    params: {
      type: reportType.value,
    },
    query: filteredFinal,
  })

  console.log(routeData)

  // open new tab
  window.open(routeData.href, '_blank')
}

// EXPORT CSV Function
const { refetch: fetchCSV } = useExportCSVQuery(filters)

const exportCSV = async () => {
  try {
    const res = await fetchCSV()

    if (!res?.data?.data) {
      alert('No data available for export.')
      return
    }

    let csvData = res.data.data

    if (csvData.startsWith('"') && csvData.endsWith('"')) {
      csvData = csvData.slice(1, -1)
    }

    csvData = csvData.replace(/\\"/g, '"')

    const blob = new Blob([csvData], {
      type: 'text/csv;charset=utf-8;',
    })

    const url = window.URL.createObjectURL(blob)

    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'ats_report.csv')

    document.body.appendChild(link)
    link.click()

    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('CSV Export Error:', error)
    alert('Failed to export CSV.')
  }
}

const getJobNameById = (jobId) => {
  return jobOptions.value.find((job) => job.id == jobId).name
}

const getWorkOrderIdById = (workOrderId) => {
  return workOrderOptions.value.find((workOrder) => workOrder.id == workOrderId).name
}

const getProcessNameById = (processId) => {
  return processOptions.value.find((process) => process.id == processId).name
}

const getClientNameById = (clientId) => {
  return clientOptions.value.find((client) => client.id == clientId).name
}

const { data: processData } = useReportProcessQuery()
const { data: workOrdersData } = useReportWorkOrdersQuery()
const { data: jobsData } = useReportJobsQuery()
const { data: clientsData } = useReportClientsQuery()

const toSelectOptions = (queryResult) => {
  const payload = queryResult?.data
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  return []
}

const processOptions = computed(() => toSelectOptions(processData.value))
const workOrderOptions = computed(() => toSelectOptions(workOrdersData.value))
const jobOptions = computed(() => toSelectOptions(jobsData.value))
const clientOptions = computed(() => toSelectOptions(clientsData.value))
</script>
