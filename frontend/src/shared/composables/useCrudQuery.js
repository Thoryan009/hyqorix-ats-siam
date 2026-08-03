import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchAll } from '@/shared/services/crudService'

export function useCrudQuery(module, pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const filters = computed(() => unref(filtersRef) || {})

  /* ---------------- Build query params dynamically ---------------- */
  const apiFilters = computed(() => {
    const raw = filters.value
    const result = {}

    for (const key in raw) {
      const value = raw[key]

      if (key === 'searchQuery') continue // 🚨 REMOVE IT HERE

      if (value === null || value === undefined || value === '') continue

      result[key] = value
    }

    return result
  })
  /* ---------------- Smart search handling ---------------- */
  const searchValue = computed(() => {
    const s = filters.value.searchQuery?.trim()
    return s && s.length >= 3 ? s : null
  })

  const query = useQuery({
    queryKey: computed(() => [
      module,
      page.value,
      perPage.value,
      JSON.stringify(apiFilters.value),
      searchValue.value ?? 'no-search',
    ]),

    queryFn: () =>
      fetchAll(module, page.value, perPage.value, {
        ...apiFilters.value,
        search: searchValue.value,
      }),

    enabled: computed(() => {
      const s = filters.value.searchQuery?.trim()
      return !s || s.length >= 3
    }),

    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,
    keepPreviousData: true,

    meta: {
      persist: true,
    },
  })

  const rows = computed(() => query.data.value?.data?.data ?? [])
  const extraData = computed(() => query.data.value?.data?.extra_data ?? {})
  const tableColumns = computed(() => query.data.value?.data?.table_meta?.columns ?? [])
  const availableFilters = computed(() => query.data.value?.data?.table_meta?.filters ?? [])
  const dashboardData = computed(() => query.data.value?.data?.table_meta?.dashboard ?? {})

  return {
    ...query,
    rows,
    extraData,
    tableColumns,
    availableFilters,
    dashboardData,
  }
}
