import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { getPartySourceConfig } from '../data/partySourceConfig'

function extractRows(data) {
  return data?.data?.data ?? data?.data ?? []
}

export function usePartySourceOptionsQuery(partyTypeRef) {
  const partyType = computed(() => unref(partyTypeRef) || '')

  return useQuery({
    queryKey: computed(() => ['party-source-options', partyType.value]),
    queryFn: async () => {
      const config = getPartySourceConfig(partyType.value)
      if (!config) return []

      const { data, error } = await config.fetch()
      if (error) throw error

      return extractRows(data).map(config.mapItem)
    },
    enabled: computed(() => Boolean(getPartySourceConfig(partyType.value))),
    staleTime: 5 * 60 * 1000,
    gcTime: 60 * 60 * 1000,
    refetchOnWindowFocus: false,
  })
}
