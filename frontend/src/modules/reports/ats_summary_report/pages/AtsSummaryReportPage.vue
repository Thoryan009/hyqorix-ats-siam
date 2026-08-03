<template>
  <SectionHeader>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <PageTitle>{{t('report.ats_summary_reports')}}</PageTitle>
      </div>
    </div>

    <div
    class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2 mt-4">
         <BaseButton
          className="bg-blue-500 text-white hover:bg-blue-600 cursor-pointer"
          @click="goToPrint"
        >
          <i class="fa fa-print"></i> {{t('report.print_ats_report')}}</BaseButton
        >
        <BaseButton
  className="bg-green-600 text-white hover:bg-green-700 cursor-pointer"
  @click="exportCSV"
>
  <i class="fa fa-file-csv"></i>
  {{t('report.export_csv')}}
</BaseButton>
    </div>
    <!-- <pre>{{ statusCards }}</pre> -->
  <!-- Status summary cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3 my-4">
      <button
        v-for="(card, index) in statusCards"
        :key="card.key"
        type="button"
        class="group relative overflow-hidden p-4 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 text-left w-full border-2 flex items-center gap-3 min-h-[4.5rem]"
        :style="{
          borderColor: `${card.color}50`,
          animationDelay: `${index * 50}ms`
        }"
      >
      <div
          class="absolute -right-4 -top-4 w-14 h-14 rounded-full opacity-10 group-hover:scale-150 transition-transform duration-500"
          :style="{ backgroundColor: card.color }"
        />
      <div
        class="relative shrink-0 w-10 h-10 rounded-lg flex items-center justify-center shadow-md group-hover:scale-105 transition-all duration-300"
        :style="{
          background: `linear-gradient(135deg, ${card.color}, ${card.color}dd)`
        }"
      >
        <i :class="`${card.icon} text-base text-white`"></i>
      </div>

      <div class="relative min-w-0">
        <p class="text-sm font-semibold uppercase tracking-wide text-gray-600 leading-snug truncate">
          {{  card.key === 'hiring_list'  ? t(`application.${card.key}`) :  t(`dashboard.pipeline.${card.key}`)}}
        </p>

        <p
          class="text-xl font-bold leading-snug"
          :style="{ color: card.color }"
        >
          {{ card.value }}
        </p>
      </div>
      </button>
    </div>

    <div class="flex justify-end items-center my-4">
      <TableFilters
        :show-search="false"
        :filters="filters"
        :has-active-filters="hasActiveFilters"
        @reset="resetFilters"
      >


      <!-- Client FILTER -->
         <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{t('shared.labels.client')}}</label>
          <BaseSearchSelect
            v-model="filters.client_id"
            :options="clientData"
            :placeholder="t('application.search_by_client_name')"
            optionLabel="name"
            optionValue="id"
            :filter-fn="filterByName"
          />
        </div>


      </TableFilters>
    </div>

    <div class="rounded-lg bg-white shadow-sm">

      <BaseTable :columns="columns" :rows="rows" />
    </div>
    <p class="text-xl font-bold mt-5">  {{t('report.total_active_applications', { total: total })}}</p>

    <p class="text-gray-600 mt-5"><strong class="text-black ">{{t('report.note')}}: </strong>{{t('report.note_tra_process')}}</p>
  </SectionHeader>
</template>

<script setup>
import { computed } from 'vue'
import { usePagination } from '@/shared/composables/usePagination'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import {  useAtsSummaryReportQuery, useExportAtsSummaryReportCsv } from '../queries/useAtsSummaryReportQuery'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import PageTitle from '@/shared/components/ui/PageTitle.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import { removeEmptyKeys } from '@/shared/helpers/objectHelper'
import { useRouter } from 'vue-router'
import { useTranslate } from '@/shared/composables/useTranslate'
const {t} = useTranslate()
const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  client_id: null,
  job_id: null,
})

const router = useRouter()

const pagination = usePagination()
const { page, perPage} = pagination
const { data } = useAtsSummaryReportQuery(page, perPage, filters)
pagination.bindMeta(data)

const rows = computed(() => data.value?.data?.rows ?? [])
const clientData = computed(() => data.value?.data?.clients ?? [])
const total = computed(() => data.value?.data?.grand_total ?? 0)



