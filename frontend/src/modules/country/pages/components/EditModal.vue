<template>
  <BaseModal

    :isVisible="store.isEditModal"
    :title="t('country.edit')"
    @close="store.handleToggleModal"
  >
    <CommonForm
      v-model:formData="formData"
      :onSubmit="handleSubmit"
      :onCancel="store.handleToggleModal"
      :loading="store.isLoading"
       :store="store"
    />
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import CommonForm from './CommonForm.vue'
import { useCountryMutations } from '@/modules/country/queries/useCountryMutations'
import { useCountryStore } from '@/modules/country/store/countryStore'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useCountryStore()

// Form state
const formData = ref({
  id: '',
  name: '',
  country_image_path: '',
  country_image_preview: '',
})

// Populate form when store.item updates
watch(
  () => store.item,
  (item) => {
    if (item) {
      Object.assign(formData.value, item)
    }
  },
  { immediate: true },
)

// Mutation handler
const { update, updateLoading } = useCountryMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal() // close modal
  },
  onError(error) {
    console.log('Custom error handling:', error)
  },
})

// Submit
const handleSubmit = async () => {
  const payload = new FormData()

  for (const key in formData.value) {
    const value = formData.value[key]

    if (value === null || value === undefined || value === '') {
      continue
    }

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
