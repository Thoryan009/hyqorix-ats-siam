import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchAll } from '../services/chartOfAccountService'

export function useChartOfAccountsQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const type = computed(() => unref(filtersRef)?.type || '')
  const financialStatement = computed(() => unref(filtersRef)?.financial_statement || '')
  const normalBalance = computed(() => unref(filtersRef)?.normal_balance || '')
  const status = computed(() => unref(filtersRef)?.status || '')

  return useQuery({
    queryKey: computed(() => [
      'chart-of-accounts',
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      type.value || 'all',
      financialStatement.value || 'all',
      normalBalance.value || 'all',
      status.value || 'all',
    ]),
    queryFn: () =>
      fetchAll(page.value, perPage.value, {
        search: search.value,
        type: type.value || undefined,
        financial_statement: financialStatement.value || undefined,
        normal_balance: normalBalance.value || undefined,
        status: status.value || undefined,
      }),
    enabled: computed(() => !search.value || search.value.length >= 3),
    staleTime: 0,
    cacheTime: 0,
    keepPreviousData: true,
  })
}
