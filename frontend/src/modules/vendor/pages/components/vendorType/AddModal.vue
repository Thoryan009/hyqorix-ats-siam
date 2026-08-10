<template>
  <BaseModal
    :isVisible="store.isModal"
    :title="t('vendor.add_type')"
    @close="store.handleToggleModal"
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
import app from '@/shared/config/appConfig'
import { useTranslate } from '@/shared/composables/useTranslate'
import { useVendorTypeMutations } from '@/modules/vendor/queries/useVendorTypeMutations'
import { useVendorTypeStore } from '@/modules/vendor/store/vendorTypeStore'
import CommonForm from './CommonForm.vue'

const { t } = useTranslate('vendor')
const store = useVendorTypeStore()

const defaultFormData = {
  name: 'Ticket',
  status: 'active',
}

const formData = ref(
  app.moduleLocal
    ? { ...defaultFormData }
    : Object.fromEntries(Object.keys(defaultFormData).map((key) => [key, key === 'status' ? 'active' : '']))
)

const { submit, submitLoading } = useVendorTypeMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
    store.handleReset(formData.value)
  },
})

const handleSubmit = async () => {
  await submit.mutateAsync(formData.value)
}
</script>
