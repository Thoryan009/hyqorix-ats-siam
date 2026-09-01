<template>
  <BaseModal :isVisible="store.isModal" :title="t('vendor.add')" @close="store.handleToggleModal">
    <CommonForm
      v-model:formData="formData"
      :userRoles="userRoles"
      :vendorTypes="vendorTypes"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="submitLoading"
      :store="store"
      :is-create="true"
    />
  </BaseModal>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useVendorStore } from '@/modules/vendor/store/vendorStore'
import { useVendorMutations } from '@/modules/vendor/queries/useVendorMutations'
import app from '@/shared/config/appConfig'
import CommonForm from './CommonForm.vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import { buildEmptyFormData, resetCreatePartyAccount } from '@/modules/parties/utils/createPartyAccountForm'

const { t } = useTranslate('vendor')
const store = useVendorStore()

const props = defineProps({
  vendorData: {
    type: Object,
    required: true,
  },
})

const userRoles = computed(() => props.vendorData?.data?.data?.roles ?? [])
const vendorTypes = computed(() => props.vendorData?.data?.data?.vendor_types ?? [])

const defaultFormData = {
  organization_name: 'Test Organization',
  vendor_type: 'ticket',
  contact_person: 'Test Contact',
  address: 'Dhaka, Bangladesh',
  email: 'vendor@example.com',
  phone: '+1234567890',
  whatsapp_no: '+1234567890',
  role_id: 1,
  send_notification: 1,
  vendor_image_path: null,
  vendor_image_preview: null,
  password: 'Test@1234',
  status: '1',
  create_party_account: 1,
}

const formData = ref(
  app.moduleLocal
    ? { ...defaultFormData }
    : buildEmptyFormData(defaultFormData)
)

watch(
  vendorTypes,
  (types) => {
    if (!formData.value.vendor_type?.trim() && types.length) {
      formData.value.vendor_type = types[0].id
    }
  },
  { immediate: true }
)

const { submit, submitLoading } = useVendorMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
    store.handleReset(formData.value)
    resetCreatePartyAccount(formData.value)
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

  await submit.mutateAsync(payload)
}
</script>
