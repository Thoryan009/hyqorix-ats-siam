import { watch } from 'vue'

export function useSyncDynamicFilters(availableFilters, filters) {
  const sync = (dynamicFilters) => {
    if (!dynamicFilters?.length) return

    dynamicFilters.forEach(({ key }) => {
      if (!(key in filters.value)) {
        filters.value[key] = ''
      }
    })
  }

  watch(
    availableFilters,
    (newVal) => sync(newVal),
    { immediate: true }
  )

  return {
    sync,
  }
}
