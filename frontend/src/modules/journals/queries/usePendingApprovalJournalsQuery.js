import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchAll } from '../services/journalService'

export function useJournalsByStatusQuery(statusRef, enabledRef) {
  const status = computed(() => unref(statusRef) || '')
  const enabled = computed(() => Boolean(unref(enabledRef)) && Boolean(status.value))

  return useQuery({
    queryKey: computed(() => ['journals', status.value || 'none']),
    queryFn: () =>
      fetchAll(1, 100, {
        status: status.value,
      }),
    enabled,
    staleTime: 30 * 1000,
    gcTime: 60 * 60 * 1000,
    refetchOnWindowFocus: false,
  })
}

export function usePendingApprovalJournalsQuery(enabledRef) {
  return useJournalsByStatusQuery('pending_approval', enabledRef)
}

export function useApprovedJournalsQuery(enabledRef) {
  return useJournalsByStatusQuery('approved', enabledRef)
}

export function useReturnedJournalsQuery(enabledRef) {
  return useJournalsByStatusQuery('returned', enabledRef)
}
