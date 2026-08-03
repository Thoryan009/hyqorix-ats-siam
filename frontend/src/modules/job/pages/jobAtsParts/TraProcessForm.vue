<template>
  <ProcessForm
    v-if="selectedApplicant.processes.some((p) => p.process === 'tra_process')"
    :selectedApplicant="selectedApplicant"
    :processId="selectedApplicant.processes.find((p) => p.process === 'tra_process').process_id"
    :title="t('ats.process_12')"
    @updateProcess="$emit('updateProcess', $event)"
    @nextProcess="$emit('nextProcess', $event)"
  >
    <template #default="{ processData, editMode }">
      <div class="space-y-3">
        <!-- Ticket No -->
        <div>
          <BaseLabel>{{ t('ats.ticket_no') }}</BaseLabel>
          <BaseInput
            v-if="editMode"
            type="text"
            v-model="processData.data.ticket_no"
            :placeholder="t('ats.enter_ticket_number')"
          />
          <p v-else class="text-black text-[15px]">{{ processData.data.ticket_no || '—' }}</p>
        </div>

        <!-- Flight Number 1-->
        <div>
          <BaseLabel>{{ t('ats.flight_number_1') }}</BaseLabel>
          <BaseInput
            v-if="editMode"
            type="text"
            v-model="processData.data.flight_no_one"
            :placeholder="t('ats.enter_flight_number_1')"
          />
          <p v-else class="text-black text-[15px]">{{ processData.data.flight_no_one || '—' }}</p>
        </div>
        <!-- Flight Number 2-->
        <div>
          <BaseLabel>{{ t('ats.flight_number_2') }}</BaseLabel>
          <BaseInput
            v-if="editMode"
            type="text"
            v-model="processData.data.flight_no_two"
            :placeholder="t('ats.enter_flight_number_2')"
          />
          <p v-else class="text-black text-[15px]">{{ processData.data.flight_no_two || '—' }}</p>
        </div>
        <!-- Departure Date-->
        <div>
          <BaseLabel>{{ t('ats.departure_date') }}</BaseLabel>
          <BaseInput v-if="editMode" type="date" v-model="processData.data.flight_date" />
          <p v-else class="text-black text-[15px]">{{ processData.data.flight_date || '—' }}</p>
        </div>

        <div>
          <BaseLabel>{{ t('ats.flight_from_city') }}</BaseLabel>
          <BaseSelect
            v-if="editMode"
            v-model="processData.data.flight_from_city"
            :options="flightFromCityOptions"
            :placeholder="t('ats.select_city')"
          />
          <p v-else class="text-black text-[15px]">
            {{
              flightFromCityOptions.find((o) => o.id === processData.data.flight_from_city)?.name ||
              processData.data.flight_from_city ||
              '—'
            }}
          </p>
        </div>

        <!-- Departure Time-->
        <div>
          <BaseLabel>{{ t('ats.departure_time') }}</BaseLabel>
          <BaseInput
            v-if="editMode"
            type="text"
            v-model="processData.data.flight_time"
            :placeholder="t('ats.enter_departure_time')"
          />
          <p v-else class="text-black text-[15px]">{{ processData.data.flight_time || '—' }}</p>
        </div>

        <!-- Airline -->
        <div>
          <BaseLabel>{{ t('ats.airline') }}</BaseLabel>
          <BaseInput
            v-if="editMode"
            type="text"
            v-model="processData.data.airline"
            :placeholder="t('ats.enter_airline_name')"
          />
          <p v-else class="text-black text-[15px]">{{ processData.data.airline || '—' }}</p>
        </div>

        <!-- Final Destination  -->
        <div>
          <BaseLabel>{{ t('ats.final_destination') }}</BaseLabel>
          <BaseInput
            v-if="editMode"
            type="text"
            v-model="processData.data.landing_airport"
            :placeholder="t('ats.enter_final_destination')"
          />
          <p v-else class="text-black text-[15px]">{{ processData.data.landing_airport || '—' }}</p>
        </div>
        <!-- Landing Date  -->
        <div>
          <BaseLabel>{{ t('ats.landing_date') }}</BaseLabel>
          <BaseInput v-if="editMode" type="date" v-model="processData.data.landing_date" />
          <p v-else class="text-black text-[15px]">{{ processData.data.landing_date || '—' }}</p>
        </div>
        <!-- Landing Time -->
        <div>
          <BaseLabel>{{ t('ats.landing_time_local') }}</BaseLabel>
          <BaseInput
            v-if="editMode"
            type="text"
            v-model="processData.data.landing_time"
            :placeholder="t('ats.enter_landing_time')"
          />
          <p v-else class="text-black text-[15px]">{{ processData.data.landing_time || '—' }}</p>
        </div>
        <!-- Flight From (City) -->

        <!-- Print Acknowledgement -->
        <div class="my-2">
          <!-- <router-link
            :to="{
              path: '/acknowledgement-doc',
              query: {
                application_id: selectedApplicant.application_id,
                phone: selectedApplicant.mobile,
                name: selectedApplicant.full_name,
                passport_no: selectedApplicant.passport_no,
              },
            }"
            target="_blank"
            class="text-blue-600 underline font-semibold"
          >{{ t('ats.print_acknowledgement') }}</router-link> -->

          <!-- Print Acknowledgement -->
          <div class="my-2">
            <div
              className="text-blue-600 underline font-semibold"
              @click="goToPrint"
            >
              <i class="fa fa-print"></i>
              {{ t('ats.print_acknowledgement') }}
            </div>
          </div>
        </div>

        <!-- Ticket Passport -->
        <div>
          <BaseLabel>{{ t('ats.ticket_passport') }}</BaseLabel>
          <BaseSelect
            v-if="editMode"
            v-model="processData.data.ticket_passport"
            :options="ticketPassportOptions"
            :placeholder="t('ats.select_status')"
          />
          <p v-else class="text-black text-[15px]">
            {{
              ticketPassportOptions.find((o) => o.id === processData.data.ticket_passport)?.name ||
              processData.data.ticket_passport ||
              '—'
            }}
          </p>
        </div>
      </div>
    </template>
  </ProcessForm>
</template>

<script setup>
import ProcessForm from './ProcessForm.vue'
import { useTranslate } from '@/shared/composables/useTranslate.js'
import { useRouter } from 'vue-router'

const router = useRouter()
const { t } = useTranslate()

const props = defineProps({
  selectedApplicant: {
    type: Object,
    required: true,
  },
})
defineEmits(['updateProcess', 'nextProcess'])

// Select Options
const flightFromCityOptions = [
  { name: 'Dhaka', id: 'dhaka' },
  { name: 'Chattogram', id: 'chattogram' },
  { name: 'Sylhet', id: 'sylhet' },
]

const goToPrint = () => {
  const routeData = router.resolve({
    path: '/acknowledgement-doc',
    query: {
      application_id: props.selectedApplicant.application_id,
      phone: props.selectedApplicant.mobile,
      name: props.selectedApplicant.full_name,
      passport_no: props.selectedApplicant.passport_no,
    },
  })

  window.open(routeData.href, '_blank')
}
const ticketPassportOptions = [
  {
    name: 'Not Handover',
    id: '0',
  },
  {
    name: 'Handover',
    id: '1',
  },
]
</script>

<style scoped></style>
