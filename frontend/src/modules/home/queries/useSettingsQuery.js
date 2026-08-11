import { useQuery } from '@tanstack/vue-query'
import { fetchAll } from '../services/settingService'

export function useSettingsQuery() {
  return useQuery({
    queryKey: ['public-settings'],
    queryFn: fetchAll,
    staleTime: 0,
    cacheTime: 60 * 60 * 1000,
    refetchOnMount: 'always',
    meta: { persist: true },
  })
}
