import { useQuery } from '@tanstack/vue-query'
import { fetchAll, fetchAgentData } from '../services/agentService'
import { computed, unref } from 'vue'

/**
 * Agents list
 */
export function useAgentsQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const status = computed(() => unref(filtersRef)?.status)
  const user_id = computed(() => unref(filtersRef)?.user_id)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'agents'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      status.value ?? 'all',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchAll(page.value, perPage.value, {
        search: search.value,
        status: status.value,
        from_date: fromDate.value,
        to_date: toDate.value,
        user_id: user_id.value,
      }),

    enabled: computed(() => !search.value || search.value.length >= 3),

    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,
    keepPreviousData: true,

    meta: {
      persist: true,
    },
  })
}

export function useAgentDataQuery() {
  const cacheKey = 'agent-data'

  return useQuery({
    queryKey: computed(() => [cacheKey]),
    queryFn: () => fetchAgentData(),
    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}


