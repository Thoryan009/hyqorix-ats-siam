import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchDemandLetterOptions, fetchJobOptions } from '../services/journalService'
import {
  formatDemandLetterSelectOptions,
  formatJobSelectOptions,
} from '../data/postJournalStatic'

export function useJobOptionsQuery(enabledRef = true) {
  const enabled = computed(() => Boolean(unref(enabledRef)))

  return useQuery({
    queryKey: ['journal-job-options'],
    queryFn: async () => {
      const result = await fetchJobOptions()
      const payload = result?.data?.data ?? result?.data ?? []
      return Array.isArray(payload) ? payload : []
    },
    enabled,
    staleTime: 5 * 60 * 1000,
    gcTime: 60 * 60 * 1000,
    refetchOnWindowFocus: false,
  })
}

export function useDemandLetterOptionsQuery(enabledRef = true) {
  const enabled = computed(() => Boolean(unref(enabledRef)))

  return useQuery({
    queryKey: ['journal-demand-letter-options'],
    queryFn: async () => {
      const result = await fetchDemandLetterOptions()
      const payload = result?.data?.data ?? result?.data ?? []
      return Array.isArray(payload) ? payload : []
    },
    enabled,
    staleTime: 5 * 60 * 1000,
    gcTime: 60 * 60 * 1000,
    refetchOnWindowFocus: false,
  })
}

export function useJobSelectOptionsQuery(enabledRef = true) {
  const query = useJobOptionsQuery(enabledRef)

  const jobSelectOptions = computed(() => formatJobSelectOptions(query.data.value ?? []))

  return {
    ...query,
    jobSelectOptions,
  }
}

export function useDemandLetterSelectOptionsQuery(enabledRef = true) {
  const query = useDemandLetterOptionsQuery(enabledRef)

  const demandLetterSelectOptions = computed(() =>
    formatDemandLetterSelectOptions(query.data.value ?? []),
  )

  return {
    ...query,
    demandLetterSelectOptions,
  }
}
