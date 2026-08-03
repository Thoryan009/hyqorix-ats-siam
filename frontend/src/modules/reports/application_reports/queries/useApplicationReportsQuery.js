import { useQuery } from '@tanstack/vue-query'
import { fetchAll, fetchOne, exportCSV } from '../services/applicationReportService'
import { computed, unref } from 'vue'

export function useApplicationReportsQuery(filtersRef) {
  const countryId = computed(() => unref(filtersRef)?.country_id)
  const workOrderId = computed(() => unref(filtersRef)?.work_order_id)
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const jobId = computed(() => unref(filtersRef)?.job_id)
  const type = computed(() => unref(filtersRef)?.type)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'application-reports'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      countryId.value || 'all',
      workOrderId.value || 'all',
      jobId.value || 'all',
      clientId.value || 'all',
      type.value || 'all',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchAll({
        country_id: countryId.value,
        work_order_id: workOrderId.value,
        job_list_id: jobId.value,
        client_id: clientId.value,
        type: type.value,
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

export function useApplicationReportQuery(pageRef, perPageRef,filtersRef) {
  const countryId = computed(() => unref(filtersRef)?.country_id)
  const workOrderId = computed(() => unref(filtersRef)?.work_order_id)
  const principalId = computed(() => unref(filtersRef)?.principal_id)
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const processId = computed(() => unref(filtersRef)?.process_id)
  const jobId = computed(() => unref(filtersRef)?.job_id)
  const agentId = computed(() => unref(filtersRef)?.agent_id)
  const applicationStatus = computed(() => unref(filtersRef)?.application_status)
  const type = computed(() => unref(filtersRef)?.type)
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'application-reports'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      countryId.value || 'all',
      workOrderId.value || 'all',
      principalId.value || 'all',
      jobId.value || 'all',
      clientId.value || 'all',
      processId.value || 'all',
      agentId.value || 'all',
      applicationStatus.value || 'all',
      type.value || 'all',
      page.value,
      perPage.value,
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchOne({
        country_id: countryId.value,
        work_order_id: workOrderId.value,
        principal_id: principalId.value,
        job_list_id: jobId.value,
        client_id: clientId.value,
        process_id: processId.value,
        agent_id: agentId.value,
        application_status: applicationStatus.value,
        type: type.value,
        page: page.value,
        per_page: perPage.value,
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

export function useExportCSVQuery(filtersRef) {
  const countryId = computed(() => unref(filtersRef)?.country_id)
  const workOrderId = computed(() => unref(filtersRef)?.work_order_id)
  const principalId = computed(() => unref(filtersRef)?.principal_id)
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const processId = computed(() => unref(filtersRef)?.process_id)
  const jobId = computed(() => unref(filtersRef)?.job_id)
  const agentId = computed(() => unref(filtersRef)?.agent_id)
  const applicationStatus = computed(() => unref(filtersRef)?.application_status)
  const type = computed(() => unref(filtersRef)?.type)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'application-reports'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      countryId.value || 'all',
      workOrderId.value || 'all',
      principalId.value || 'all',
      jobId.value || 'all',
      clientId.value || 'all',
      processId.value || 'all',
      agentId.value || 'all',
      applicationStatus.value || 'all',
      type.value || 'all',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      exportCSV({
        country_id: countryId.value,
        work_order_id: workOrderId.value,
        principal_id: principalId.value,
        job_list_id: jobId.value,
        client_id: clientId.value,
        process_id: processId.value,
        agent_id: agentId.value,
        application_status: applicationStatus.value,
        type: type.value,
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
