import { useQuery } from '@tanstack/vue-query'
import { fetchAll as fetchJobs } from '@/modules/job/services/jobService'
import { formatJobSelectOption, mapJobFromApi, sortJobsAlphabetically } from '@/finance/utils/jobListMapper'

export function usePartyJobOptionsQuery(enabledRef) {
  return useQuery({
    queryKey: ['party-job-options'],
    queryFn: async () => {
      const { data, error } = await fetchJobs(1, 500, {
        include_ats_count: 1,
      })
      if (error) throw error

      const rows = data?.data?.data ?? data?.data ?? []
      return sortJobsAlphabetically(rows.map(mapJobFromApi)).map(formatJobSelectOption)
    },
    enabled: enabledRef,
    staleTime: 5 * 60 * 1000,
    gcTime: 60 * 60 * 1000,
    refetchOnWindowFocus: false,
  })
}
