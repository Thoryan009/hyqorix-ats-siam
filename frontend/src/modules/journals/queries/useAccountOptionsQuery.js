import { useQuery } from '@tanstack/vue-query'
import { fetchAll } from '@/modules/accounts/services/chartOfAccountService'

export function useAccountOptionsQuery() {
  return useQuery({
    queryKey: ['account-options', 'active'],
    queryFn: () =>
      fetchAll(1, 500, {
        status: 'active',
      }),
    staleTime: 5 * 60 * 1000,
    gcTime: 60 * 60 * 1000,
    refetchOnWindowFocus: false,
  })
}
