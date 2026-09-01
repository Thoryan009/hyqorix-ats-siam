import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { watch } from 'vue'
import { fetchPartyTypeMappings, updatePartyTypeMappings } from '../services/settingService'
import { fetchPartyTypeOptions } from '@/modules/parties/services/partyTypeService'
import { usePartyTypeSourceStore } from '@/modules/parties/store/partyTypeSourceStore'
import { toast } from '@/shared/config/toastConfig'

export function usePartyTypeMappingsQuery() {
  return useQuery({
    queryKey: ['party-type-mappings'],
    queryFn: async () => {
      const result = await fetchPartyTypeMappings()
      return result?.data?.data ?? []
    },
    staleTime: 60 * 1000,
    refetchOnWindowFocus: false,
  })
}

export function usePartyTypeMappingOptionsQuery() {
  const sourceStore = usePartyTypeSourceStore()

  const query = useQuery({
    queryKey: ['party-type-mapping-options'],
    queryFn: async () => {
      const result = await fetchPartyTypeOptions('active')
      const payload = result?.data?.data ?? result?.data ?? []
      return Array.isArray(payload) ? payload : []
    },
    staleTime: 5 * 60 * 1000,
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

export function usePartyTypeMappingMutations() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: updatePartyTypeMappings,
    onSuccess: async () => {
      toast.success('Party type mappings updated successfully')
      queryClient.invalidateQueries({ queryKey: ['party-type-mappings'] })
      queryClient.invalidateQueries({ queryKey: ['party-type-options'] })
      queryClient.invalidateQueries({ queryKey: ['party-type-mapping-options'] })

      const result = await fetchPartyTypeOptions('active')
      const payload = result?.data?.data ?? result?.data ?? []
      if (Array.isArray(payload)) {
        usePartyTypeSourceStore().setFromPartyTypes(payload)
      }
    },
    onError: (error) => {
      const validationMessage = error?.errors && Object.values(error.errors).flat()[0]
      toast.error(validationMessage || error?.message || 'Request failed')
    },
  })
}
