<template>
  <BaseModal
    :isVisible="store.isModal"
    :title="t('client.add')"
    @close="store.handleToggleModal"
  >
    <!-- Use CommonForm here -->
    <CommonForm
      v-model:formData="formData"
      :countries="countries"
      :userRoles="userRoles"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="submitLoading"
      :store="store"
      :is-create="true"
    />
  </BaseModal>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useClientStore } from '@/modules/client/store/clientStore'
import { useClientMutations } from '@/modules/client/queries/useClientMutations'
// import { useClientDataQuery } from '@/modules/client/queries/useClientsQuery'
import app from '@/shared/config/appConfig'
import CommonForm from './CommonForm.vue'
import {useTranslate} from "@/shared/composables/useTranslate";

const { t } = useTranslate('client')
// Store
const store = useClientStore()

// Fetch Countries
// const { clientData } = useClientDataQuery()

const props = defineProps({
  clientData: {
    type: Object,
    required: true,
  },
})

const countries = computed(() => props.clientData?.data?.data?.countries ?? [])
const userRoles = computed(() => props.clientData?.data?.data?.roles ?? [])

// Default Form Data
const defaultFormData = {
  name: 'Test Name',
  email: 'test@example.com',
  phone: '+1234567890',
  whatsapp_no: '+1234567890',
  role_id: 1,
  country_id: 1,
  send_notification: 1,
  client_image_path: null,
  client_image_preview: null,
  password: 'Test@1234',
  status: '1',
  create_party_account: 1,
}


// Initialize formData
const formData = ref(
  app.moduleLocal
    ? { ...defaultFormData }
    : Object.fromEntries(
        Object.keys(defaultFormData)
          .filter((key) => key !== 'status') // remove status
          .map((key) => [key, ' ']), // set empty space
      ),
)

// Mutation
const { submit, submitLoading } = useClientMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
    store.handleReset(formData.value)
  },
  onError: (error) => {
    console.log('Custom error handling', error)
  },
})

// Submit handler
// const handleSubmit = async () => {
//   await submit.mutateAsync(formData.value)
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
  await submit.mutateAsync(payload)
}
</script>
