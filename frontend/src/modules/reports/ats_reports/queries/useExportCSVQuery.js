import { useQuery } from '@tanstack/vue-query'
import { fetchExportCSV } from '../services/atsReportService'
import { computed, unref } from 'vue'

export function useExportCSVQuery(filtersRef) {
  const processId = computed(() => unref(filtersRef)?.process_id)
  const workOrderId = computed(() => unref(filtersRef)?.work_order_id)
  const jobId = computed(() => unref(filtersRef)?.job_id)
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const type = computed(() => unref(filtersRef)?.type)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)

  return useQuery({
    queryKey: computed(() => [
      'export-csv',
      processId.value || 'all',
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
        work_order_id: workOrderId.value,
        job_list_id: jobId.value,
        client_id: clientId.value,
        type: type.value,
        from_date: fromDate.value,
        to_date: toDate.value,
      }),
    staleTime: Infinity,
    cacheTime: 24 * 60 * 60 * 1000,
    enabled: false,
    meta: {
      persist: true,
    },
  })
}
