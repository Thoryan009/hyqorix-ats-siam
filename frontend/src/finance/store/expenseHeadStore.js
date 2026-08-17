import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'
import {
  fetchAll,
  fetchSummary,
  submitData,
  updateData,
} from '../services/expenseHeadService'

export const useExpenseHeadStore = defineStore('expenseHead', () => {
  const { item, isModal, isViewModal, isEditModal, handleToggleModal, handleReset } =
    useModalHelpers()

  const moduleName = 'Expense Head'
  const heads = ref([])
  const isLoading = ref(false)
  const isLoaded = ref(false)
  const paginationMeta = ref({
    total: 0,
    from: 0,
    to: 0,
    current_page: 1,
    per_page: 10,
    last_page: 1,
    links: [],
  })
  const summaryData = ref({
    total_count: 0,
    active_count: 0,
    total_base_price: 0,
  })
  const lastFetchParams = ref({
    page: 1,
    perPage: 10,
    filters: {},
  })

  const totalHeads = computed(() => summaryData.value.total_count)
  const activeHeadCount = computed(() => summaryData.value.active_count)
  const totalBasePrice = computed(() => summaryData.value.total_base_price)

  function getApiErrorMessage(error, fallback = 'Request failed.') {
    return (
      Object.values(error?.errors || {})?.flat()?.[0] ||
      error?.message ||
      fallback
    )
  }

  function mapPayload(payload, linkedAccounts = []) {
    return {
      category_id: Number(payload.category_id),
      expense_category_id: Number(payload.category_id),
      name: payload.name?.trim(),
      base_price: Number(payload.base_price ?? 0),
      status: payload.status ?? 'Active',
      linked_accounts: linkedAccounts,
      is_bills_receivable_link:
        payload.is_bills_receivable_link === true ||
        payload.is_bills_receivable_link === 1 ||
        payload.is_bills_receivable_link === '1',
      is_depreciation_expense:
        payload.is_depreciation_expense === true ||
        payload.is_depreciation_expense === 1 ||
        payload.is_depreciation_expense === '1',
    }
  }

  function normalizeFetchOptions(options = true) {
    if (typeof options === 'boolean') {
      return {
        force: options,
        page: lastFetchParams.value.page,
        perPage: lastFetchParams.value.perPage,
        filters: { ...lastFetchParams.value.filters },
      }
    }

    return {
      force: options.force !== false,
      page: Number(options.page) || lastFetchParams.value.page || 1,
      perPage: Number(options.perPage) || lastFetchParams.value.perPage || 10,
      filters: { ...(options.filters ?? lastFetchParams.value.filters ?? {}) },
    }
  }

  function extractPaginationMeta(payload, page, perPage, rowCount) {
    const meta = payload?.meta ?? {}
    const total = Number(meta.total ?? rowCount) || 0
    const currentPage = Number(meta.current_page ?? page) || 1
    const perPageValue = Number(meta.per_page ?? perPage) || perPage
    const lastPage = Math.max(1, Number(meta.last_page) || Math.ceil(total / perPageValue) || 1)
    const from = Number(meta.from ?? (total === 0 ? 0 : (currentPage - 1) * perPageValue + 1))
    const to = Number(meta.to ?? (total === 0 ? 0 : Math.min(currentPage * perPageValue, total)))

    const links =
      Array.isArray(meta.links) && meta.links.length
        ? meta.links
        : Array.from({ length: lastPage }, (_, index) => ({
            label: String(index + 1),
            active: currentPage === index + 1,
            url: currentPage === index + 1 ? null : '#',
          }))

    return {
      total,
      from,
      to,
      current_page: currentPage,
      per_page: perPageValue,
      last_page: lastPage,
      links,
    }
  }

  function mapHead(head) {
    return {
      ...head,
      category_id: Number(head.category_id ?? head.expense_category_id),
    }
  }

  async function fetchHeadSummary(filters = {}) {
    try {
      const { data, error } = await fetchSummary(filters)
      if (error) throw error
      const payload = data?.data ?? {}
      summaryData.value = {
        total_count: Number(payload.total_count) || 0,
        active_count: Number(payload.active_count) || 0,
        total_base_price: Number(payload.total_base_price) || 0,
      }
    } catch {
      // Keep prior summary on failure.
    }
  }

  async function fetchHeads(options = true) {
    const { force, page, perPage, filters } = normalizeFetchOptions(options)

    if (isLoading.value) return
    if (isLoaded.value && !force) return

    isLoading.value = true
    lastFetchParams.value = { page, perPage, filters }

    try {
      const { data, error } = await fetchAll(page, perPage, filters)
      if (error) throw error

      const rows = (data?.data ?? []).map(mapHead)
      heads.value = rows
      paginationMeta.value = extractPaginationMeta(data, page, perPage, rows.length)
      isLoaded.value = true
    } catch {
      heads.value = []
      paginationMeta.value = extractPaginationMeta(null, page, perPage, 0)
    } finally {
      isLoading.value = false
    }
  }

  function getHeadsByCategory(categoryId) {
    return heads.value.filter((head) => Number(head.category_id) === Number(categoryId))
  }

  function getHead(headId) {
    return heads.value.find((head) => Number(head.id) === Number(headId)) ?? null
  }

  function getBillsReceivableLinkedHead() {
    return heads.value.find((head) => head.is_bills_receivable_link) ?? null
  }

  function validateLinkedAccounts(linkedAccounts = []) {
    const rows = Array.isArray(linkedAccounts) ? linkedAccounts : []
    const startedRows = rows.filter((link) => link.account_category)

    const normalized = startedRows.map((link) => ({
      account_category: link.account_category ?? '',
    }))

    const keys = new Set()

    for (const link of normalized) {
      if (!link.account_category) {
        return { ok: false, message: 'Please select an account type for each linked row.' }
      }

      if (keys.has(link.account_category)) {
        return { ok: false, message: 'Duplicate account types are not allowed.' }
      }

      keys.add(link.account_category)
    }

    return { ok: true, data: normalized }
  }

  async function addHead(payload) {
    const categoryId = Number(payload.category_id)

    if (!categoryId) {
      return { ok: false, message: 'Expense category is required.' }
    }

    if (!payload.name?.trim()) {
      return { ok: false, message: 'Expense head name is required.' }
    }

    const basePrice = Number(payload.base_price)
    if (!Number.isFinite(basePrice) || basePrice < 0) {
      return { ok: false, message: 'Please enter a valid base price.' }
    }

    const linkResult = validateLinkedAccounts(payload.linked_accounts)
    if (!linkResult.ok) {
      return linkResult
    }

    try {
      const response = await submitData(mapPayload(payload, linkResult.data))
      const created = response?.data
      await fetchHeads({
        force: true,
        page: lastFetchParams.value.page,
        perPage: lastFetchParams.value.perPage,
        filters: lastFetchParams.value.filters,
      })
      await fetchHeadSummary({})
      return { ok: true, head: created }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to create expense head.') }
    }
  }

  async function updateHead(payload) {
    const categoryId = Number(payload.category_id)

    if (!categoryId) {
      return { ok: false, message: 'Expense category is required.' }
    }

    if (!payload.name?.trim()) {
      return { ok: false, message: 'Expense head name is required.' }
    }

    const basePrice = Number(payload.base_price)
    if (!Number.isFinite(basePrice) || basePrice < 0) {
      return { ok: false, message: 'Please enter a valid base price.' }
    }

    const linkResult = validateLinkedAccounts(payload.linked_accounts)
    if (!linkResult.ok) {
      return linkResult
    }

    try {
      await updateData({ id: payload.id, ...mapPayload(payload, linkResult.data) })
      await fetchHeads({
        force: true,
        page: lastFetchParams.value.page,
        perPage: lastFetchParams.value.perPage,
        filters: lastFetchParams.value.filters,
      })
      await fetchHeadSummary({})
      return { ok: true }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to update expense head.') }
    }
  }

  return {
    item,
    isModal,
    isViewModal,
    isEditModal,
    moduleName,
    heads,
    isLoading,
    isLoaded,
    paginationMeta,
    summaryData,
    lastFetchParams,
    totalHeads,
    activeHeadCount,
    totalBasePrice,
    handleToggleModal,
    handleReset,
    fetchHeads,
    fetchHeadSummary,
    getHeadsByCategory,
    getHead,
    getBillsReceivableLinkedHead,
    addHead,
    updateHead,
  }
})
