import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchAtsSummaryReport, exportAtsSummaryReportCsv } from '../services/useAtsSummaryReportService'

export function useAtsSummaryReportQuery(pageRef, perPageRef, filtersRef) {
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const process = computed(() => unref(filtersRef)?.process)
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const agentId = computed(() => unref(filtersRef)?.agent_id)
  const jobId = computed(() => unref(filtersRef)?.job_id)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))

  return useQuery({
    queryKey: computed(() => [
      'ats-summary-report',
      search.value && search.value.length >= 3 ? search.value : 'all',
      process.value || 'all',
      clientId.value || 'all',
      agentId.value || 'all',
      jobId.value || 'all',
      fromDate.value || 'today',
      toDate.value || 'today',
      page.value,
      perPage.value,
    ]),

    queryFn: () =>
      fetchAtsSummaryReport({
        process: process.value,
        search: search.value,
        client_id: clientId.value,
        agent_id: agentId.value,
        job_id: jobId.value,
        from_date: fromDate.value,
        to_date: toDate.value,
        page: page.value,
        per_page: perPage.value,
      }),

    enabled: computed(() => !search.value || search.value.length >= 3),

    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,
    keepPreviousData: true,
    meta: {
      persist: true,
    },
  })
}


export function useExportAtsSummaryReportCsv(filtersRef) {
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)

  return useQuery({
    queryKey: computed(() => [
      'export-ats-summary-report',
      clientId.value || 'all',
      fromDate.value || 'today',
      toDate.value || 'today',
    ]),
    queryFn: () =>
      exportAtsSummaryReportCsv({

        client_id: clientId.value,

        from_date: fromDate.value,
        to_date: toDate.value,
      }),


    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,
    keepPreviousData: true,
    meta: {
      persist: true,
    },
  })
}
