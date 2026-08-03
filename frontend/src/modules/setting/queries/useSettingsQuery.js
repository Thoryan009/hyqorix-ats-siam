import { computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { fetchOne, fetchSettingData } from '../services/settingService'

export function useSettingsQuery(settingId) {
  return useQuery({
    queryKey: ['settings'],

    queryFn: () => fetchOne(settingId),

    enabled: computed(() => !!settingId),
    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,

    meta: {
      persist: true,
    },
  })
}


export function useSettingDataQuery() {
  return useQuery({
    queryKey: ['setting-data'],
    queryFn: fetchSettingData,
    staleTime: Infinity, // rarely changes
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}
