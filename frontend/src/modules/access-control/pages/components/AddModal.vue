
<template>
  <BaseModal
    :isVisible="store.isModal"
    :title="t('role.add')"
    @close="store.handleToggleModal"
    :className="'w-full xl:max-w-[50vw]'"
  >
    <CommonForm
      v-model:formData="formData"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="submitLoading"
    />
  </BaseModal>
</template>

<script setup>
import { ref } from 'vue'
import CommonForm from './CommonForm.vue'
import { useRoleMutations } from '@/modules/access-control/queries/useRoleMutations'
import app from '@/shared/config/appConfig'
import { useRoleStore } from '@/modules/access-control/stores/RoleStore'
import {useTranslate} from '@/shared/composables/useTranslate'
const { t } = useTranslate()

const store = useRoleStore()

// Default Form Data
const defaultFormData = {
  name: 'Test Name',
}

// Initialize formData
const formData = ref(
  app.moduleLocal
    ? { ...defaultFormData }
    : Object.fromEntries(Object.keys(defaultFormData).map((key) => [key, '']))
)

const { submit, submitLoading } = useRoleMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
    store.handleReset(formData.value)
  },
  onError(error) {
    console.log('Error:', error)
  },
})

// Submit handler
const handleSubmit = async () => {
  await submit.mutateAsync(formData.value)
}
</script>
