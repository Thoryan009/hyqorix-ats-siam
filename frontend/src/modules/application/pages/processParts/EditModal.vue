<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="t('process.edit_title')"
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
import { useProcessStore } from '../../store/processStore'
import { useProcessMutations } from '../../queries/useProcessMutations'
import { useProcessesQuery } from '../../queries/useProcessesQuery'

const { data } = useProcessesQuery()
const categories = computed(() => data.value?.data?.data ?? [])
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useProcessStore()

const formData = ref({
  id: '',
  name: '',
  duration: '',
  validity: '',
  notify_before: '',
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

const { update, updateLoading } = useProcessMutations(store.moduleName, {
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
