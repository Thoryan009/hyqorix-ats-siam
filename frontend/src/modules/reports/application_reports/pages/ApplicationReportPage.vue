<template>
  <SectionHeader>
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <!-- Responsive Heading -->
        <PageTitle class="capitalize">{{t('report.applicant_reports')}}</PageTitle>
      </div>
    </div>
    <!-- Report Cards -->
    <div
      class="flex flex-col md:flex-row justify-start md:justify-between items-start md:items-center"
    >
    <!-- <pre>{{ processData }}</pre> -->
      <ApplicationReportNavigation />
      <div class="flex gap-2">
        <BaseButton @click="goToPrint" className="bg-blue-500 text-white hover:bg-blue-600 cursor-pointer">
          {{ t('report.print_application_report') }}
        </BaseButton>
          <!-- Export CSV -->
        <BaseButton @click="exportCSV"> <i class="fa fa-table"></i> {{ t('report.export_csv') }}</BaseButton>
      </div>
    </div>

    <!-- FILTERS -->
    <div class="flex flex-col my-4 gap-3">
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
        <template v-if="showBasicFilters">
          <!-- Countries FILTER -->
          <div class="flex flex-col">
            <label class="text-gray-800 text-[15px]">{{t('shared.labels.country')}}</label>
            <BaseSearchSelect
              v-model="filters.country_id"
              :options="countryOptions"
              :placeholder="t('application.search_by_country_name')"
              optionLabel="name"
              optionValue="id"
              :filter-fn="filterByName"
            />
          </div>
          <!-- Work Order FILTER -->
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
          <!-- Jobs FILTER -->
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

          <!-- Agents FILTER -->
          <div
            class="flex flex-col"
            v-if="authStore.userType != 'agent' && authStore.userType != 'client' && authStore.userType != 'principal'"
          >
            <label class="text-gray-800 text-[15px]">{{t('shared.labels.agent')}}</label>
            <BaseSearchSelect
              v-model="filters.agent_id"
              :options="agentOptions"
              :placeholder="t('application.search_by_agent_name')"
              optionLabel="name"
              optionValue="id"
              :filter-fn="filterByName"
            />
          </div>
          <!-- Principals FILTER -->
          <div
            class="flex flex-col"
            v-if="authStore.userType != 'principal' && authStore.userType != 'agent' && authStore.userType != 'client'"
          >
            <label class="text-gray-800 text-[15px]">{{t('shared.labels.principal')}}</label>
            <BaseSearchSelect
              v-model="filters.principal_id"
              :options="principalOptions"
              :placeholder="t('application.search_by_principal_name')"
              optionLabel="name"
              optionValue="id"
              :filter-fn="filterByName"
            />
          </div>
          <!-- Clients -->
          <div class="flex flex-col" v-if="authStore.userType != 'client'">
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
        </template>

        <template v-if="showRecruitmentFilters">
          <!-- Recruitment Status filter -->
          <div class="w-full basis-full">
            <label class="text-gray-800 text-[15px] mb-2 block font-medium">{{t('report.recruitment_status')}}</label>
            <div class="rounded-xl border border-indigo-100 bg-gradient-to-br from-indigo-50/40 via-white to-slate-50 p-3 shadow-sm">
              <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-6">
                <label
                  v-for="status in statuses"
                  :key="status.id"
                  class="cursor-pointer rounded-lg border bg-white p-3 py-1 transition-all duration-150"
                  :class="
                    isStatusSelected(status.id)
                      ? 'border-primary bg-primary-light! ring-1 ring-primary'
                      : 'border-gray-200 hover:border-primary hover:bg-primary-light! hover:ring-1 hover:ring-primary'
                  "
                >
                  <div class="flex items-start gap-3">
                    <input
                      type="checkbox"
                      class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-primary focus:ring-primary"
                      :checked="isStatusSelected(status.id)"
                      @change="toggleStatusFilter(status.id, $event.target.checked)"
                    />
                    <span
                      class="block text-xs font-medium leading-snug sm:text-sm"
                      :class="isStatusSelected(status.id) ? 'text-primary' : 'text-gray-700'"
                    >
                      {{ t(`application.${status.id}`) }}
                    </span>
                  </div>
                </label>
              </div>
            </div>
          </div>
        </template>

        <template v-if="showAtsProcessFilters">
          <!-- Processes FILTER -->
          <div class="w-full basis-full">
            <label class="text-gray-800 text-[15px] mb-2 block font-medium">{{t('report.ats_process')}}</label>
            <div class="rounded-xl border border-indigo-100 bg-gradient-to-br from-indigo-50/40 via-white to-slate-50 p-3 shadow-sm">
              <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
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
                      class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-primary focus:ring-primary"
                      :checked="isProcessSelected(process.id)"
                      @change="toggleProcessFilter(process.id, $event.target.checked)"
                    />
                    <span
                      class="block text-xs font-medium capitalize leading-snug sm:text-sm"
                      :class="isProcessSelected(process.id) ? 'text-primary' : 'text-gray-700'"
                    >
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
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCrudTable } from '@/shared/composables/useCrudTable'
import { useApplicationReportStore } from '../store/applicationReportStore'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useApplicationReportQuery , useExportCSVQuery } from '../queries/useApplicationReportsQuery'
import {
  useReportClientsQuery,
  useReportCountryQuery,
  useReportJobsQuery,
  useReportWorkOrdersQuery,
  useReportAgentsQuery,
  useReportPrincipalsQuery,
  useReportProcessQuery,
} from '../../queries/useReportsQuery'
import { removeEmptyKeys } from '@/shared/helpers/objectHelper'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import ApplicationReportNavigation from './components/ApplicationReportNavigation.vue'
import PageTitle from '@/shared/components/ui/PageTitle.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import { useAuthStore } from '@/modules/auth/store/authStore'
import applicationReportTableData from '../data/applicationReportTableData'
import { filterColumns } from '../composables/useReportUtils'
import { usePagination } from '@/shared/composables/usePagination'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const route = useRoute()
const router = useRouter()
const reportType = computed(() => route.params.type)
const store = useApplicationReportStore()
const authStore = useAuthStore()

