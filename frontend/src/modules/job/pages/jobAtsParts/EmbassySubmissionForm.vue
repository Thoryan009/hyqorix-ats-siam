<template>
  <ProcessForm
    v-if="selectedApplicant.processes.some((p) => p.process === 'embassy_submission')"
    :selectedApplicant="selectedApplicant"
    :processId="
      selectedApplicant.processes.find((p) => p.process === 'embassy_submission').process_id
    "
    :title="t('ats.process_7')"
    @updateProcess="$emit('updateProcess', $event)"
    @nextProcess="$emit('nextProcess', $event)"
  >
    <template #default="{ processData, editMode }">
      <div class="space-y-3">
        <!-- Date of Submission -->
        <div>
          <BaseLabel>{{t('ats.date_of_submission')}}</BaseLabel>
          <BaseInput v-if="editMode" type="date" v-model="processData.data.date_of_submission" />
          <p v-else class="text-black text-[15px]">{{ processData.data.date_of_submission || '—' }}</p>
        </div>

        <!-- Endorsement Date -->
        <div>
          <BaseLabel>{{t('ats.endorsement_date')}}</BaseLabel>
          <BaseInput v-if="editMode" type="date" v-model="processData.data.endorsment_date" />
          <p v-else class="text-black text-[15px]">{{ processData.data.endorsment_date || '—' }}</p>
        </div>
        <!-- Visa Expiry Date -->
        <div>
          <BaseLabel>{{t('ats.visa_expiry_date')}}</BaseLabel>
          <BaseInput v-if="editMode" type="date" v-model="processData.data.visa_expiry" />
          <p v-else class="text-black text-[15px]">{{ processData.data.visa_expiry || '—' }}</p>
        </div>

        <!-- Collection Date -->
        <div>
          <BaseLabel>{{t('ats.collection_date')}}</BaseLabel>
          <BaseInput v-if="editMode" type="date" v-model="processData.data.collection_date" />
          <p v-else class="text-black text-[15px]">{{ processData.data.collection_date || '—' }}</p>
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
