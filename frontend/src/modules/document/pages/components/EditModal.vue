<template>
  <BaseModal :isVisible="store.isEditModal" :title="`Edit ${store.moduleName}`" @close="store.handleToggleModal">
    <CommonForm
      v-model:formData="formData"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="updateLoading"
      is-edit
    />
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import BaseModal from '@/shared/components/base/BaseModal.vue'
import CommonForm from './CommonForm.vue'
import { useDocumentMutations } from '../../queries/useDocumentMutations'
import { useDocumentStore } from '../../store/documentStore'

const store = useDocumentStore()

const formData = ref({
  name: '',
  category: '',
  document_file: null,
  document_preview: null,
})

watch(
  () => store.item,
  (item) => {
    if (!item) return
    formData.value = {
      name: item.name || '',
      category: item.category || '',
      document_file: null,
      document_preview: null,
    }
  },
  { immediate: true },
)

const { update, updateLoading } = useDocumentMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
  },
})

const handleSubmit = async () => {
  const payload = new FormData()

  for (const key in formData.value) {
    const value = formData.value[key]
    if (value === null || value === undefined || value === '') continue
    if (key.includes('_preview')) continue
    payload.append(key, value)
  }

  await update.mutateAsync({
    id: store.item.id,
    data: payload,
  })
}
</script>
