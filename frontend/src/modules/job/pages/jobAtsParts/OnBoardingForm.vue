<template>
  <ProcessForm
    v-if="selectedApplicant.processes.some((p) => p.process === 'on_boarding')"
    :selectedApplicant="selectedApplicant"
    :processId="selectedApplicant.processes.find((p) => p.process === 'on_boarding').process_id"
    :title="t('ats.process_13')"
    @updateProcess="$emit('updateProcess', $event)"
    @nextProcess="$emit('nextProcess', $event)"
  >
    <template #default="{ processData, editMode }">
      <div class="space-y-3">
        <!-- Flight Status -->
        <div>
          <BaseLabel>{{ t('ats.select_flight_status') }}</BaseLabel>
          <BaseSelect
            v-if="editMode"
            v-model="processData.data.flight_status"
            :options="flightStatusOptions"
            class="w-full"
            :placeholder="t('ats.select_status')"
          />
          <p
            v-else
            class="text-black text-[15px] capitalize"
          >{{ flightStatusOptions.find(o => o.id === processData.data.flight_status)?.name || processData.data.flight_status || '—' }}</p>
        </div>

      </div>
    </template>
  </ProcessForm>
</template>

<script setup>
import ProcessForm from './ProcessForm.vue'
import { useTranslate } from '@/shared/composables/useTranslate.js'

const { t } = useTranslate()
defineProps({
  selectedApplicant: { type: Object, required: true },
})

defineEmits(['updateProcess', 'nextProcess'])

const flightStatusOptions = [
  { id: 'missed', name: 'Missed' },
  { id: 'departed', name: 'Departed' },
  { id: 'cancelled', name: 'Cancelled' },
]
</script>

<style scoped></style>
