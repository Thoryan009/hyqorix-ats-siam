<template>
  <ProcessForm
    v-if="selectedApplicant.processes.some((p) => p.process === 'trade_test')"
    :selectedApplicant="selectedApplicant"
    :processId="selectedApplicant.processes.find((p) => p.process === 'trade_test').process_id"
    :title="t('ats.process_5')"
    @updateProcess="$emit('updateProcess', $event)"
    @nextProcess="$emit('nextProcess', $event)"
  >
    <template #default="{ processData, editMode }">
      <div class="space-y-3">
        <!-- Trade Test Date -->
        <div>
          <BaseLabel>{{t('ats.trade_test_date')}}</BaseLabel>
          <BaseInput v-if="editMode" type="date" v-model="processData.data.trade_test_date" />
          <p v-else class="text-black text-[15px]">{{ processData.data.trade_test_date || '—' }}</p>
        </div>

        <!-- Name of the Center -->
        <div>
          <BaseLabel>{{t('ats.name_of_the_center')}}</BaseLabel>
          <BaseInput
            v-if="editMode"
            type="text"
            v-model="processData.data.name_of_the_center"
            :placeholder="t('ats.enter_test_center_name')"
          />
          <p v-else class="text-black text-[15px]">{{ processData.data.name_of_the_center || '—' }}</p>
        </div>

        <!-- Trade Test Status -->
        <div>
          <BaseLabel>{{t('ats.trade_test_result')}}</BaseLabel>
          <BaseSelect
            v-if="editMode"
            v-model="processData.data.status"
            :options="tradeTestStatusOptions"
            class="w-full"
            :placeholder="t('shared.placeholders.select')"
          />
          <p
            v-else
            class="text-black text-[15px] capitalize"
          >{{ tradeTestStatusOptions.find(o => o.id === processData.data.status)?.name || processData.data.status || '—' }}</p>
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

const tradeTestStatusOptions = [
  { name: 'Pass', id: 'pass' },
  { name: 'Fail', id: 'fail' },
  { name: 'Appointment', id: 'appointment' },
  { name: 'Not Required', id: 'not_required' },
]
</script>

<style scoped></style>
