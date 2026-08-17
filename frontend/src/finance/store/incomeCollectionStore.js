import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import {
  fetchIncomeCollections,
  fetchIncomeCollectionSummary,
  submitIncomeCollection,
} from '../services/incomeCollectionService'
import { extractPaginatedRows } from '@/shared/utils/extractPaginatedRows'
import { useFinanceAccountStore } from './financeAccountStore'
import { useIncomeAccountsStore } from './incomeAccountsStore'
import { useAccountLedgerStore } from './accountLedgerStore'
import { useAccountStore } from './accountStore'
import { incomeAccountConfigs } from '../config/incomeAccountConfigs'

export const useIncomeCollectionStore = defineStore('incomeCollection', () => {
  const collections = ref([])
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
    this_month_count: 0,
    total_collected: 0,
  })
  const lastFetchParams = ref({
    page: 1,
    perPage: 10,
    filters: {},
  })

  function getApiErrorMessage(error, fallback = 'Request failed.') {
    return (
      Object.values(error?.errors || {})?.flat()?.[0] ||
      error?.message ||
      fallback
    )
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

  async function fetchCollectionSummary(filters = lastFetchParams.value.filters) {
    try {
      const { data, error } = await fetchIncomeCollectionSummary(filters)
      if (error) throw error
      const payload = data?.data ?? {}
      summaryData.value = {
        total_count: Number(payload.total_count) || 0,
        this_month_count: Number(payload.this_month_count) || 0,
        total_collected: Number(payload.total_collected) || 0,
      }
    } catch {
      // Keep prior summary on failure.
    }
  }

  async function fetchCollections(options = true) {
    const { force, page, perPage, filters } = normalizeFetchOptions(options)

    if (isLoading.value) return
    if (isLoaded.value && !force) return

    isLoading.value = true
    lastFetchParams.value = { page, perPage, filters }

    try {
      const [{ data, error }] = await Promise.all([
        fetchIncomeCollections(page, perPage, filters),
        fetchCollectionSummary(filters),
      ])
      if (error) throw error

      const rows = extractPaginatedRows(data)
      collections.value = rows
      paginationMeta.value = extractPaginationMeta(data, page, perPage, rows.length)
      isLoaded.value = true
    } catch {
      collections.value = []
      paginationMeta.value = extractPaginationMeta(null, page, perPage, 0)
    } finally {
      isLoading.value = false
    }
  }

  const totalCollectedAmount = computed(() => summaryData.value.total_collected)

  const totalCollectionCount = computed(() => summaryData.value.total_count)

  const thisMonthCollectionCount = computed(() => summaryData.value.this_month_count)

  const thisMonthCollectedAmount = computed(() => {
    const now = new Date()
    const month = now.getMonth()
    const year = now.getFullYear()

    return collections.value.reduce((sum, row) => {
      const raw = row.collection_date_raw || row.collection_date
      if (!raw) return sum
      const date = new Date(raw)
      if (date.getMonth() !== month || date.getFullYear() !== year) return sum
      return sum + Number(row.amount || 0)
    }, 0)
  })

  async function refreshAccountsAfterCollection(payload = {}, created = {}) {
    const financeAccountStore = useFinanceAccountStore()
    const incomeAccountsStore = useIncomeAccountsStore()
    const ledgerStore = useAccountLedgerStore()
    const accountStore = useAccountStore()

    const accountIds = new Set(
      [
        payload.receive_account_id,
        payload.liability_account_id,
        payload.linked_account_id,
        created.receive_account_id,
        created.linked_account_id,
      ]
        .map((id) => Number(id))
        .filter(Boolean)
    )

    const incomeType = incomeAccountsStore.getIncomeTypeByCategoryId(
      Number(payload.category_id ?? created.income_category_id)
    )

    await Promise.all([
      accountStore.fetchActiveAccounts(true),
      accountStore.refreshAccounts?.() ?? accountStore.fetchAccounts(true),
      ...Object.keys(incomeAccountConfigs).map((type) =>
        incomeAccountsStore.fetchAccounts(type, true)
      ),
      incomeType ? incomeAccountsStore.fetchAccounts(incomeType, true) : Promise.resolve(),
      financeAccountStore.fetchAllCategories(true),
    ])

    accountIds.forEach((accountId) => {
      ledgerStore.invalidateAccountLedger(accountId)
    })

    if (incomeType) {
      const incomeAccount = incomeAccountsStore.getAccountByHeadId(
        Number(payload.category_id ?? created.income_category_id),
        Number(payload.head_id ?? created.income_head_id)
      )
      if (incomeAccount?.id) {
        ledgerStore.invalidateAccountLedger(incomeAccount.id)
      }
    }
  }

  async function collectIncome(payload) {
    try {
      const response = await submitIncomeCollection(payload)
      const created = response?.data ?? response
      await fetchCollections({
        force: true,
        page: lastFetchParams.value.page,
        perPage: lastFetchParams.value.perPage,
        filters: lastFetchParams.value.filters,
      })
      await refreshAccountsAfterCollection(payload, created)
      return { ok: true, data: created }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to collect income.') }
    }
  }

  function getCollectedAmountForCandidate(applicationId) {
    const id = Number(applicationId)
    if (!id) return 0

    return collections.value.reduce((sum, row) => {
      if (String(row.payment_method || '').toLowerCase() === 'due') {
        return sum
      }

      const candidates = Array.isArray(row.candidates) ? row.candidates : []
      return (
        sum +
        candidates.reduce((inner, candidate) => {
          const appId = Number(candidate.application_id ?? candidate.candidate_id)
          if (appId !== id) return inner
          return inner + (Number(candidate.amount) || 0)
        }, 0)
      )
    }, 0)
  }

  function candidateHasDueReceivable(applicationId) {
    const id = Number(applicationId)
    if (!id) return false

    return collections.value.some((row) => {
      if (String(row.payment_method || '').toLowerCase() !== 'due') return false
      const candidates = Array.isArray(row.candidates) ? row.candidates : []
      return candidates.some(
        (candidate) => Number(candidate.application_id ?? candidate.candidate_id) === id
      )
    })
  }

  function getLatestSalePriceForCandidate(applicationId) {
    const id = Number(applicationId)
    if (!id) return 0

    for (const row of collections.value) {
      const candidates = Array.isArray(row.candidates) ? row.candidates : []
      for (const candidate of candidates) {
        const appId = Number(candidate.application_id ?? candidate.candidate_id)
        if (appId !== id) continue
        const salePrice = Number(candidate.sale_price) || 0
        if (salePrice > 0) return salePrice
      }
    }

    return 0
  }

  return {
    collections,
    isLoading,
    isLoaded,
    paginationMeta,
    summaryData,
    lastFetchParams,
    totalCollectedAmount,
    totalCollectionCount,
    thisMonthCollectionCount,
    thisMonthCollectedAmount,
    fetchCollections,
    fetchCollectionSummary,
    collectIncome,
    getCollectedAmountForCandidate,
    getLatestSalePriceForCandidate,
    candidateHasDueReceivable,
  }
})
