import { useQuery } from '@tanstack/vue-query'
import { fetchAll, fetchDetailHeads } from '../services/jobDetailsService'
import { computed, unref } from 'vue'

/**
 * Job Details (Charges)
 */
export function useJobDetailsQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const jobId = computed(() => unref(filtersRef)?.job_id)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'job-details'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      jobId.value,
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchAll(page.value, perPage.value, {
        search: search.value,
        job_id: jobId.value,
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
 * Job Detail Heads
 */
export function useJobDetailHeadsQuery() {
  return useQuery({
    queryKey: ['job-detail-heads'],
    queryFn: fetchDetailHeads,
    staleTime: Infinity,
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}
