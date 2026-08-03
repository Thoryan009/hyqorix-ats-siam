<template>
  <ProcessForm
    v-if="selectedApplicant.processes.some((p) => p.process === 'biometric_enrollment')"
    :selectedApplicant="selectedApplicant"
    :processId="
      selectedApplicant.processes.find((p) => p.process === 'biometric_enrollment').process_id
    "
    :title="t('ats.process_6')"
    @updateProcess="$emit('updateProcess', $event)"
    @nextProcess="$emit('nextProcess', $event)"
  >
    <template #default="{ processData, editMode }">
      <div class="space-y-3">
        <!-- Mofa Status -->
        <div>
          <BaseLabel>{{t('ats.mofa_status')}}</BaseLabel>
          <BaseSelect
            v-if="editMode"
            v-model="processData.data.mofa_status"
            :options="mofaStatusOptions"
            class="w-full"
            :placeholder="t('shared.placeholders.select')"
          />
          <p
            v-else
            class="text-black text-[15px] capitalize"
          >{{ mofaStatusOptions.find(o => o.id === processData.data.mofa_status)?.name || processData.data.mofa_status || '—' }}</p>
        </div>
        <!-- Date of Enrollment -->
        <div>
          <BaseLabel>{{t('ats.date_of_enrollment')}}</BaseLabel>
          <BaseInput v-if="editMode" type="date" v-model="processData.data.date_of_enrollment" />
          <p v-else class="text-black text-[15px]">{{ processData.data.date_of_enrollment || '—' }}</p>
        </div>

        <!-- Biometric Status -->
        <div>
          <BaseLabel>{{t('ats.biometric_status')}}</BaseLabel>
          <BaseSelect
            v-if="editMode"
            v-model="processData.data.status"
            :options="biometricStatusOptions"
            class="w-full"
            :placeholder="t('shared.placeholders.select')"
          />
          <p
            v-else
            class="text-black text-[15px] capitalize"
          >{{ biometricStatusOptions.find(o => o.id === processData.data.status)?.name || processData.data.status || '—' }}</p>
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
const biometricStatusOptions = [
  { name: 'Approved', id: 'approved' },
  { name: 'Rejected', id: 'rejected' },
  { name: 'Appointment', id: 'appointment' },
]
const mofaStatusOptions = [
  { name: 'Completed', id: 'completed' },
  { name: 'Not Completed', id: 'not_completed' },
]
</script>

<style scoped></style>
