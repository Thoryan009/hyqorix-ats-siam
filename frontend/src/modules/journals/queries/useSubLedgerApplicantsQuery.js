import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchSubLedgerApplicants } from '../services/journalService'

export function useSubLedgerApplicantsQuery(jobListIdRef, enabledRef = true) {
  const jobListId = computed(() => {
    const value = Number(unref(jobListIdRef))
    return Number.isFinite(value) && value > 0 ? value : null
  })
  const enabled = computed(() => Boolean(unref(enabledRef)) && Boolean(jobListId.value))

  return useQuery({
    queryKey: computed(() => ['sub-ledger-applicants', jobListId.value]),
    queryFn: async () => {
      const result = await fetchSubLedgerApplicants(jobListId.value)
      const payload = result?.data?.data ?? result?.data ?? []
      return Array.isArray(payload) ? payload : []
    },
    enabled,
    staleTime: 60 * 1000,
    gcTime: 5 * 60 * 1000,
    refetchOnWindowFocus: false,
  })
}
