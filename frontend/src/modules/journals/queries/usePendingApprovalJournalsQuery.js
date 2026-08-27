import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchAll } from '../services/journalService'

export function usePendingApprovalJournalsQuery(enabledRef) {
  const enabled = computed(() => Boolean(unref(enabledRef)))

  return useQuery({
    queryKey: ['journals', 'pending_approval'],
    queryFn: () =>
      fetchAll(1, 100, {
        status: 'pending_approval',
      }),
    enabled,
    staleTime: 30 * 1000,
    gcTime: 60 * 60 * 1000,
    refetchOnWindowFocus: false,
  })
}
