import { useQuery } from '@tanstack/vue-query'
import { fetchEmbassyHints } from '../services/embassyService'
import { computed, unref } from 'vue'

export function useEmbassyHintsQuery(searchRef, options = {}) {
  const search = computed(() => unref(searchRef))
  const submitDate = computed(() => unref(options.submitDateRef))
  const excludeEmbassyListId = computed(() => unref(options.excludeEmbassyListIdRef))

  return useQuery({
    queryKey: computed(() => [
      'embassy-hints',
      search.value || 'all',
      submitDate.value || 'all',
      excludeEmbassyListId.value || 'none',
    ]),
    queryFn: () =>
      fetchEmbassyHints(search.value, {
        submitDate: submitDate.value,
        excludeEmbassyListId: excludeEmbassyListId.value,
      }),
    staleTime: 2 * 60 * 1000,
    cacheTime: 10 * 60 * 1000,
    keepPreviousData: true,
  })
}
