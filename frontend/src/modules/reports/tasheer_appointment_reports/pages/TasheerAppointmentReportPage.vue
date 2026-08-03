<template>
  <SectionHeader>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <PageTitle>{{t('tasheer.title')}}</PageTitle>
        <p class="text-gray-600 text-sm pt-2" >{{t('tasheer.sub_title')}}</p>
      </div>
      <template v-if="selectedIds.length">
        <div class="flex flex-col gap-40 sm:flex-row sm:items-center">

          <div>
           <BaseButton v-can="'visa_processing.view_report'" @click="exportCsv">
              <i class="fa fa-table"></i> {{t('tasheer.export_csv')}} ({{ selectedIds.length }})
            </BaseButton>
          </div>
        </div>
      </template>
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
       <!-- Jobs FILTER -->
        <div class="flex flex-col">
          <label class="text-gray-800 text-[15px]">{{t('shared.labels.job')}}</label>
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
          <label class="text-gray-800 text-[15px]">{{t('shared.labels.agent')}}</label>
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

    <div className="flex justify-between items-center gap-2 mb-4">
            <div class="flex flex-col ml-7 ">
              <label class="text-gray-800 text-[15px]">{{t('shared.labels.status')}}</label>
              <BaseSelect v-model="filters.status" :options="statuses" placeholder="Select" />
            </div>

        <div v-if="selectedIds.length">
          <BaseButton v-can="'visa_processing.edit'"
          v-if="filters.status !== 'pending'"
            :className="'bg-yellow-600 text-white hover:bg-yellow-700 mr-5'"
            @click="handleBulkStatusUpdate('pending')"
            :disabled="bulkStatusUpdateLoading"
          >{{ t('tasheer.pending') }} ({{ selectedIds.length }})</BaseButton>

          <BaseButton v-can="'visa_processing.edit'"
            v-if="filters.status !== 'done'"
            :className="'bg-green-600 text-white hover:bg-green-700 mr-5'"
            @click="handleBulkStatusUpdate('done')"
            :disabled="bulkStatusUpdateLoading"
          >{{ t('tasheer.done') }} ({{ selectedIds.length }})</BaseButton>

          <BaseButton v-can="'visa_processing.edit'"
            v-if="filters.status !== 'cancel'"
            :className="'bg-red-500 text-white hover:bg-red-600'"
            @click="handleBulkStatusUpdate('cancel')"
            :disabled="bulkStatusUpdateLoading"
          >{{ t('tasheer.cancel') }} ({{ selectedIds.length }})</BaseButton>
      </div>



    </div>
    <div class="rounded-lg bg-white shadow-sm">
      <BaseTable
       selectable
       :columns="columns"
       :rows="rows"
      :selected-ids="selectedIds"
      @toggleAll="(checked) => toggleAll(rows, checked)"
      @toggleRow="toggleRow"

       />
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
import { computed } from 'vue'
import { usePagination } from '@/shared/composables/usePagination'
import PageTitle from '@/shared/components/ui/PageTitle.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import { useTasheerFilterDataQuery, useTasheerAppointmentReportQuery } from '../queries/useTasheerAppointmentReportQuery'
import { useTasheerAppointmentMutations } from '../queries/useTasheerAppointmentMutations'
import { exportTasheerAppointmentCsv } from '../services/tasheerAppointmentReportService'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import { useBulkDelete } from '@/shared/composables/useBulkDelete'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()

const today = new Date().toISOString().split('T')[0]
const { selectedIds, toggleAll, toggleRow } = useBulkDelete()

const {
  bulkStatusUpdateMutation,
  bulkStatusUpdateLoading,
} = useTasheerAppointmentMutations("Tasheer")

const handleBulkStatusUpdate = async (status) => {
  if (!selectedIds.value.length) return

  try {
    await bulkStatusUpdateMutation.mutateAsync({ ids: selectedIds.value, status })
     selectedIds.value = []
  } catch (err) {
    console.error('Bulk status update failed:', err)
  }
}

const { filters, resetFilters } = useTableFilters({
  status: 'pending',
  client_id: null,
  agent_id: null,
  job_id: null,
  from_date: null,
  to_date: null,
})

const hasActiveFilters = computed(() => {
  return (
    !!filters.value.process || filters.value.from_date !== today || filters.value.to_date !== today
  )
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination
const { data, isLoading } = useTasheerAppointmentReportQuery(page, perPage, filters)
pagination.bindMeta(data)

const { data: filterData } = useTasheerFilterDataQuery()

const clientData = computed(() => filterData.value?.data?.clients ?? [])
const agentData = computed(() => filterData.value?.data?.agents ?? [])
const jobData = computed(() => filterData.value?.data?.jobs ?? [])
const statuses = computed(() => filterData.value?.data?.statuses ?? [])
const rows = computed(() => data.value?.data?.data ?? [])

const filterByName = (option, query) => {
  return (option.name ?? '').toLowerCase().includes(query)
}

const filterJobList = (option, query) => {
  const jobName = (option.job_name ?? '').toLowerCase()
  const jobCode = (option.job_code ?? '').toLowerCase()
  const displayName = (option.name ?? '').toLowerCase()

  return jobName.includes(query) || jobCode.includes(query) || displayName.includes(query)
}

const columns = computed(() => [
  { key: 'sl', label: 'SL' },
  { key: 'e_no', label: t('tasheer.e_no') },
  { key: 'first_name', label: t('tasheer.first_name') },
  { key: 'second_name', label: t('tasheer.second_name') },
  { key: 'last_name', label: t('tasheer.last_name') },

  { key: 'passport_number', label: t('shared.labels.passport_no') },
  { key: 'date_of_birth', label: t('application.date_of_birth') },
  { key: 'nationality', label: t('application.nationality') },
  { key: 'date_of_issue', label: t('application.date_of_issue') },
  { key: 'gender', label: t('application.sex') },
  { key: 'place_of_issue', label: t('tasheer.place_of_issue') },
  { key: 'expiry_date', label: t('tasheer.expiry_date') },
  { key: 'applicant_mobile_no', label: t('tasheer.applicant_mobile_no') },
  { key: 'email_id', label: t('shared.labels.email') },
  // { key: 'job_name', label: t('tasheer.job_name') },
  // { key: 'agent_name', label: t('tasheer.agent_name') },
  // { key: 'client_name', label: t('tasheer.client_name') },
  { key: 'status', label: t('shared.labels.status') },
])
const exportCsv = async () => {
  try {
    filters.value.ids = selectedIds.value
    const blob = await exportTasheerAppointmentCsv(filters.value)
    const url = window.URL.createObjectURL(new Blob([blob]))
    const link = document.createElement('a')

    link.href = url
    link.setAttribute('download', 'tasheer appointment form.csv')

    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Tasheer CSV export failed:', error)
    alert('Failed to export CSV')
  }
}
</script>
