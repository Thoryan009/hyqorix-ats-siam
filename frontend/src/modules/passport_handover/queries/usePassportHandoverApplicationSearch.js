import { computed, unref } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { searchApplicationsByPassport } from '../services/passportHandoverService'

export function mapApplicationSearchItem(application) {
  return {
    id: application.id,
    applicationCode: application.application_id,
    passportNo: application.passport_no || '',
    fullName:
      application.full_name ||
      [application.given_name, application.sur_name].filter(Boolean).join(' ').trim() ||
      'N/A',
    mobile: application.mobile || '',
    jobTitle: application.job_list?.title || application.job_list?.job_title || '',
  }
}

export function usePassportHandoverApplicationSearch(searchRef) {
  const search = computed(() => unref(searchRef)?.trim() || '')

  return useQuery({
    queryKey: computed(() => ['passport-handover-application-search', search.value || 'all']),
    queryFn: () => searchApplicationsByPassport(search.value),
    enabled: computed(() => search.value.length >= 3),
    staleTime: 60 * 1000,
    keepPreviousData: true,
  })
}
