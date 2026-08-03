import { useQuery } from '@tanstack/vue-query'
import { fetchAll } from '../services/settingService'

export function useSettingsQuery(settingId) {
  return useQuery({
    queryKey: ['settings', settingId],
    queryFn: fetchAll,
    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,
    meta: { persist: true },
  })
}
