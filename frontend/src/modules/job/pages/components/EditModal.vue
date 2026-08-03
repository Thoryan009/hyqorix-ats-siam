<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="t('job.edit')"
    @close="store.handleToggleModal"
    :className="'xl:max-w-[60vw]'"
  >
    <CommonForm
      v-if="store.isEditModal"
      :key="`edit-${store.item?.id ?? 'new'}`"
      v-model:formData="formData"
      :workOrders="workOrders"
      :principals="principals"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="updateLoading"
    />
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import CommonForm from './CommonForm.vue'
import { useJobStore } from '@/modules/job/store/jobStore'
import { useJobMutations } from '@/modules/job/queries/useJobMutations'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('job')
const store = useJobStore()
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

const formData = ref({
  id: '',
  vacancy: '',
  name: '',
  experience: '',
  price: '',
  client_commission_per_candidate: 0,
  min_age: '',
  max_age: '',
  contract_length: '',
  description: '',
  qualification: '',
  language: '',
  salary: '',
  deadline: '',
  status: '',
  interview_date: '',
  work_order_id: '',
  principal_id: '',
})

watch(
  () => store.item,
  (newItem) => {
    if (!newItem) return

    formData.value = {
      id: newItem.id ?? '',
      vacancy: newItem.vacancy ?? '',
      name: newItem.name ?? '',
      experience: newItem.experience ?? '',
      price: newItem.price ?? '',
      client_commission_per_candidate: newItem.client_commission_per_candidate ?? 0,
      min_age: newItem.min_age ?? '',
      max_age: newItem.max_age ?? '',
      contract_length: newItem.contract_length ?? '',
      description: newItem.description ?? '',
      qualification: newItem.qualification ?? '',
      language: newItem.language ?? '',
      salary: newItem.salary ?? '',
      deadline: newItem.deadline ?? '',
      status: newItem.status ?? '',
      interview_date: newItem.interview_date ?? '',
      work_order_id: newItem.work_order_id ?? '',
      principal_id: newItem.principal_id ?? '',
    }
  },
  { immediate: true },
)

const { update, updateLoading } = useJobMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal() // ✅ close modal
  },
  onError: (error) => {
    console.log('Custom error handling', error)
  },
})

const handleSubmit = async () => {
  await update.mutateAsync(formData.value)
}
</script>
