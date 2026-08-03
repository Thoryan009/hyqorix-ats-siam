<template>
  <ProcessForm
    v-if="selectedApplicant.processes.some((p) => p.process === 'immigration_clearance')"
    :selectedApplicant="selectedApplicant"
    :processId="
      selectedApplicant.processes.find((p) => p.process === 'immigration_clearance').process_id
    "
    :title="t('ats.process_10')"
    @updateProcess="$emit('updateProcess', $event)"
    @nextProcess="$emit('nextProcess', $event)"
  >
    <template #default="{ processData, editMode }">
      <div class="space-y-3">
        <!-- Date of Submission -->
        <div>
          <BaseLabel>{{t('ats.date_of_submission')}}</BaseLabel>
          <BaseInput v-if="editMode" type="date" v-model="processData.data.date_of_submission" :placeholder="t('ats.enter_date_of_submission')" />
          <p v-else class="text-black text-[15px]">{{ processData.data.date_of_submission || '—' }}</p>
        </div>

        <!-- Collection Date -->
        <div>
          <BaseLabel>{{t('ats.collection_date')}}</BaseLabel>
          <BaseInput v-if="editMode" type="date" v-model="processData.data.collection_date" :placeholder="t('ats.enter_collection_date')" />
          <p v-else class="text-black text-[15px]">{{ processData.data.collection_date || '—' }}</p>
        </div>
        <div>
          <BaseLabel>{{t('ats.immigration_clearance_status')}}</BaseLabel>
          <BaseSelect
            v-if="editMode"
            v-model="processData.data.clearance_status"
            :options="immigrationClearanceOptions"
            class="w-full"
            :placeholder="t('shared.placeholders.select')"
          />
          <p
            v-else
            class="text-black text-[15px] capitalize"
          >{{ immigrationClearanceOptions.find(o => o.id === processData.data.clearance_status)?.name || processData.data.clearance_status || '—' }}</p>
        </div>
      </div>
    </template>
  </ProcessForm>
</template>

<script setup>
import ProcessForm from './ProcessForm.vue'
import { useTranslate } from '@/shared/composables/useTranslate.js'

const { t } = useTranslate()
const immigrationClearanceOptions = [
  { name: 'Awaiting', id: 'awaiting' },
  { name: 'Completed', id: 'completed' },
  { name: 'Under process', id: 'under_process' },
]

defineProps({
  selectedApplicant: { type: Object, required: true },
})

defineEmits(['updateProcess', 'nextProcess'])
</script>
