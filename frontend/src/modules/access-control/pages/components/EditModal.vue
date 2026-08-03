
<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="t('role.edit')"
    @close="store.handleToggleModal"
    :className="'w-full xl:max-w-[50vw]'"
  >
    <CommonForm
      v-model:formData="formData"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="updateLoading"
    />
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import CommonForm from './CommonForm.vue'
import { useRoleMutations } from '@/modules/access-control/queries/useRoleMutations'
import { useRoleStore } from '@/modules/access-control/stores/RoleStore'
import {useTranslate} from '@/shared/composables/useTranslate'
const { t } = useTranslate()
const store = useRoleStore()

// Form state
const formData = ref({
  id: '',
  name: '',
})

// Populate form when store.item updates
watch(
  () => store.item,
  (item) => {
    if (item) {
      Object.assign(formData.value, item)
    }
  },
  { immediate: true }
)

// Mutation handler
const { update, updateLoading } = useRoleMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal() // close modal
  },
  onError(error) {
    console.log('Custom error handling:', error)
  },
})

// Submit
const handleSubmit = async () => {
  await update.mutateAsync(formData.value)
}
</script>
