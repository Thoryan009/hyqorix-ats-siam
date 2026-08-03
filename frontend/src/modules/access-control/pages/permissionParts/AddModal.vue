<template>
  <BaseModal
    :isVisible="store.isModal"
    title="Add Permissions by Module"
    @close="closeModal"
    :className="'w-full xl:max-w-[50vw]'"
  >
    <CommonForm
      v-model:formData="formData"
      :onSubmit="handleSubmit"
      :onCancel="closeModal"
      :loading="generateModuleLoading"
    />
  </BaseModal>
</template>

<script setup>
import { ref } from 'vue'
import CommonForm from './ModulePermissionForm.vue'
import { usePermissionMutations } from '@/modules/access-control/queries/usePermissionMutations'
import { usepermissionStore } from '@/modules/access-control/stores/permissionStore'

const store = usepermissionStore()

const defaultFormData = {
  module: '',
  label: '',
  actions: ['create', 'edit', 'view', 'delete'],
  extraActions: '',
  assign_to_admin: true,
}

const formData = ref({ ...defaultFormData })

function resetForm() {
  formData.value = {
    ...defaultFormData,
    actions: [...defaultFormData.actions],
  }
}

function closeModal() {
  store.handleToggleModal()
  resetForm()
}

const { generateModule, generateModuleLoading } = usePermissionMutations(store.moduleName, {
  onSuccess() {
    closeModal()
  },
})

function normalizeToken(value) {
  return String(value || '')
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9_]+/g, '_')
    .replace(/^_+|_+$/g, '')
}

const handleSubmit = async () => {
  const module = normalizeToken(formData.value.module)
  const extras = String(formData.value.extraActions || '')
    .split(',')
    .map((item) => normalizeToken(item))
    .filter(Boolean)

  const actions = [...new Set([...(formData.value.actions || []), ...extras])]

  await generateModule.mutateAsync({
    module,
    label: formData.value.label?.trim() || undefined,
    actions,
    assign_to_admin: Boolean(formData.value.assign_to_admin),
  })
}
</script>
