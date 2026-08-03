<template>
  <ProcessForm
    v-if="selectedApplicant.processes.some((p) => p.process === 'visa_authorization')"
    :selectedApplicant="selectedApplicant"
    :processId="
      selectedApplicant.processes.find((p) => p.process === 'visa_authorization').process_id
    "
    :title="t('ats.process_2')"
    @updateProcess="$emit('updateProcess', $event)"
    @nextProcess="$emit('nextProcess', $event)"
  >
    <template #default="{ processData, editMode }">
      <div class="space-y-3">
        <div>
          <BaseLabel>{{t('ats.visa_number')}}</BaseLabel>
          <BaseInput v-if="editMode" type="number" v-model="processData.data.visa_no" />
          <p v-else class="text-black text-[15px]">{{ processData.data.visa_no || '—' }}</p>
        </div>

        <div>
          <BaseLabel>{{t('shared.labels.sponsor_id')}}</BaseLabel>
          <BaseInput v-if="editMode" type="number" v-model="processData.data.sponsor_id" />
          <p v-else class="text-black text-[15px]">{{ processData.data.sponsor_id || '—' }}</p>
        </div>

        <div>
          <BaseLabel>{{t('ats.date_of_issue')}}</BaseLabel>
          <BaseInput v-if="editMode" type="text" v-model="processData.data.date_of_issue" />
          <p v-else class="text-black text-[15px]">{{ processData.data.date_of_issue || '—' }}</p>
        </div>

        <div>
          <BaseLabel>{{t('ats.visa_profession')}}</BaseLabel>
          <BaseTextArea
            v-if="editMode"
            v-model="processData.data.visa_profession"
            :rows="2"
            :placeholder="t('ats.enter_you_visa_profession_info')"
          />
          <p v-else class="text-black text-[15px]">{{ processData.data.visa_profession || '—' }}</p>
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
