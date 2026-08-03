<template>
  <ProcessForm
    v-if="selectedApplicant.processes.some((p) => p.process === 'medical_test')"
    :selectedApplicant="selectedApplicant"
    :processId="selectedApplicant.processes.find(p => p.process === 'medical_test').process_id"
    :title="t('ats.process_3')"
    @updateProcess="$emit('updateProcess', $event)"
    @nextProcess="$emit('nextProcess', $event)"
  >
    <template #default="{ processData, editMode }">
      <div class="space-y-3">
        <!-- Date of Medical -->
        <div>
          <BaseLabel>{{t('ats.date_of_medical')}}</BaseLabel>
          <BaseInput v-if="editMode" type="date" v-model="processData.data.date_of_medical" />
          <p v-else class="text-black text-[15px]">{{ processData.data.date_of_medical || '—' }}</p>
        </div>

        <!-- Medical Center Name -->
        <div>
          <BaseLabel>{{t('ats.medical_center_name')}}</BaseLabel>
          <BaseInput
            v-if="editMode"
            type="text"
            v-model="processData.data.medical_center_name"
            :placeholder="t('ats.enter_medical_center_name')"
          />
          <p v-else class="text-black text-[15px]">{{ processData.data.medical_center_name || '—' }}</p>
        </div>

        <!-- Medical Fit Status -->
        <div>
          <BaseLabel>{{t('ats.medical_result')}}</BaseLabel>
          <BaseSelect
            v-if="editMode"
            v-model="processData.data.medical_fit"
            :options="medicalFitOptions"
            class="w-full"
            :placeholder="t('shared.placeholders.select')"
          />
          <p
            v-else
            class="text-black text-[15px] capitalize"
          >{{ medicalFitOptions.find(o => o.id === processData.data.medical_fit)?.name || processData.data.medical_fit || '—' }}</p>
        </div>

        <!-- Unfit Reason (only if unfit) -->
        <div v-if="processData.data.medical_fit === 'unfit'">
          <BaseLabel>{{ t('ats.unfit_reason') }}</BaseLabel>
          <BaseTextArea
            v-if="editMode"
            v-model="processData.data.unfit_reason"
            :rows="2"
            :placeholder="t('ats.enter_unfit_reason')"

          />
          <p v-else class="text-black text-[15px]">{{ processData.data.unfit_reason || '—' }}</p>
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

const medicalFitOptions = [
  { name: 'Fit', id: 'fit' },
  { name: 'Unfit', id: 'unfit' },
  { name: 'Meet', id: 'meet' },
]
</script>

<style scoped></style>
