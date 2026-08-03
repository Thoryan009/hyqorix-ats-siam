import { computed, unref } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { searchHandoversByPassport } from '../services/passportHandoverService'

export function mapPassportHandoverSearchItem(item) {
  return {
    id: item.id,
    handoverId: item.passport_handover_id,
    handoverNo: item.handover_no || '',
    passportNo: item.passport_no || '',
    candidateName: item.candidate_name || 'N/A',
    status: item.status,
    handoverStatus: item.handover_status,
    takerName: item.taker_name || '',
  }
}

export function usePassportHandoverPassportSearch(searchRef) {
  const search = computed(() => unref(searchRef)?.trim() || '')

  return useQuery({
    queryKey: computed(() => ['passport-handover-passport-search', search.value || 'all']),
    queryFn: () => searchHandoversByPassport(search.value),
    enabled: computed(() => search.value.length >= 1),
    staleTime: 30 * 1000,
    keepPreviousData: true,
  })
}
