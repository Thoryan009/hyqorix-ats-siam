<template>
  <ProcessForm
    v-if="selectedApplicant.processes.some((p) => p.process === 'bmet_training')"
    :selectedApplicant="selectedApplicant"
    :processId="selectedApplicant.processes.find(p => p.process === 'bmet_training').process_id"
    :title="t('ats.process_8')"
    @updateProcess="$emit('updateProcess', $event)"
    @nextProcess="$emit('nextProcess', $event)"
  >
    <template #default="{ processData, editMode }">
      <div class="space-y-3">
        <!-- Training Start Date -->
        <div>
          <BaseLabel>{{t('ats.training_start_date')}}</BaseLabel>
          <BaseInput v-if="editMode" type="date" v-model="processData.data.training_start_date" />
          <p v-else class="text-black text-[15px]">{{ processData.data.training_start_date || '—' }}</p>
        </div>

        <!-- Finished Date -->
        <div>
          <BaseLabel>{{t('ats.finished_date')}}</BaseLabel>
          <BaseInput
            v-if="editMode"
            type="date"
            v-model="processData.data.finished_date"
            :placeholder="t('ats.enter_finished_date')"
          />
          <p v-else class="text-black text-[15px]">{{ processData.data.finished_date || '—' }}</p>
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
