import { useQuery } from '@tanstack/vue-query'
import { fetchAll, fetchHiringListData, fetchApplicationListData, fetchEmbassySubmissiondData, fetchShortListData, fetchWaitingListData, fetchRejectedListData, fetchEmbassySubmissions, fetchApplicationProcesses, fetchApplicationData, fetchSingle, fetchMofaInformations } from '../services/applicationService'
import { computed, unref } from 'vue'

export function useApplicationsQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const jobId = computed(() => unref(filtersRef)?.job_id)
  const workOrderId = computed(() => unref(filtersRef)?.work_order_id)
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const agentId = computed(() => unref(filtersRef)?.agent_id)
  const process_id = computed(() => unref(filtersRef)?.process_id)
  const applicationStatus = computed(() => unref(filtersRef)?.application_status)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'applications'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      jobId.value || 'all',
      workOrderId.value || 'all',
      clientId.value || 'all',
      agentId.value || 'all',
      process_id.value || 'all',
      applicationStatus.value || 'all',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchAll(page.value, perPage.value, {
        search: search.value,
        job_list_id:  Number(jobId.value),
        work_order_id: Number(workOrderId.value),
        client_id: Number(clientId.value),
        agent_id: Number(agentId.value),
        process_id: Number(process_id.value),
        application_status: applicationStatus.value,
        from_date: fromDate.value,
        to_date: toDate.value,
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

export function useEmbassySubmissionsQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const jobId = computed(() => unref(filtersRef)?.job_id)
  const workOrderId = computed(() => unref(filtersRef)?.work_order_id)
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const agentId = computed(() => unref(filtersRef)?.agent_id)
  const process_id = computed(() => unref(filtersRef)?.process_id)
  const status = computed(() => unref(filtersRef)?.status)
  const hasEmbassySubmission = computed(() => unref(filtersRef)?.has_embassy_submission)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'embassy-submissions'
  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      jobId.value || 'all',
      workOrderId.value || 'all',
      clientId.value || 'all',
      agentId.value || 'all',
      process_id.value || 'all',
      status.value || 'all',
      hasEmbassySubmission.value || 'all',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchEmbassySubmissions(page.value, perPage.value, {
        search: search.value,
        job_list_id:  Number(jobId.value),
        work_order_id: Number(workOrderId.value),
        client_id: Number(clientId.value),
        agent_id: Number(agentId.value),
        process_id: Number(process_id.value),
        status: status.value,
        has_embassy_submission: hasEmbassySubmission.value,
        from_date: fromDate.value,
        to_date: toDate.value,
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

/**
 * Client countries
 */
export function useApplicationDataQuery() {
  return useQuery({
    queryKey: ['application-data', 'v6'],
    queryFn: fetchApplicationData,
    staleTime: Infinity,
    meta: {
      persist: true,
    },
  })
}

export function useEmbassySubmissionDataQuery() {
  return useQuery({
    queryKey: ['embassy-submissions-ksa-data'],
    queryFn: fetchEmbassySubmissiondData,
    staleTime: Infinity,
    meta: {
      persist: true,
    },
  })
}

export function useHiringListDataQuery() {
  return useQuery({
    queryKey: ['hiring-list-data'],
    queryFn: fetchHiringListData,
    staleTime: Infinity,
    meta: {
      persist: true,
    },
  })
}

export function useApplicationListDataQuery() {
  return useQuery({
    queryKey: ['application-list-data'],
    queryFn: fetchApplicationListData,
    staleTime: Infinity,
    meta: {
      persist: true,
    },
  })
}

export function useShortListDataQuery() {
  return useQuery({
    queryKey: ['short-list-data'],
    queryFn: fetchShortListData,
    staleTime: Infinity,
    meta: {
      persist: true,
    },
  })
}

export function useWaitingListDataQuery() {
  return useQuery({
    queryKey: ['waiting-list-data'],
    queryFn: fetchWaitingListData,
    staleTime: Infinity,
    meta: {
      persist: true,
    },
  })
}

export function useRejectedListDataQuery() {
  return useQuery({
    queryKey: ['rejected-list-data'],
    queryFn: fetchRejectedListData,
    staleTime: Infinity,
    meta: {
      persist: true,
    },
  })
}

export function useApplicationProcessesQuery() {
  return useQuery({
    queryKey: ['application-processes'],
    queryFn: fetchApplicationProcesses,
    staleTime: Infinity,
    meta: {
      persist: true,
    },
  })
}

/**
 * Fetch single application by ID
 */
export function useApplicationQuery(idRef) {
  const id = computed(() => unref(idRef))

  return useQuery({
    queryKey: computed(() => ['application', id.value]),
    queryFn: () => fetchSingle(id.value),
    enabled: computed(() => !!id.value),
    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,
  })
}

/**
 * Fetch MOFA (embassy submission) informations by application ID
 */
export function useMofaInformationsQuery(applicationIdRef) {
  const applicationId = computed(() => unref(applicationIdRef))

  return useQuery({
    queryKey: computed(() => ['mofa-informations', applicationId.value]),
    queryFn: () => fetchMofaInformations(applicationId.value),
    enabled: computed(() => !!applicationId.value),
    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,
  })
}
