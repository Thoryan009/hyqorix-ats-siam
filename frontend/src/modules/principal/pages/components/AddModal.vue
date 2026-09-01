<template>
  <BaseModal
    :isVisible="store.isModal"
    :title="t('principal.add')"
    @close="store.handleToggleModal"
    :className="'w-full xl:max-w-[60vw] h-[80vh] '"
  >
    <!-- Use CommonForm here -->

    <CommonForm
      v-model:formData="formData"
      :countries="countries"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="store.isLoading"
      :is-create="true"
    />
  </BaseModal>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePrincipalStore } from '@/modules/principal/store/principalStore'

import app from '@/shared/config/appConfig'
import CommonForm from './CommonForm.vue'
import { usePrincipalMutations } from '../../queries/usePrincipalMutations'
import {useTranslate} from '@/shared/composables/useTranslate'
import { buildEmptyFormData, resetCreatePartyAccount } from '@/modules/parties/utils/createPartyAccountForm'

const {t} = useTranslate()
// Store
const store = usePrincipalStore()

const props = defineProps({
  clientData: {
    type: Object,
    required: true,
  },
})
const countries = computed(() => props.clientData?.data?.data?.countries ?? [])

// Default Form Data
const defaultFormData = {
  organization_name: 'Demo Organization',
  address: 'Demo Address',
  country_id: 1,
  contact_no: '1234567890',
  whatsapp_no: '1234567890',
  email: 'demo@example.com',
  contact_person_name: 'John Doe',
  designation: 'Manager',
  contact_person_no: '0987654321',
  role_id: 1,
  send_notification: 1,
  status: 1,
  password: 'Test@1234',
  create_party_account: 1,
}
// Initialize formData
const formData = ref(
  app.moduleLocal
    ? { ...defaultFormData }
    : buildEmptyFormData(defaultFormData),
)

// Mutation
const { submit } = usePrincipalMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
    store.handleReset(formData.value)
    resetCreatePartyAccount(formData.value)
  },
  onError: (error) => {
    console.log('Custom error handling', error)
    store.isLoading = false
  },
})

// Submit handler
const handleSubmit = async () => {
  store.isLoading = true
  await submit.mutateAsync(formData.value)
  store.isLoading = false
}
</script>
