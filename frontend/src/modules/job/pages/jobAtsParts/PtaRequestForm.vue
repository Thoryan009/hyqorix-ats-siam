<template>
  <ProcessForm
    v-if="selectedApplicant.processes.some((p) => p.process === 'pta_request')"
    :selectedApplicant="selectedApplicant"
    :processId="selectedApplicant.processes.find((p) => p.process === 'pta_request').process_id"
    :title="t('ats.process_11')"
    @updateProcess="$emit('updateProcess', $event)"
    @nextProcess="$emit('nextProcess', $event)"
  >
    <template #default="{ processData, editMode }">
      <div class="space-y-3">
        <!-- Pta Request Date -->
        <div>
          <BaseLabel>{{ t('ats.pta_request_date') }}</BaseLabel>
          <BaseInput v-if="editMode" type="date" v-model="processData.data.pta_request_date" />
          <p v-else class="text-black text-[15px]">{{ processData.data.pta_request_date || '—' }}</p>
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
