import { useQuery } from '@tanstack/vue-query'
import { computed, unref, watch } from 'vue'
import { fetchPartyTypeOptions } from '../services/partyTypeService'
import { usePartyTypeSourceStore } from '../store/partyTypeSourceStore'

export function usePartyTypeOptionsQuery(statusRef = 'active', enabledRef = true) {
  const status = computed(() => unref(statusRef) || 'active')
  const enabled = computed(() => Boolean(unref(enabledRef)))
  const sourceStore = usePartyTypeSourceStore()

  const query = useQuery({
    queryKey: computed(() => ['party-type-options', status.value]),
    queryFn: async () => {
      const result = await fetchPartyTypeOptions(status.value)
      const payload = result?.data?.data ?? result?.data ?? []
      return Array.isArray(payload) ? payload : []
    },
    enabled,
    staleTime: 5 * 60 * 1000,
    gcTime: 60 * 60 * 1000,
    refetchOnWindowFocus: false,
  })

  watch(
    () => query.data.value,
    (partyTypes) => {
      if (partyTypes) {
        sourceStore.setFromPartyTypes(partyTypes)
      }
    },
    { immediate: true },
  )

  return query
}
