<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="t('client.edit')"
    @close="store.handleToggleModal"
  >
    <CommonForm
      v-model:formData="formData"
      :countries="countries"
       :userRoles="userRoles"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="updateLoading"
      :store="store"
    />
  </BaseModal>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useClientMutations } from '@/modules/client/queries/useClientMutations'
import { useClientStore } from '@/modules/client/store/clientStore'
// import { useClientDataQuery } from '../../queries/useClientsQuery'
import CommonForm from './CommonForm.vue'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('client')
const props = defineProps({
  clientData: {
    type: Object,
    required: true,
  },
})
const countries = computed(() => props.clientData?.data?.data?.countries ?? [])
const userRoles = computed(() => props.clientData?.data?.data?.roles ?? [])


const store = useClientStore()
const formData = ref({
  id: '',
  name: '',
  email: '',
  client_image_path: '',
  client_image_preview: '',
  send_notification: 1,
  phone: '',
  whatsapp_no: '',
  role_id: '',
  status: '1',
  country_id: '',
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

const { update, updateLoading } = useClientMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
  },
  onError: (error) => {
    console.log('Custom error handling', error)
  },
})

// const handleSubmit = async () => {
//   await update.mutateAsync(formData.value)
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