const filterByName = (option, query) => {
  return (option.name ?? '').toLowerCase().includes(query)
}



const goToPrint = () => {
  const filtered = removeEmptyKeys(filters.value)
  filtered.clientName = filtered.client_id ? getClientNameById(filtered.client_id) : ''

  // ADD DATES
  filtered.from_date = filtered.from_date ? filtered.from_date : ''
  filtered.to_date = filtered.to_date ? filtered.to_date : ''
  const filteredFinal = removeEmptyKeys(filtered)
  // create route URL
  const routeData = router.resolve({
    name: 'ATS Summary Report Print',
    query: filteredFinal,
  })
  console.log(routeData)
  // open new tab
  window.open(routeData.href, '_blank')
}

const { refetch: fetchCSV } = useExportAtsSummaryReportCsv(filters)

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

const clientOptions = computed(() => clientData.value)

// console.log('clientOptions.value', clientOptions.value)

const getClientNameById = (clientId) => {
  return clientOptions.value.find((client) => client.id == clientId).name
}




const cardConfig = {
  offer_extended: {
    icon: 'fa fa-file-text',
    color: '#10b981',
  },
  visa_authorization: {
    icon: 'fa fa-id-card',
    color: '#3b82f6',
  },
  medical_test: {
    icon: 'fa fa-medkit',
    color: '#ef4444',
  },
  police_clearance: {
    icon: 'fa fa-shield',
    color: '#8b5cf6',
  },
  trade_test: {
    icon: 'fa fa-wrench',
    color: '#f59e0b',
  },
  biometric_enrollment: {
    icon: 'fa fa-fingerprint',
    color: '#06b6d4',
  },
  embassy_submission: {
    icon: 'fa fa-building',
    color: '#ec4899',
  },
  bmet_training: {
    icon: 'fa fa-graduation-cap',
    color: '#14b8a6',
  },
  bmet_biometric_enrollment: {
    icon: 'fa fa-user-circle',
    color: '#6366f1',
  },
  immigration_clearance: {
    icon: 'fa fa-plane',
    color: '#84cc16',
  },
  pta_request: {
    icon: 'fa fa-paper-plane',
    color: '#f97316',
  },
  tra_process: {
    icon: 'fa fa-cogs',
    color: '#64748b',
  },
  on_boarding: {
    icon: 'fa fa-users',
    color: '#b400c9',
  },
}

const statusCards = computed(() => {
  const cards = data.value?.data?.cards ?? {}

  return Object.entries(cards).map(([key, value]) => ({
    key,
    value,
    title: key
      .replace(/_/g, ' ')
      .replace(/\b\w/g, char => char.toUpperCase()),
    icon: cardConfig[key]?.icon ?? 'fa fa-file',
    color: cardConfig[key]?.color ?? '#64748b',
  }))
})


const columns = computed(() => [
  { key: 'sl', label: t('shared.labels.sl') },
  { key: 'client_name', label: t('shared.labels.client_name') },
  { key: 'job_name', label: t('shared.labels.job_name') },
  { key: 'hiring_list', label: t('application.hiring_list') },
  { key: 'offer_extended', label: t('dashboard.pipeline.offer_extended') },
  { key: 'visa_authorization', label: t('dashboard.pipeline.visa_authorization') },
  { key: 'medical_test', label: t('dashboard.pipeline.medical_test') },
  { key: 'police_clearance', label: t('dashboard.pipeline.police_clearance') },
  { key: 'trade_test', label: t('dashboard.pipeline.trade_test') },
  { key: 'biometric_enrollment', label: t('dashboard.pipeline.biometric_enrollment') },
  { key: 'embassy_submission', label: t('dashboard.pipeline.embassy_submission') },
  { key: 'bmet_training', label: t('dashboard.pipeline.bmet_training') },
  { key: 'bmet_biometric_enrollment', label: t('dashboard.pipeline.bmet_biometric_enrollment') },
  { key: 'immigration_clearance', label: t('dashboard.pipeline.immigration_clearance') },
  { key: 'pta_request', label: t('dashboard.pipeline.pta_request') },
  { key: 'tra_process', label: t('dashboard.pipeline.tra_process') },
  { key: 'on_boarding', label: t('dashboard.pipeline.on_boarding') },
])
</script>
