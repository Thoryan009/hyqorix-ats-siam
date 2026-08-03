<template>
  <div class="w-full">
    <DashboardSectionHeading
      :title="t('dashboard.flight_summary.title')"

    />
     <div v-can="'flight_summary.export'" class="flex justify-end gap-2">
          <BaseButton @click="goToPrint"           className="bg-blue-500 text-white hover:bg-blue-600 cursor-pointer"
            >{{ t('dashboard.flight_summary.print_report') }}</BaseButton>
            <!-- Export CSV -->
          <!-- <BaseButton @click="exportCSV"> <i class="fa fa-table"></i> {{ t('dashboard.flight_summary.export_csv') }}</BaseButton> -->
        </div>
     <div class="flex justify-start md:justify-between items-center my-4">
          <TableFilters :from-date="false" :to-date="false" :filters="filters" :has-active-filters="hasActiveFilters" @reset="resetFilters">
              <!-- FROM DATE -->
              <div class="flex flex-col w-full sm:w-auto sm:min-w-[150px]">
                <label class="text-gray-800 text-sm sm:text-[15px] mb-1">
                {{ t('dashboard.flight_summary.departure_from') }}
                </label>
                <input type="date" v-model="filters.departure_from" class="border border-gray-300 rounded-md px-3 py-2 w-full" />
              </div>

              <!-- TO DATE -->
              <div class="flex flex-col w-full sm:w-auto sm:min-w-[150px]">
                <label class="text-gray-800 text-sm sm:text-[15px] mb-1">
                {{ t('dashboard.flight_summary.departure_to') }}
                </label>

                <input type="date" v-model="filters.departure_to" class="border border-gray-300 rounded-md px-3 py-2 w-full" />
              </div>

              <div  class="flex flex-col sm:min-w-[200px]">
                <label class="text-gray-800 text-[15px]">{{ t('dashboard.flight_summary.country') }}</label>
                <BaseSearchSelect
                  v-model="filters.country_id"
                  :options="countryOptions"
                  :placeholder="t('dashboard.flight_summary.search_country')"
                  option-label="name"
                  option-value="id"
                  :filter-fn="filterByCountryName"
                />
              </div>
            <div  class="flex flex-col sm:min-w-[200px]">
                <label class="text-gray-800 text-[15px]">{{ t('dashboard.flight_summary.client') }}</label>
                <BaseSearchSelect
                  v-model="filters.client_id"
                  :options="clientOptions"
                  :placeholder="t('dashboard.flight_summary.search_client')"
                  option-label="name"
                  option-value="id"
                  :filter-fn="filterByClientName"
                />
              </div>
      </TableFilters>

      </div>

    <div
      class="bg-white backdrop-blur-lg rounded-2xl shadow-xl border border-gray-200 hover:shadow-2xl transition-shadow duration-300 overflow-hidden"
    >
      <div v-if="rows.length" class="overflow-x-auto">
        <BaseTable
          :columns="columns"
          :rows="rows"
          :scrollable="true"
          :show-serial="false"
          :cursor-pointer="true"
          thead-bg-color="bg-tertiary/20"
          @row-click="openPassengerModal"
        >
          <template #cell-client="{ row }">
            <span class="font-medium" :title="row.client_full">{{ row.client }}</span>
          </template>
        </BaseTable>
      </div>

      <div v-else class="px-6 py-10 text-center text-gray-500">
        <i class="fa fa-plane text-3xl text-gray-300 mb-3"></i>
        <p class="text-sm">{{ t('dashboard.flight_summary.no_data') }}</p>
      </div>

      <div
        v-if="showSeeMore"
        class="px-4 py-3 border-t border-gray-100 flex justify-center"
      >
        <button
          type="button"
          class="text-sm font-semibold text-primary hover:text-primary/80 disabled:opacity-60 disabled:cursor-not-allowed"
          :disabled="isLoading"
          @click="loadAllFlights"
        >
          {{ isLoading ? t('dashboard.flight_summary.loading') : t('dashboard.flight_summary.see_more', { count: remainingCount }) }}
        </button>
      </div>
    </div>

    <FlightPassengerModal
      :is-visible="isModalOpen"
      :flight="selectedFlight"
      @close="closePassengerModal"
    />
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import BaseTable from '@/shared/components/base/BaseTable.vue'
// import DashboardSectionHeading from '../../components/DashboardSectionHeading.vue'
import DashboardSectionHeading from '../components/DashboardSectionHeading.vue'
import FlightPassengerModal from './components/FlightPassengerModal.vue'
import { formatClientsSummary } from '../utils/formatClients'
import { useFlightSummaryQuery } from '../queries/useDashboardQuery'
import TableFilters from '@/shared/components/ui/TableFilters.vue'
import { useTableFilters } from '@/shared/composables/useTableFilters.js'
import { usePagination } from '@/shared/composables/usePagination.js'
import { removeEmptyKeys } from '@/shared/helpers/objectHelper.js'
import { useRoute, useRouter } from 'vue-router'
import { useTranslate } from '@/shared/composables/useTranslate.js'
const { t } = useTranslate()
const route = useRoute()
const router = useRouter()
const reportType = computed(() => route.params.type)
const { filters, hasActiveFilters, resetFilters } = useTableFilters({
  searchQuery: '',
  country_id: null,
  client_id: null,
  departure_from: null,
  departure_to: null,
})

