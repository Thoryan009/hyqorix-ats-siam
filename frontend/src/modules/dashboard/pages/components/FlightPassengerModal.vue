<template>
  <BaseModal
    :is-visible="isVisible"
    :title="modalTitle"
    class-name="xl:max-w-[70vw] w-full max-w-[95vw] sm:max-w-[90vw]"
    @close="$emit('close')"
  >
    <div v-if="flight" class="space-y-4">
      <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-sm">
        <div class="sm:col-span-2">
          <p class="text-gray-500 text-xs uppercase">Client(s)</p>
          <p class="font-medium text-black break-words">{{ clientNames }}</p>
        </div>
        <div>
          <p class="text-gray-500 text-xs uppercase">Flight Date</p>
          <p class="font-medium text-black">{{ flight.flight_date || '—' }}</p>
        </div>
        <div>
          <p class="text-gray-500 text-xs uppercase">Flight Time</p>
          <p class="font-medium text-black">{{ flight.flight_time || '—' }}</p>
        </div>
        <div>
          <p class="text-gray-500 text-xs uppercase">From (City)</p>
          <p class="font-medium text-black">{{ flight.from_city || '—' }}</p>
        </div>
        <div>
          <p class="text-gray-500 text-xs uppercase">Passengers</p>
          <p class="font-medium text-black">{{ flight.candidate_count ?? 0 }}</p>
        </div>
      </div>

      <BaseTable
        :columns="columns"
        :rows="passengerRows"
        :scrollable="true"
        :show-serial="true"
        thead-bg-color="bg-tertiary/20"
      >
        <template #cell-client="{ row }">
          <span class="font-medium" :title="hasMultipleClients ? row.client_full : undefined">
            {{ hasMultipleClients ? truncateText(row.client, 18) : row.client }}
          </span>
        </template>
      </BaseTable>
    </div>
  </BaseModal>
</template>

<script setup>
import { computed } from 'vue'
import BaseModal from '@/shared/components/base/BaseModal.vue'
import BaseTable from '@/shared/components/base/BaseTable.vue'
import { truncateText } from '../../utils/formatClients'

const props = defineProps({
  isVisible: {
    type: Boolean,
    default: false,
  },
  flight: {
    type: Object,
    default: null,
  },
})

defineEmits(['close'])

const modalTitle = computed(() => {
  const flightNo = props.flight?.flight_no
  return flightNo ? `Flight ${flightNo} - Passenger Details` : 'Passenger Details'
})

const clientNames = computed(() => {
  const clients = props.flight?.clients?.length
    ? props.flight.clients
    : props.flight?.client_full
      ? props.flight.client_full.split(', ')
      : []

  return clients.filter(Boolean).join(', ') || '—'
})

const hasMultipleClients = computed(() => {
  const clients = props.flight?.clients?.length
    ? props.flight.clients
    : props.flight?.client_full
      ? props.flight.client_full.split(', ')
      : []

  return clients.filter(Boolean).length > 1
})

const columns = [
  { key: 'sl', label: 'SL' },
  { key: 'name', label: 'Candidate Name' },
  { key: 'client', label: 'Client' },
  { key: 'passport_no', label: 'Passport No' },
  { key: 'application_id', label: 'Applicant ID' },
  { key: 'job_name', label: 'Job Name' },
  { key: 'demand_letter', label: 'Demand Letter' },
]

const passengerRows = computed(() =>
  (props.flight?.passengers || []).map((passenger) => ({
    name: passenger.name || '—',
    client: passenger.client || '—',
    client_full: passenger.client || '—',
    passport_no: passenger.passport_no || '—',
    application_id: passenger.application_id || '—',
    job_name: passenger.job_name || '—',
    demand_letter: passenger.demand_letter || '—',
  })),
)
</script>
