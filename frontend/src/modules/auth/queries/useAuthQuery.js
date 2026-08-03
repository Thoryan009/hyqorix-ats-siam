import { useQuery } from '@tanstack/vue-query'
import { getUser } from '../services/authService'

export function useAuthQuery(params = {}) {
  return useQuery({
    queryKey: ['auth', params],
    queryFn: () => getUser(params),

    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,
    keepPreviousData: true,

    retry(failureCount, error) {
      // ❌ DO NOT retry if unauthorized
      if (error?.response?.status === 401) return false
      return failureCount < 2
    },
  })
}