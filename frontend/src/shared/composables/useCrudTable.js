import { computed, unref } from 'vue'

export function useCrudTable(store, fields = [], options = {}) {
  const { timestamps = false, trackUser = false } = options

  const columns = computed(() => {
    const safeFields = Array.isArray(unref(fields)) ? unref(fields) : []

    const cols = [{ key: 'sl', label: 'SL' }, ...safeFields]

    if (timestamps) {
      cols.push(
        { key: 'created_at', label: 'Created At' },
        { key: 'updated_at', label: 'Updated At' },
      )
    }

    if (trackUser) {
      cols.push(
        { key: 'created_by', label: 'Created By' },
        { key: 'updated_by', label: 'Updated By' },
      )
    }

    return cols
  })

  const onView = (row) => store.handleToggleModal('view', row)
  const onEdit = (row) => store.handleToggleModal('edit', row)

  return {
    columns,
    onView,
    onEdit,
  }
}
