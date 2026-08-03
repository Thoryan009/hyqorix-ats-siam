import { ref } from 'vue'

export function useBulkDelete(removeMutation, options = {}) {
  const selectedIds = ref([])

  const toggleAll = (rows, checked) => {
    selectedIds.value = checked ? rows.map((r) => r.id) : []
  }

  const toggleRow = (id) => {
    selectedIds.value.includes(id)
      ? (selectedIds.value = selectedIds.value.filter((i) => i !== id))
      : selectedIds.value.push(id)
  }

  const clearSelection = () => {
    selectedIds.value = []
  }

  const bulkDelete = async () => {
    if (!selectedIds.value.length) return

    if (!confirm(options.confirmText || 'Are you sure to Delete selected records?')) return

    removeMutation.mutate(selectedIds.value, {
      onSuccess: () => {
        clearSelection()
        options.onSuccess?.()
      },
      onError: (error) => {
        options.onError?.(error)
      },
    })
  }

  return {
    selectedIds,
    toggleAll,
    toggleRow,
    bulkDelete,
    clearSelection,
  }
}
