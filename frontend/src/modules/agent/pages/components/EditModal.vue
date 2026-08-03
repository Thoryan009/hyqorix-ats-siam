<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="t('agent.edit')"
    @close="store.handleToggleModal"
  >
    <CommonForm
      v-model:formData="formData"
      :countries="countries"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="store.isLoading"
      :store="store"
    />
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useAgentMutations } from '@/modules/agent/queries/useAgentMutations'
import { useAgentStore } from '@/modules/agent/store/agentStore'
import CommonForm from './CommonForm.vue'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('agent')
const store = useAgentStore()

const formData = ref({
  id: '',
  name: '',
  email: '',
  agent_image_path: '',
  agent_image_preview: '',
  manager_name: '',
  nid_no: '',
  role_id: '',
  phone: '',
  phone2: '',
  stuff_name: '',
  stuff_phone: '',
  status: '1',
  address: '',
  password: '',
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

const { update } = useAgentMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal() // ✅ close modal
  },
  onError: (error) => {
    console.log('Custom error handling', error)
    store.isLoading = false
  },
})

// const handleSubmit = async () => {
//   store.isLoading = true
//   await update.mutateAsync(formData.value)
//   store.isLoading = false
// }

const handleSubmit = async () => {
  const payload = new FormData()

  for (const key in formData.value) {
    const value = formData.value[key]

    // Skip null, undefined, empty values
    if (value === null || value === undefined || value === '') {
      continue
    }

    // Skip preview fields (only append actual file)
    if (key.includes('_preview')) {
      continue
    }

    payload.append(key, value)
  }
  await update.mutateAsync({
    id: formData.value.id,
    data: payload,
  })

}
</script>
