<template>
  <BaseModal
    :isVisible="store.isModal"
    :title="t('country.add')"
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
import CommonForm from './CommonForm.vue'
import app from '@/shared/config/appConfig'
import { useCountryMutations } from '@/modules/country/queries/useCountryMutations'
import { useCountryStore } from '@/modules/country/store/countryStore'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()

const store = useCountryStore()

// Default Form Data
const defaultFormData = {
  name: 'Test Name',
  country_image_path: null,
  country_image_preview: null,
}

// Initialize formData
const formData = ref(
  app.moduleLocal
    ? { ...defaultFormData }
    : Object.fromEntries(Object.keys(defaultFormData).map((key) => [key, ''])),
)

const { submit, submitLoading } = useCountryMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
    store.handleReset(formData.value)
  },
  onError(error) {
    console.log('Error:', error)
  },
})

// Submit handler
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
