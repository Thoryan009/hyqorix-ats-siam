import { ref, watch } from 'vue'
import app from '@/shared/config/appConfig'

export function useCrudForm(store, defaultFormData, excludeKeys = []) {
  const formData = ref({})

  const initForm = () => {
    if (store.type === 'edit' && store.item) {
      formData.value = deepCloneSerializable(store.item)
    } else if (app.moduleLocal) {
      formData.value = deepCloneSerializable(defaultFormData)
    } else {
      formData.value = Object.fromEntries(
        Object.keys(defaultFormData).map((key) => {
          if (excludeKeys.includes(key)) return [key, defaultFormData[key]]
          return [key, '']
        }),
      )
    }
  }
  const deepCloneSerializable = (obj) => {
    return JSON.parse(
      JSON.stringify(obj, (key, value) => {
        // remove non-serializable fields
        if (value instanceof File) return null
        return value
      }),
    )
  }

  watch(() => [store.type, store.item, store.isModal], initForm, { immediate: true })

  const resetForm = () => initForm()

  return {
    formData,
    resetForm,
  }
}
