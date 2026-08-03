<template>
  <SectionHeader>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <PageTitle>{{ t('report.process_expiry_reports') }}</PageTitle>
      </div>
    </div>
  <!-- Status summary cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 my-4">
      <button
        v-for="(card, index) in statusCards"
        :key="card.id"
        type="button"
        class="group relative overflow-hidden p-4 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 text-left w-full border-2 flex items-center gap-3 min-h-[4.5rem]"
        :class="
          isStatusCardActive(card)
            ? 'border-primary bg-primary-light! ring-1 ring-primary'
            : 'border-primary/30 bg-linear-to-br from-primary/10 to-primary/10 hover:border-primary'
        "
        :style="{ animationDelay: `${index * 50}ms` }"
        @click="setProcessFilter(card.processId)"
      >
        <div
          class="absolute -right-4 -top-4 w-14 h-14 rounded-full opacity-10 group-hover:scale-150 transition-transform duration-500"
          :style="{ backgroundColor: card.color }"
        ></div>

        <div
          class="relative shrink-0 w-10 h-10 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-all duration-300"
          :style="{ background: `linear-gradient(135deg, ${card.color}, ${card.color}dd)` }"
        >
          <i :class="`${card.icon} text-base text-white`"></i>
        </div>

        <div class="relative min-w-0">
          <p class="text-sm font-semibold uppercase tracking-wide text-gray-600 leading-snug truncate">
            {{ t(`report.card.${card.id}`) }}
          </p>
          <p class="text-xl font-bold leading-snug" :style="{ color: card.color }">
            {{ card.value }}
          </p>
        </div>
      </button>
    </div>
       <div
    class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2 mt-4">
         <BaseButton
          className="bg-blue-500 text-white hover:bg-blue-600 cursor-pointer"
          @click="goToPrint"
        >
          <i class="fa fa-print"></i> {{ t('report.print_ats_report') }}</BaseButton
        >
        <BaseButton
  className="bg-green-600 text-white hover:bg-green-700 cursor-pointer"
  @click="exportCSV"
>
  <i class="fa fa-file-csv"></i>
  {{ t('report.export_csv') }}
</BaseButton>
    </div>

    <div class="flex justify-end items-center my-4">
      <TableFilters
        :show-search="false"
        :filters="filters"
        :has-active-filters="hasActiveFilters"
        @reset="resetFilters"
      >

        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{ t('shared.labels.document') }}</label>
          <BaseSelect v-model="filters.process" :options="processOptions" placeholder="Select" />
        </div>

      <!-- Client FILTER -->
         <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{ t('shared.labels.client') }}</label>
          <BaseSearchSelect
            v-model="filters.client_id"
            :options="clientData"
            :placeholder="t('application.search_by_client_name')"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterByName"
          />
        </div>
       <!-- Jobs FILTER -->
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{ t('shared.labels.job') }}</label>
          <BaseSearchSelect
            v-model="filters.job_id"
            :options="jobData"
            :placeholder="t('application.search_by_job_name_or_job_code')"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterJobList"
          />
        </div>

        <!-- Agents FILTER -->
        <div
          class="flex flex-col"
        >
          <label class="text-gray-800 text-[15px]">{{ t('shared.labels.agent') }}</label>
          <BaseSearchSelect
            v-model="filters.agent_id"
            :options="agentData"
            :placeholder="t('application.search_by_agent_name')"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterByName"
          />
        </div>
      </TableFilters>
    </div>

    <div class="rounded-lg bg-white shadow-sm">
      <BaseTable :columns="columns" :rows="rows" />
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
  </SectionHeader>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePagination } from '@/shared/composables/usePagination'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useExpiryReportQuery, useExpiryFilterDataQuery, useExportExpiryReportCsv } from '../queries/useExpiryReportQuery'
import { useExpiryStatusCards } from '../composables/useExpiryStatusCards'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import PageTitle from '@/shared/components/ui/PageTitle.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import { removeEmptyKeys } from '@/shared/helpers/objectHelper'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const route = useRoute()
const router = useRouter()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  process: null,
  client_id: null,
  agent_id: null,
  job_id: null,
  from_date: null,
  to_date: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination
const { data, isLoading } = useExpiryReportQuery(page, perPage, filters)
pagination.bindMeta(data)

const rows = computed(() => data.value?.data?.data ?? [])

const { data: filterData } = useExpiryFilterDataQuery(filters)

