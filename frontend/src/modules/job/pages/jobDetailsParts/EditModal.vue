<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="t('job_price.edit')"
    @close="store.handleToggleModal"
  >

    <CommonForm
      v-model:formData="formData"
      :fees="heads"
      :onSubmit="handleUpdate"
      @cancel="store.handleToggleModal"
      :loading="updateLoading"
    />
  </BaseModal>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import CommonForm from './CommonForm.vue'
import { useJobDetailsMutations } from '@/modules/job/queries/useJobDetailsMutations'
import { useJobDetailHeadsQuery } from '@/modules/job/queries/useJobDetailsQuery'
import { useJobDetailsStore } from '../../store/jobDetailsStore'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('job')
const store = useJobDetailsStore()
const { data } = useJobDetailHeadsQuery()
const heads = computed(() => data.value?.data?.data ?? [])

const formData = ref({
  job_list_details_head_id: '',
  amount: '',
  amount_usd: '',
  id: '',
  job_list_id: '',
})

watch(
  () => store.item,
  (newItem) => {
    if (!newItem) return

    Object.keys(formData.value).forEach((key) => {
      formData.value[key] = newItem[key] ?? formData.value[key]
    })
  },
  { immediate: true },
)

const { update, updateLoading } = useJobDetailsMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal() // ✅ close modal
  },
  onError: (error) => {
    console.log('Custom error handling', error)
  },
})

// update handler
const handleUpdate = async () => {
  const payload = {
    ...formData.value,
    job_list_details_head_id: Number(formData.value.job_list_details_head_id),
  }
  await update.mutateAsync(payload)
}
</script>
