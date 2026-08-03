<template>
  <ProcessForm
    v-if="selectedApplicant.processes.some((p) => p.process === 'offer_extended')"
    :selectedApplicant="selectedApplicant"
    :processId="selectedApplicant.processes.find((p) => p.process === 'offer_extended').process_id"
    :title="t('ats.process_1')"
    @updateProcess="$emit('updateProcess', $event)"
    @nextProcess="$emit('nextProcess', $event)"
    @isLastProcess="$emit('isLastProcess', $event)"
  >
    <template #default="{ processData, editMode }">
      <div>
        <BaseLabel>{{t('ats.offer_status')}}</BaseLabel>
        <BaseSelect
          v-if="editMode"
          v-model="processData.data.offer_status"
          :options="options"
          class="w-full"
        />
        <p
          v-else
          class="text-black text-[15px] capitalize"
        >{{ options.find(o => o.id === processData.data.offer_status)?.name || processData.data.offer_status || '—' }}</p>
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

const options = [
  { name: 'Select', id: '' },
  { name: 'Accepted', id: 'accepted' },
  { name: 'Rejected', id: 'rejected' },
]
</script>
