import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { fetchIncomeCollections, submitIncomeCollection } from '../services/incomeCollectionService'
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

  function getApiErrorMessage(error, fallback = 'Request failed.') {
    return (
      Object.values(error?.errors || {})?.flat()?.[0] ||
      error?.message ||
      fallback
    )
  }

  async function fetchCollections(force = false) {
    if (isLoading.value) return
    if (isLoaded.value && !force) return

    isLoading.value = true
    try {
      const { data, error } = await fetchIncomeCollections(1, 500, {})
      if (error) throw error
      collections.value = extractPaginatedRows(data)
      isLoaded.value = true
    } catch {
      collections.value = []
    } finally {
      isLoading.value = false
    }
  }

  const totalCollectedAmount = computed(() =>
    collections.value.reduce((sum, row) => sum + Number(row.amount || 0), 0)
  )

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
      if (created?.id) {
        collections.value.unshift(created)
      } else {
        await fetchCollections(true)
      }
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
    totalCollectedAmount,
    thisMonthCollectedAmount,
    fetchCollections,
    collectIncome,
    getCollectedAmountForCandidate,
    getLatestSalePriceForCandidate,
    candidateHasDueReceivable,
  }
})