const clientData = computed(() => filterData.value?.data?.clients ?? [])
const agentData = computed(() => filterData.value?.data?.agents ?? [])
const jobData = computed(() => filterData.value?.data?.jobs ?? [])
const processOptions = computed(() => filterData.value?.data?.documents ?? [])

const { statusCards } = useExpiryStatusCards(processOptions)

const applyRouteQueryFilters = () => {
  const process = route.query.process
  if (!process) return

  const value = Array.isArray(process) ? process[0] : process
  filters.value.process = value || null
}

watch(() => route.query.process, applyRouteQueryFilters, { immediate: true })

const isStatusCardActive = (card) => {
  if (card.processId === null) {
    return !filters.value.process
  }
  return filters.value.process === card.processId
}

const setProcessFilter = (processId) => {
  filters.value.process = processId
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

const columnTemp  = computed(() => {
  return [
    { key: 'sl', label: t('shared.labels.sl') },
    { key: 'passport_no', label: t('shared.labels.passport_no') },
    { key: 'candidate_name', label: t('shared.labels.candidate_name') },
    { key: 'mobile', label: t('shared.labels.mobile') },
    { key: 'agent_name', label: t('shared.labels.agent') + ' ' + t('shared.labels.name') },
    { key: 'client_name', label: t('shared.labels.client') + ' ' + t('shared.labels.name') },
    { key: 'job_name', label: t('shared.labels.job_name') },
    { key: 'agent_mobile_no', label: t('report.agent_mobile_no') },
    { key: 'document', label: t('shared.labels.document') },
    { key: 'expiry_date_formatted', label: t('report.expiry_date') },
    { key: 'days_left', label: t('report.days_left') },
    { key: 'current_process', label: t('report.current_process') },
  ]
})

const columns = computed(() => {
  return [
    { key: 'sl', label: t('shared.labels.sl') },
    { key: 'passport_no', label: t('shared.labels.passport_no') },
    { key: 'candidate_name', label: t('shared.labels.candidate_name') },
    { key: 'mobile', label: t('shared.labels.phone') },
    { key: 'agent_name', label: t('shared.labels.agent') + ' ' + t('shared.labels.name') },
    { key: 'client_name', label: t('shared.labels.client') + ' ' + t('shared.labels.name') },
    { key: 'job_name', label: t('shared.labels.job_name') },
    { key: 'agent_mobile_no', label: t('report.agent_mobile_no') },
    { key: 'document', label: t('shared.labels.document') },
    { key: 'expiry_date_formatted', label: t('report.expiry_date') },
    { key: 'days_left', label: t('report.days_left') },
    { key: 'current_process', label: t('report.current_process') },
  ]
})

const goToPrint = () => {
  const filtered = removeEmptyKeys(filters.value)

  // Optional (Print title-এর জন্য)
  filtered.clientName = filtered.client_id ? getClientNameById(filtered.client_id) : ''


  filtered.jobName = filtered.job_id
    ? getJobNameById(filtered.job_id)
    : ''
  filtered.processName = filtered.process
    ? processOptions.value.find((doc) => doc.id == filtered.process)?.name || ''
    : ''
  filtered.agentName = filtered.agent_id
    ? getAgentNameById(filtered.agent_id)
    : ''

  const filteredFinal = removeEmptyKeys(filtered)

  const routeData = router.resolve({
    name: 'Process Expiry Report Print',
    query: filteredFinal,
  })

  window.open(routeData.href, '_blank')
}

const clientOptions = computed(() => clientData.value)
const jobOptions = computed(() => jobData.value)
const agentOptions = computed(() => agentData.value)

const getClientNameById = (clientId) => {
  return clientOptions.value.find((client) => client.id == clientId).name
}


const getJobNameById = (jobId) => {
  return jobOptions.value.find((job) => job.id == jobId).name
}


const getAgentNameById = (agentId) => {
  return agentOptions.value.find((agent) => agent.id == agentId).name
}




const { refetch: fetchCSV } = useExportExpiryReportCsv(filters)

const exportCSV = async () => {
  try {
    const res = await fetchCSV()

    if (!res?.data?.data) {
      alert('No data available for export.')
      return
    }

    // Create a Blob from the CSV data
    const blob = new Blob([res.data.data], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)

    // Create a temporary anchor element to trigger the download
    const a = document.createElement('a')
    a.href = url
    a.download = 'expiry_report.csv'
    document.body.appendChild(a)
    a.click()

    // Clean up
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Error exporting CSV:', error)
  }
}

</script>