const activeFilterTab = ref('basic')

const filterTabs = computed(() => {
  const tabs = [
    { id: 'basic', label: t('report.basic_filter') },
    { id: 'recruitment', label: t('report.recruitment_status') },
  ]

  if (authStore.userType !== 'client') {
    tabs.push({ id: 'ats_process', label: t('report.ats_process') })
  }

  tabs.push({ id: 'all', label: t('report.show_all') })

  return tabs
})

const showBasicFilters = computed(() => {
  return activeFilterTab.value === 'basic' || activeFilterTab.value === 'all'
})

const showRecruitmentFilters = computed(() => {
  return activeFilterTab.value === 'recruitment' || activeFilterTab.value === 'all'
})

const showAtsProcessFilters = computed(() => {
  return (
    authStore.userType !== 'client' &&
    (activeFilterTab.value === 'ats_process' || activeFilterTab.value === 'all')
  )
})

const { filters, resetFilters } = useTableFilters({
  country_id: null,
  agent_id: null,
  principal_id: null,
  work_order_id: null,
  job_id: null,
  client_id: null,
  process_id: null,
  application_status: null,
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

const hasActiveFilters = computed(() => {
  return Object.entries(filters.value).some(([key, value]) => {
    if (key === 'type') return false
    if (Array.isArray(value)) return value.length > 0
    return Boolean(value)
  })
})

const toggleStatusFilter = (statusId, checked) => {
  const current = Array.isArray(filters.value.application_status)
    ? [...filters.value.application_status]
    : []

  const next = checked
    ? Array.from(new Set([...current, statusId]))
    : current.filter((item) => item !== statusId)

  filters.value.application_status = next.length ? next : null
}

const isStatusSelected = (statusId) => {
  return (filters.value.application_status || []).includes(statusId)
}

const toggleProcessFilter = (processId, checked) => {
  const current = Array.isArray(filters.value.process_id) ? [...filters.value.process_id] : []

  const next = checked
    ? Array.from(new Set([...current, processId]))
    : current.filter((item) => item !== processId)

  filters.value.process_id = next.length ? next : null
}

const isProcessSelected = (processId) => {
  return (filters.value.process_id || []).includes(processId)
}


const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination
const { data } = useApplicationReportQuery(page, perPage, filters)
pagination.bindMeta(data)
const rows = computed(() => data.value?.data?.data ?? [])

const tableData = computed(() => {
  return filterColumns(applicationReportTableData, reportType.value).map(column => ({
    ...column,
    label: t(column.label),
  }))
})

const { columns } = useCrudTable(store, tableData)

const goToPrint = () => {
  const filtered = removeEmptyKeys(filters.value)

  filtered.jobName = filtered.job_id ? getJobNameById(filtered.job_id) : ''
  filtered.workOrderCode = filtered.work_order_id ? getWorkOrderIdById(filtered.work_order_id) : ''
  filtered.countryName = filtered.country_id ? getCountryNameById(filtered.country_id) : ''
  filtered.clientName = filtered.client_id ? getClientNameById(filtered.client_id) : ''
  filtered.agentName = filtered.agent_id ? getAgentNameById(filtered.agent_id) : ''
  filtered.principalName = filtered.principal_id ? getPrincipalNameById(filtered.principal_id) : ''

  // ADD DATES
  filtered.from_date = filtered.from_date ? filtered.from_date : ''

  filtered.to_date = filtered.to_date ? filtered.to_date : ''

  const filteredFinal = removeEmptyKeys(filtered)

  // create route URL
  const routeData = router.resolve({
    name: 'Application Report Print',
    params: {
      type: reportType.value,
    },
    query: filteredFinal,
  })

  console.log(routeData)

  // open new tab
  window.open(routeData.href, '_blank')
}

const getJobNameById = (jobId) => {
  return jobOptions.value.find((job) => job.id == jobId).name
}

const getWorkOrderIdById = (workOrderId) => {
  return workOrderOptions.value.find((workOrder) => workOrder.id == workOrderId).name
}

const getCountryNameById = (countryId) => {
  return countryOptions.value.find((country) => country.id == countryId).name
}

const getClientNameById = (clientId) => {
  return clientOptions.value.find((client) => client.id == clientId).name
}

const getAgentNameById = (agentId) => {
  return agentOptions.value.find((agent) => agent.id == agentId).name
}

const getPrincipalNameById = (principalId) => {
  return principalOptions.value.find((principal) => principal.id == principalId).name
}

const { data: countryData } = useReportCountryQuery()
const { data: agentData } = useReportAgentsQuery()
const { data: workOrdersData } = useReportWorkOrdersQuery()
const { data: jobsData } = useReportJobsQuery()
const { data: clientsData } = useReportClientsQuery()
const { data: principalData } = useReportPrincipalsQuery()
const { data: processData } = useReportProcessQuery()

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
    link.setAttribute('download', 'application_report.csv')

    document.body.appendChild(link)
    link.click()

    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)

  } catch (error) {
    console.error('CSV Export Error:', error)
    alert('Failed to export CSV.')
  }
}

