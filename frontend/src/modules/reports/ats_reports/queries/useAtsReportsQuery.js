import { useQuery } from '@tanstack/vue-query'
import { fetchAll, fetchExportCSV, fetchOne } from '../services/atsReportService'
import { computed, unref } from 'vue'

export function useAtsReportsQuery(pageRef, perPageRef,filtersRef) {
  const processId = computed(() => unref(filtersRef)?.process_id)
  const workOrderId = computed(() => unref(filtersRef)?.work_order_id)
  const jobId = computed(() => unref(filtersRef)?.job_id)
  const type = computed(() => unref(filtersRef)?.type)
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'ats-reports'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      processId.value || 'all',
      workOrderId.value || 'all',
      jobId.value || 'all',
      page.value ,
      perPage.value ,
      type.value || 'all',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchAll({
        process_id: processId.value,
        work_order_id: workOrderId.value,
        job_list_id: jobId.value,
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

export function useAtsReportQuery(pageRef, perPageRef,filtersRef) {
  const processId = computed(() => unref(filtersRef)?.process_id)
  const applicationStatus = computed(() => unref(filtersRef)?.application_status)
  const workOrderId = computed(() => unref(filtersRef)?.work_order_id)
  const jobId = computed(() => unref(filtersRef)?.job_id)
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const type = computed(() => unref(filtersRef)?.type)
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'ats-report'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      processId.value || 'all',
      applicationStatus.value || 'all',
      workOrderId.value || 'all',
      jobId.value || 'all',
      clientId.value || 'all',
      type.value || 'all',
      page.value ,
      perPage.value ,
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchOne({
        process_id: processId.value,
        application_status: applicationStatus.value,
        work_order_id: workOrderId.value,
        job_list_id: jobId.value,
        client_id: clientId.value,
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
  const processId = computed(() => unref(filtersRef)?.process_id)
  const applicationStatus = computed(() => unref(filtersRef)?.application_status)
  const workOrderId = computed(() => unref(filtersRef)?.work_order_id)
  const jobId = computed(() => unref(filtersRef)?.job_id)
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const type = computed(() => unref(filtersRef)?.type)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'export-csv-report'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      processId.value || 'all',
      applicationStatus.value || 'all',
      workOrderId.value || 'all',
      jobId.value || 'all',
      clientId.value || 'all',
      type.value || 'all',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchExportCSV({
        process_id: processId.value,
        application_status: applicationStatus.value,
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
