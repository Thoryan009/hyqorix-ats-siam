import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchAll, fetchCategories } from '../services/documentService'

export function useDocumentsQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const category = computed(() => unref(filtersRef)?.category)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)

  return useQuery({
    queryKey: computed(() => [
      'documents',
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      category.value || 'all',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),
    queryFn: () =>
      fetchAll(page.value, perPage.value, {
        search: search.value,
        category: category.value,
        from_date: fromDate.value,
        to_date: toDate.value,
      }),
    enabled: computed(() => !search.value || search.value.length >= 3),
    staleTime: 5 * 60 * 1000,
    keepPreviousData: true,
  })
}

export function useDocumentCategoriesQuery() {
  return useQuery({
    queryKey: ['document-categories'],
    queryFn: fetchCategories,
    staleTime: 60 * 60 * 1000,
  })
}
