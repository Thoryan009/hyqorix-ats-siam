<template>
  <BaseModal
    :isVisible="store.isEditModal"
    :title="t('principal.edit')"
    @close="store.handleToggleModal"
    :className="'w-full xl:max-w-[60vw] h-[80vh] '"
  >
    <CommonForm
      v-model:formData="formData"
      :countries="countries"
      :users="users"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="store.isLoading"
    />
  </BaseModal>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { usePrincipalMutations } from '@/modules/principal/queries/usePrincipalMutations'
import { usePrincipalStore } from '@/modules/principal/store/principalStore'
import CommonForm from './CommonForm.vue'
import {useTranslate} from '@/shared/composables/useTranslate'

const {t} = useTranslate()
const props = defineProps({
  clientData: {
    type: Object,
    required: true,
  },
})
const countries = computed(() => props.clientData?.data?.data?.countries ?? [])
const users = computed(() => props.clientData?.data?.data?.users ?? [])

const store = usePrincipalStore()
const formData = ref({
  id: '',
  principal_id: '',
  organization_name: '',
  email: '',
  contact_no: '',
  role_id: '',
  status: '1',
  send_notification: 1,
  address: '',
  country_id: '',
  contact_person_name: '',
  designation: '',
  contact_person_no: '',
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

const { update } = usePrincipalMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal() // ✅ close modal
  },
  onError: (error) => {
    console.log('Custom error handling', error)
    store.isLoading = false
  },
})

const handleSubmit = async () => {
  store.isLoading = true
  await update.mutateAsync(formData.value)
  store.isLoading = false
}
</script>