let name = ''
if (reportType.value === 'application') {
  name = 'Applicant'
}

const filterByName = (option, query) => {
  return (option.name ?? '').toLowerCase().includes(query)
}

const filterJobList = (option, query) => {
  const jobName = (option.job_name ?? '').toLowerCase()
  const jobCode = (option.job_code ?? '').toLowerCase()
  const displayName = (option.name ?? '').toLowerCase()

  return jobName.includes(query) || jobCode.includes(query) || displayName.includes(query)
}

const toSelectOptions = (queryResult) => {
  const payload = queryResult?.data
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  return []
}

const countryOptions = computed(() => toSelectOptions(countryData.value))
const workOrderOptions = computed(() => toSelectOptions(workOrdersData.value))
const jobOptions = computed(() => toSelectOptions(jobsData.value))
const clientOptions = computed(() => toSelectOptions(clientsData.value))
const agentOptions = computed(() => toSelectOptions(agentData.value))
const processOptions = computed(() => toSelectOptions(processData.value))
const principalOptions = computed(() => toSelectOptions(principalData.value))

const applyRouteQueryFilters = () => {
  const filterTab = route.query.filter_tab
  if (filterTab && filterTabs.value.some((tab) => tab.id === filterTab)) {
    activeFilterTab.value = Array.isArray(filterTab) ? filterTab[0] : filterTab
  }

  const processName = route.query.process
  if (processName) {
    const name = Array.isArray(processName) ? processName[0] : processName
    const match = processOptions.value.find((process) => process.name === name)
    if (match) {
      filters.value.process_id = [match.id]
      if (authStore.userType !== 'client') {
        activeFilterTab.value = 'ats_process'
      }
      return
    }
  }

  const processIdQuery = route.query.process_id
  if (!processIdQuery) return

  const ids = (Array.isArray(processIdQuery) ? processIdQuery : [processIdQuery])
    .map((id) => Number(id))
    .filter((id) => !Number.isNaN(id))

  if (ids.length) {
    filters.value.process_id = ids
    if (authStore.userType !== 'client') {
      activeFilterTab.value = 'ats_process'
    }
  }
}

watch(
  processOptions,
  (options) => {
    if (options.length) {
      applyRouteQueryFilters()
    }
  },
  { immediate: true },
)
</script>
