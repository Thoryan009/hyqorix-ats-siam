import { ref, computed, watch } from 'vue'

export function usePagination(options = {}) {
  // INTERNAL STATE
  const page = ref(options.page ?? 1)
  const perPage = ref(options.perPage ?? 10)

  const _total = ref(0)
  const _showing = ref(0)
  const _links = ref([])

  // RESET PAGE WHEN PER PAGE CHANGES
  watch(perPage, () => {
    page.value = 1
  })

  // ACTIONS
  function setPage(value) {
    if (value && value !== page.value) {
      page.value = value
    }
  }

  function setPerPage(value) {
    perPage.value = Number(value)
  }

  // META BINDING
  function bindMeta(queryDataRef) {
    watch(
      queryDataRef,
      (val) => {
        const meta = val?.data?.meta
        if (!meta) return

        _total.value = meta.total ?? 0
        _showing.value = meta.to ?? 0
        _links.value = meta.links ?? []
      },
      { immediate: true },
    )
  }

  // 🔥 PUBLIC API (NO REFS EXPOSED TO TEMPLATE)
  return {
    // query inputs (refs are OK here)
    page,
    perPage,

    // template-safe values
    total: computed(() => _total.value),
    showing: computed(() => _showing.value),
    links: computed(() => _links.value),

    // actions
    setPage,
    setPerPage,
    bindMeta,
  }
}
