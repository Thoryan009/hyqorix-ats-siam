import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchJournalTransactionTypeOptions } from '../services/journalTransactionTypeService'

export function useJournalTransactionTypeOptionsQuery(statusRef = 'active', enabledRef = true) {
  const status = computed(() => unref(statusRef) || 'active')
  const enabled = computed(() => Boolean(unref(enabledRef)))

  return useQuery({
    queryKey: computed(() => ['journal-transaction-type-options', status.value]),
    queryFn: async () => {
      const result = await fetchJournalTransactionTypeOptions(status.value)
      const payload = result?.data?.data ?? result?.data ?? []
      return Array.isArray(payload) ? payload : []
    },
    enabled,
    staleTime: 5 * 60 * 1000,
    gcTime: 60 * 60 * 1000,
    refetchOnWindowFocus: false,
  })
}
