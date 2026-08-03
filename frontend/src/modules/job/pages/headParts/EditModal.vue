<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="t('job_price.edit_fee_head')"
    @close="store.handleToggleModal"
  >
    <CommonForm
      v-model:formData="formData"
      :categories="categories"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="updateLoading"
    />
  </BaseModal>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import CommonForm from './CommonForm.vue'
import { useJobDetailsHeadCategoriesQuery } from '../../queries/useJobDetailsHeadQuery'
import { useJobDetailsHeadStore } from '../../store/jobDetailsHeadStore'
import { useJobDetailsHeadMutations } from '../../queries/useJobDetailsHeadMutations'
import { useTranslate } from '@/shared/composables/useTranslate'
const { t } = useTranslate()
const { data } = useJobDetailsHeadCategoriesQuery()

const categories = computed(() => data.value?.data?.data ?? [])

const store = useJobDetailsHeadStore()

const formData = ref({
  name: '',
  id: '',
  amount: '',
  amount_usd: '',
  job_list_details_category_id: '',
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

const { update, updateLoading } = useJobDetailsHeadMutations(store.moduleName, {
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
