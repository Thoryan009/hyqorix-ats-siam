<template>
  <BaseModal
    :isVisible="store.isModal"
    :title="t('job.add')"
    @close="store.handleToggleModal"
    :className="'xl:max-w-[60vw]'"
  >
    <CommonForm
      v-if="store.isModal"
      :key="`add-${store.type}`"
      v-model:formData="formData"
      :workOrders="workOrders"
      :principals="principals"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="submitLoading"
    />
  </BaseModal>
</template>

<script setup>
import { ref } from 'vue'
import { useJobStore } from '@/modules/job/store/jobStore'
import { useJobMutations } from '@/modules/job/queries/useJobMutations'
import app from '@/shared/config/appConfig'
import CommonForm from './CommonForm.vue'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('job')
// Get Work Orders

defineProps({
  workOrders: {
    type: Array,
    default: () => [],
  },
  principals: {
    type: Array,
    default: () => [],
  },
})

// Store
const store = useJobStore()

// from now to 45 Days later
const jobDeadLine = new Date()
jobDeadLine.setDate(jobDeadLine.getDate() + 45)

// Default Form Data
const defaultFormData = {
  name: 'Factory Worker',
  vacancy: 10,
  experience: 'No experience required',
  price: 1200,
  client_commission_per_candidate: 0,
  min_age: 20,
  max_age: 35,
  contract_length: '2 Years',
  description:
    'General factory helper duties including packing, sorting, machine assistance, loading, unloading and maintaining basic cleanliness.',
  qualification: 'SSC Pass',
  language: 'Basic English',
  salary: '1200 AED',
  deadline: jobDeadLine.toISOString().split('T')[0],
  interview_date: '2026-03-20',
  status: 'open',
  work_order_id: 1,
  principal_id: 1,
}

const formData = ref(
  app.moduleLocal
    ? defaultFormData
    : Object.fromEntries(
        Object.keys(defaultFormData).map((key) => [
          key,
          key === 'status'
            ? defaultFormData.status
            : key === 'language'
              ? defaultFormData.language
              : key === 'deadline'
                ? defaultFormData.deadline
                : '',
        ]),
      ),
)
// Mutation submit
const { submit, submitLoading } = useJobMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
    store.handleReset(formData.value)
  },
})

// Submit
const handleSubmit = async () => {
  await submit.mutateAsync(formData.value)
}
</script>
