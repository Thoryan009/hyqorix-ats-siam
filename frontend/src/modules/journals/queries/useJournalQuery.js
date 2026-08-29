import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchJournal } from '../services/journalService'

export function useJournalQuery(idRef) {
  const id = computed(() => unref(idRef))

  return useQuery({
    queryKey: computed(() => ['journal', id.value]),
    queryFn: async () => {
      const result = await fetchJournal(id.value)
      return result.data?.data ?? result.data
    },
    enabled: computed(() => Boolean(id.value)),
    staleTime: 30 * 1000,
    refetchOnWindowFocus: false,
  })
}
