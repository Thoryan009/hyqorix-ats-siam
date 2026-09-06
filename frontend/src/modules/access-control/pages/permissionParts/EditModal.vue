<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="t('permission.edit')"
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
import { useTranslate } from '@/shared/composables/useTranslate'
import CommonForm from './CommonForm.vue'
import { usePermissionMutations } from '@/modules/access-control/queries/usePermissionMutations'
import { usepermissionStore } from '@/modules/access-control/stores/permissionStore'

const { t } = useTranslate()
const store = usepermissionStore()

const formData = ref({
  id: '',
  name: '',
  slug: '',
})

watch(
  () => store.item,
  (item) => {
    if (item) {
      Object.assign(formData.value, {
        id: item.id,
        name: item.name || '',
        slug: item.slug || '',
      })
    }
  },
  { immediate: true }
)

const { update, updateLoading } = usePermissionMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
  },
  onError(error) {
    console.log('Custom error handling:', error)
  },
})

const handleSubmit = async () => {
  await update.mutateAsync(formData.value)
}
</script>
