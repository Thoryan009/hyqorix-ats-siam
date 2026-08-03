<template>
  <ProcessForm
    v-if="selectedApplicant.processes.some((p) => p.process === 'police_clearance')"
    :selectedApplicant="selectedApplicant"
    :processId="selectedApplicant.processes.find(p => p.process === 'police_clearance').process_id"
    :title="t('ats.process_4')"
    @updateProcess="$emit('updateProcess', $event)"
    @nextProcess="$emit('nextProcess', $event)"
  >
    <template #default="{ processData, editMode }">
      <div class="space-y-3">
        <!-- Date of Apply -->
        <div>
          <BaseLabel>{{t('ats.date_of_apply')}}</BaseLabel>
          <BaseInput v-if="editMode" type="date" v-model="processData.data.date_of_apply" />
          <p v-else class="text-black text-[15px]">{{ processData.data.date_of_apply || '—' }}</p>
        </div>

        <!-- Police Station Name -->
        <div>
          <BaseLabel>{{t('ats.police_station_name')}}</BaseLabel>
          <BaseInput
            v-if="editMode"
            type="text"
            v-model="processData.data.ps_name"
            :placeholder="t('ats.enter_police_station_name')"
          />
          <p v-else class="text-black text-[15px]">{{ processData.data.ps_name || '—' }}</p>
        </div>

        <!-- Not Issued Reason -->
        <div>
          <BaseLabel>{{t('ats.not_issued_reason')}}</BaseLabel>
          <BaseTextArea
            v-if="editMode"
            v-model="processData.data.not_issued_reason"
            :rows="2"
            :placeholder="t('ats.enter_not_issued_reason')"
          />
          <p v-else class="text-black text-[15px]">{{ processData.data.not_issued_reason || '—' }}</p>
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