const pagination = usePagination()
const { page, perPage, total, showing, links, setPage, setPerPage } = pagination


const data = useFlightSummaryQuery(page, perPage, filters)

const flights = computed(() => data.data.value?.data?.data.data ?? [])
const countryOptions = computed(() => data.data.value?.data?.data?.countries ?? [])
const clientOptions = computed(() => data.data.value?.data?.data?.clients ?? [])


const isModalOpen = ref(false)
const selectedFlight = ref(null)
const isLoading = ref(false)
const isExpanded = ref(false)

const columns = computed(() => [
  { key: 'flight_no', label: t('dashboard.flight_summary.columns.flight_no') },
  { key: 'client', label: t('dashboard.flight_summary.columns.client') },
  { key: 'candidate_count', label: t('dashboard.flight_summary.columns.candidate_count') },
  { key: 'country', label: t('dashboard.flight_summary.columns.country') },
  { key: 'flight_date', label: t('dashboard.flight_summary.columns.flight_date') },
  { key: 'flight_time', label: t('dashboard.flight_summary.columns.flight_time') },
  { key: 'from_city', label: t('dashboard.flight_summary.columns.from_city') },
  { key: 'landing_airport', label: t('dashboard.flight_summary.columns.landing_airport') },
  { key: 'landing_time', label: t('dashboard.flight_summary.columns.landing_time') },
])

watch(
  () => flights.value,
  (summary) => {
    if (!isExpanded.value) {
      flights.value = summary?.data || []
    }
  },
  { immediate: true, deep: true },
)

const totalFlights = computed(() => flights.value?.total ?? flights.value.length)
const showSeeMore = computed(() => !isExpanded.value && flights.value?.has_more)
const remainingCount = computed(() => Math.max(totalFlights.value - flights.value.length, 0))

const rows = computed(() =>
  flights.value.map((item) => {
    const clients = item.clients?.length ? item.clients : item.client ? [item.client] : []
    const { display, full } = formatClientsSummary(clients)

    return {
      flight_no: item.flight_no || '—',
      clients,
      client: display,
      client_full: full,
      candidate_count: item.candidate_count ?? 0,
      country: item.country || '—',
      flight_date: formatDate(item.flight_date),
      flight_time: item.flight_time || '—',
      from_city: item.from_city || '—',
      landing_airport: item.landing_airport || '—',
      landing_time: item.landing_time || '—',
      passengers: item.passengers || [],
    }
  }),
)

const goToPrint = () => {
  const filtered = removeEmptyKeys(filters.value)

  filtered.countryName = filtered.country_id ? getCountryNameById(filtered.country_id) : ''
  filtered.clientName = filtered.client_id ? getClientNameById(filtered.client_id) : ''


  // ADD DATES
  filtered.from_date = filtered.from_date ? filtered.from_date : ''

  filtered.to_date = filtered.to_date ? filtered.to_date : ''

  const filteredFinal = removeEmptyKeys(filtered)

  // create route URL
  const routeData = router.resolve({
    name: 'Flight Summary Print',
    params: {
      type: 'Nai',
    },
    query: filteredFinal,
  })

  console.log(routeData)

  // open new tab
  window.open(routeData.href, '_blank')
}

const getCountryNameById = (countryId) => {
  return countryOptions.value.find((country) => country.id == countryId).name
}

const getClientNameById = (clientId) => {
  return clientOptions.value.find((client) => client.id == clientId).name
}



function formatDate(value) {
  if (!value) return '—'

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value

  return date.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}


function openPassengerModal(row) {
  selectedFlight.value = row
  isModalOpen.value = true
}

function closePassengerModal() {
  isModalOpen.value = false
  selectedFlight.value = null
}
</script>
