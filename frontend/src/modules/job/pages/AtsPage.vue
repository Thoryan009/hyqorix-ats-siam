<template>
  <div>
    <!-- Page Title -->
    <div class="pt-2 pb-4">
      <PageTitle>{{ $t('ats.ats') }}</PageTitle>
    </div>
    <SectionHeader>
      <!-- Filters -->
      <div class="flex justify-start md:justify-end items-center my-1 mb-3">
        <TableFilters
          :filters="filters"
          :has-active-filters="hasActiveFilters"
          @reset="resetFilters"
        >
          <div v-if="authStore.userType != 'client'" class="flex flex-col sm:min-w-[200px]">
            <label class="text-gray-800 text-[15px]">{{ $t('shared.labels.client') }}</label>
            <BaseSearchSelect
              id="client_id"
              v-model="filters.client_id"
              :options="atsClients"
              :placeholder="$t('ats.search_by_client_name')"
              option-label="name"
              option-value="id"
              :filter-fn="filterByName"
            />
          </div>
        </TableFilters>
      </div>
      <!-- Loading -->
      <div v-if="isLoading" class="flex items-center justify-center py-20">
        <div
          class="h-12 w-12 animate-spin rounded-full border-4 border-primary border-t-transparent"
        ></div>
      </div>

      <!-- No Jobs -->
      <NoJobFound v-else-if="!jobs || jobs.length === 0" />

      <!-- Job Cards -->
      <div v-else class="space-y-4">
        <div
          v-for="job in jobs"
          :key="job.id"
          class="group overflow-hidden rounded-xl shadow-md transition-all duration-300 hover:shadow-xl border-b border-gray-100 bg-linear-to-r from-primary-light to-primary-gradient"
        >
          <div
            class="grid grid-cols-1 items-start gap-x-4 gap-y-3 px-3 py-2.5 lg:grid-cols-[minmax(0,1fr)_42rem]"
          >
            <router-link :to="`/jobs/${job.id}/ats`" class="min-w-0 overflow-hidden">
              <CardHeader
                :title="job.name"
                :subtitle="job.work_order"
                :meta="job.client"
                :jobcode="job.job_code"
              />
            </router-link>

            <div class="flex w-full flex-wrap items-center gap-2 lg:flex-nowrap">
              <AtsApplicationsBadge class="shrink-0" :count="job.applications_count" />

              <CardStat
                class="shrink-0"
                :label="t('shared.labels.vacancy')"
                :value="job.vacancy"
                value-class="text-sm text-purple-900"
              />

              <CardStat
                class="shrink-0"
                :label="t('shared.labels.salary')"
                :value="job.salary"
                value-class="text-sm text-green-900"
              />

              <div class="ml-auto flex shrink-0 gap-1.5">
                <BaseButton
                  @click="toggleDetails(job.id)"
                  :class="
                    expandedJobId === job.id
                      ? 'px-3 py-1.5 text-xs bg-gray-600! hover:bg-gray-700! text-white'
                      : 'px-3 py-1.5 text-xs bg-blue-600! hover:bg-blue-700! text-white'
                  "
                >
                  {{ expandedJobId === job.id ? t('shared.titles.hide') : t('shared.titles.details') }}
                </BaseButton>

                <router-link :to="`/jobs/${job.id}/ats`">
                  <BaseButton class="px-3 py-1.5 text-xs bg-green-500! hover:bg-green-600! text-white"
                    >{{t('ats.track')}}</BaseButton
                  >
                </router-link>
              </div>
            </div>
          </div>

          <!-- Animated Job Process -->
          <transition
            enter-active-class="transition-all duration-300 ease-out"
            leave-active-class="transition-all duration-200 ease-in"
            enter-from-class="max-h-0 opacity-0"
            enter-to-class="max-h-[500px] opacity-100"
            leave-from-class="max-h-[500px] opacity-100"
            leave-to-class="max-h-0 opacity-0"
          >
            <JobProcessTable
              v-if="expandedJobId === job.id"
              :process-counts="job.applications_process_count"
              :total-applications="job.applications_count"
            />
          </transition>
        </div>
      </div>

      <!-- Pagination -->
      <BasePagination
        v-if="!isLoading"
        :total="total"
        :showing="showing"
        :links="links"
        :per-page="perPage"
        @update:page="setPage"
        @update:perPage="setPerPage"
      />
    </SectionHeader>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import NoJobFound from './atsParts/NoJobFound.vue'
import CardHeader from './atsParts/CardHeader.vue'
import JobProcessTable from './atsParts/JobProcessTable.vue'
import CardStat from './atsParts/CardStat.vue'
import AtsApplicationsBadge from './atsParts/AtsApplicationsBadge.vue'
import { useAtsQuery, useAtsDataQuery } from '../queries/useAtsQuery'
import { usePagination } from '@/shared/composables/usePagination'
import { useTableFilters } from '@/shared/composables/useTableFilters'
import { useAtsStore } from '../store/atsStore'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import SectionHeader from '@/shared/components/ui/SectionHeader.vue'
import { useAuthStore } from '@/modules/auth/store/authStore'
import { useTranslate } from '@/shared/composables/useTranslate.js'

const { t } = useTranslate()

const store = useAtsStore()
const authStore = useAuthStore()

const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  client_id: '',
  from_date: null,
  to_date: null,
})
// Boolean to control collapse
const expandedJobId = ref(null)

const toggleDetails = (jobId) => {
  expandedJobId.value = expandedJobId.value === jobId ? null : jobId
}

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination

const { data, isLoading } = useAtsQuery(page, perPage, filters)
// const { data: workOrderData } = useAtsWorkOrdersQuery(filters)
// const { data: clientsData } = useAtsClientsQuery()
const { data: atsData } = useAtsDataQuery()

pagination.bindMeta(data)

const jobs = computed(() => data.value?.data?.data ?? [])
const atsClients = computed(() => atsData.value?.data?.data.clients ?? [])

const filterByName = (option, query) => {
  return (option.name ?? '').toLowerCase().includes(query)
}
</script>
