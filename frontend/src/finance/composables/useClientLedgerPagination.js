import { ref, computed, unref } from 'vue'

export function useClientLedgerPagination(filteredRows, options = {}) {
  const page = ref(1)
  const perPage = ref(options.perPage ?? 250)
  const perPageOptions = options.perPageOptions ?? [10, 25, 50, 100, 250]

  const total = computed(() => unref(filteredRows).length)
  const showing = computed(() => Math.min(total.value, page.value * perPage.value))
  const links = computed(() => {
    const lastPage = Math.max(1, Math.ceil(total.value / perPage.value))
    return Array.from({ length: lastPage }, (_, index) => {
      const p = index + 1
      return {
        label: String(p),
        active: p === page.value,
        url: p === page.value ? null : '#',
      }
    })
  })

  const paginatedRows = computed(() => {
    const rows = unref(filteredRows)
    const start = (page.value - 1) * perPage.value
    return rows.slice(start, start + perPage.value)
  })

  function setPage(value) {
    if (value && value !== page.value) page.value = value
  }

  function setPerPage(value) {
    const next = Number(value)
    if (Number.isFinite(next) && next > 0 && next !== perPage.value) {
      perPage.value = next
      page.value = 1
    }
  }

  function resetPage() {
    page.value = 1
  }

  return {
    page,
    perPage,
    perPageOptions,
    total,
    showing,
    links,
    paginatedRows,
    setPage,
    setPerPage,
    resetPage,
  }
}
