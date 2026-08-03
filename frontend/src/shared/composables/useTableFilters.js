import { ref, computed } from 'vue'

export function useTableFilters(initial = {}) {
  const filters = ref({ ...initial })

  const hasActiveFilters = computed(() =>
    Object.values(filters.value).some((v) => v !== null && v !== ''),
  )

  const resetFilters = () => {
    Object.keys(filters.value).forEach((key) => {
      filters.value[key] = initial[key] ?? null
    })
  }

  return {
    filters,
    hasActiveFilters,
    resetFilters,
  }
}
