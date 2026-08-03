<template>
  <ProcessForm
    v-if="selectedApplicant.processes.some((p) => p.process === 'bmet_biometric_enrollment')"
    :selectedApplicant="selectedApplicant"
    :processId="selectedApplicant.processes.find(p => p.process === 'bmet_biometric_enrollment').process_id"
    :title="t('ats.process_9')"
    @updateProcess="$emit('updateProcess', $event)"
    @nextProcess="$emit('nextProcess', $event)"
  >
    <template #default="{ processData, editMode }">
      <div class="space-y-3">
        <!-- Date of Enrollment -->
        <div>
          <BaseLabel>{{t('ats.date_of_enrollment')}}</BaseLabel>
          <BaseInput
            v-if="editMode"
            type="date"
            v-model="processData.data.date_of_enrollment"
            :placeholder="t('ats.enter_date_of_enrollment')"
          />
          <p v-else class="text-black text-[15px]">{{ processData.data.date_of_enrollment || '—' }}</p>
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
</script>

<style scoped></style>
