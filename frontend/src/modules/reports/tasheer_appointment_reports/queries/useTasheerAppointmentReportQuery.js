import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchTasheerAppointmentReport, fetchTasheerFilterData } from '../services/tasheerAppointmentReportService'

export function useTasheerAppointmentReportQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const status = computed(() => unref(filtersRef)?.status)
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const agentId = computed(() => unref(filtersRef)?.agent_id)
  const jobId = computed(() => unref(filtersRef)?.job_id)
  return useQuery({
    queryKey: computed(() => [
      'tasheer-appointment-report',
      page.value,
      perPage.value,
      clientId.value || 'all',
      agentId.value || 'all',
      jobId.value || 'all',
      status.value ?? 'pending',
    ]),
    queryFn: () =>
      fetchTasheerAppointmentReport({
        page: page.value,
        per_page: perPage.value,
        client_id: clientId.value,
        agent_id: agentId.value,
        job_id: jobId.value,
        status: status.value ?? 'pending',
      }),
    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,
    keepPreviousData: true,
    meta: {
      persist: true,
    },
  })
}

export function useTasheerFilterDataQuery() {
  return useQuery({
    queryKey: ['tasheer-appointment-filter-data'],
    queryFn: fetchTasheerFilterData,
    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}
