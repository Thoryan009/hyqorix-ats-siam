import { computed } from "vue"

export function useCrudSubmit(store, api, formData) {
  const handleSubmit = async () => {
    const payload =
      typeof formData === "function"
        ? formData()
        : formData.value

    if (store.type === 'add') {
      await api.submit.mutateAsync(payload)
    } else {
      // ✅ FIX: pass id separately
      await api.update.mutateAsync({
        id: store.item?.id, // 🔥 or formData.value.id
        data: payload
      })
    }
  }

  const submitText = computed(() =>
    store.type === 'add' ? 'Save' : 'Update'
  )

  const submitSavingText = computed(() =>
    store.type === 'add' ? 'Saving...' : 'Updating...'
  )

  return {
    handleSubmit,
    submitLoading: api.submitLoading,
    updateLoading: api.updateLoading,
    submitText,
    submitSavingText,
  }
}
