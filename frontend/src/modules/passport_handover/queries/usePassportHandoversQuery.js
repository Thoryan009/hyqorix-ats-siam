import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchAll, fetchOne } from '../services/passportHandoverService'

export function usePassportHandoversQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const passportNo = computed(() => unref(filtersRef)?.passportNo?.trim())
  const type = computed(() => unref(filtersRef)?.type)
  const status = computed(() => unref(filtersRef)?.status)

  return useQuery({
    queryKey: computed(() => [
      'passport-handovers',
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      passportNo.value || 'all',
      type.value || 'all',
      status.value || 'all',
    ]),
    queryFn: () =>
      fetchAll(page.value, perPage.value, {
        search: search.value,
        passport_no: passportNo.value,
        type: type.value,
        status: status.value,
      }),
    enabled: computed(() => {
      if (passportNo.value) {
        return true
      }

      return !search.value || search.value.length >= 3
    }),
    staleTime: 60 * 1000,
    keepPreviousData: true,
  })
}

export function usePassportHandoverQuery(idRef) {
  const id = computed(() => unref(idRef))

  return useQuery({
    queryKey: computed(() => ['passport-handover', id.value]),
    queryFn: () => fetchOne(id.value),
    enabled: computed(() => !!id.value),
    staleTime: 30 * 1000,
  })
}
