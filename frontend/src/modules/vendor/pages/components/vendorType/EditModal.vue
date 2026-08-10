<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="t('vendor.edit_type')"
    @close="store.handleToggleModal"
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
import { useVendorTypeMutations } from '@/modules/vendor/queries/useVendorTypeMutations'
import { useVendorTypeStore } from '@/modules/vendor/store/vendorTypeStore'
import CommonForm from './CommonForm.vue'

const { t } = useTranslate('vendor')
const store = useVendorTypeStore()

const formData = ref({
  id: '',
  name: '',
  status: 'active',
})

watch(
  () => store.item,
  (item) => {
    if (!item) return
    formData.value = {
      id: item.id,
      name: item.name,
      status: item.status || 'active',
    }
  },
  { immediate: true }
)

const { update, updateLoading } = useVendorTypeMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
  },
})

const handleSubmit = async () => {
  await update.mutateAsync(formData.value)
}
</script>
