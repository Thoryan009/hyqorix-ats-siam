<template>
  <div class="w-full">
    <div class="flex items-center gap-5 mb-4">
      <DashboardSectionHeading
      :title="t('dashboard.flight_summary_title')"
      icon="fa fa-plane"
      icon-class="bg-linear-to-br from-tertiary to-tertiary/70"
    />
      <router-link
        :to="{ path: '/dashboard/flight-summary' }"
        class="text-sm mb-2 font-semibold text-primary hover:text-primary/80  disabled:opacity-60 disabled:cursor-not-allowed"
      >
       <button>{{ t('dashboard.see_all') }}</button>
      </router-link>
    </div>
<!-- <pre>{{ flightSummary }}</pre> -->

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
        <p class="text-sm">{{ t('dashboard.no_flight_data') }}</p>
      </div>

      <div

        class="px-4 py-3 border-t border-gray-100 flex justify-center"
      >
        <button
        v-if="showSeeMore"

          type="button"
          class="text-sm font-semibold text-primary hover:text-primary/80 disabled:opacity-60 disabled:cursor-not-allowed"
          :disabled="isLoading"
          @click="loadAllFlights"
        >
          {{ isLoading ? t('dashboard.loading') : t('dashboard.see_more', { count: remainingCount }) }}
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
import DashboardSectionHeading from '../../components/DashboardSectionHeading.vue'
import FlightPassengerModal from './FlightPassengerModal.vue'
import { formatClientsSummary } from '../../utils/formatClients'
import router from '@/router/index.js'
import { fetchFlightSummary } from '../../services/DashboardService'
import { useTranslate } from '@/shared/composables/useTranslate'
const { t } = useTranslate()
const props = defineProps({
  flightSummary: {
    type: Object,
    default: () => ({
      data: [],
      total: 0,
      has_more: false,
    }),
  },
})

const isModalOpen = ref(false)
const selectedFlight = ref(null)
const isLoading = ref(false)
const isExpanded = ref(false)
const flights = ref([])

const columns = [
  { key: 'flight_no', label: 'Flight No' },
  { key: 'client', label: 'Client' },
  { key: 'candidate_count', label: 'Candidates' },
  { key: 'country', label: 'Country' },
  { key: 'flight_date', label: 'Departure Date' },
  { key: 'flight_time', label: 'Departure Time' },
  { key: 'from_city', label: 'From (City)' },
  { key: 'landing_airport', label: 'Final Destination' },
  { key: 'landing_time', label: 'Landing Time (Local)' },
]

watch(
  () => props.flightSummary,
  (summary) => {
    if (!isExpanded.value) {
      flights.value = summary?.data || []
    }
  },
  { immediate: true, deep: true },
)

const totalFlights = computed(() => props.flightSummary?.total ?? flights.value.length)
const showSeeMore = computed(() => !isExpanded.value && props.flightSummary?.has_more)
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

async function loadAllFlights() {
  isLoading.value = true

  try {
    const result = await fetchFlightSummary()
    flights.value = result?.data?.data?.data || []
    isExpanded.value = true
  } finally {
    isLoading.value = false
  }
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
