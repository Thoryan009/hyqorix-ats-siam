<template>
  <BaseModal :isVisible="store.isEditModal" :title="t('vendor.edit')" @close="store.handleToggleModal">
    <CommonForm
      v-model:formData="formData"
      :userRoles="userRoles"
      :vendorTypes="vendorTypes"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="updateLoading"
      :store="store"
    />
  </BaseModal>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useVendorMutations } from '@/modules/vendor/queries/useVendorMutations'
import { useVendorStore } from '@/modules/vendor/store/vendorStore'
import CommonForm from './CommonForm.vue'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('vendor')

const props = defineProps({
  vendorData: {
    type: Object,
    required: true,
  },
})

const store = useVendorStore()

const formData = ref({
  id: '',
  organization_name: '',
  vendor_type: '',
  contact_person: '',
  address: '',
  email: '',
  vendor_image_path: '',
  vendor_image_preview: '',
  send_notification: 1,
  phone: '',
  whatsapp_no: '',
  role_id: '',
  status: '1',
  password: '',
})

const userRoles = computed(() => props.vendorData?.data?.data?.roles ?? [])
const vendorTypes = computed(() => {
  const types = props.vendorData?.data?.data?.vendor_types ?? []
  const current = formData.value.vendor_type
  if (!current || types.some((type) => type.id === current)) {
    return types
  }

  return [
    ...types,
    {
      id: current,
      name: store.item?.vendor_type_formatted || current,
    },
  ]
})

watch(
  () => store.item,
  (newItem) => {
    if (!newItem) return
    Object.keys(formData.value).forEach((key) => {
      formData.value[key] = newItem[key] ?? formData.value[key]
    })
  },
  { immediate: true }
)

const { update, updateLoading } = useVendorMutations(store.moduleName, {
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
    id: formData.value.id,
    data: payload,
  })
}
</script>
