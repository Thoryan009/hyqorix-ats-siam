import { useMutation } from '@tanstack/vue-query'
import { fetchNarrationHints } from '../services/journalService'

export function useJournalNarrationHints() {
  return useMutation({
    mutationFn: fetchNarrationHints,
  })
}
