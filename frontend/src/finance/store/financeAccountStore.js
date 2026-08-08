import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { ACCOUNT_CATEGORIES } from '../data/accountCategoryCodes'
import {
  bulkDelete,
  deleteItem,
  fetchAll,
  fetchOne,
  fetchSummary,
  submitData,
  updateData,
} from '../services/financeAccountService'

export const useFinanceAccountStore = defineStore('financeAccount', () => {
  const accounts = ref([])
  const isLoading = ref(false)
  const loadingCategories = ref([])
  const loadedCategories = ref(new Set())

  function isCategoryLoading(category) {
    return loadingCategories.value.includes(category)
  }

  function getApiErrorMessage(error, fallback = 'Request failed.') {
    return (
      Object.values(error?.errors || {})?.flat()?.[0] ||
      error?.message ||
      fallback
    )
  }

  function getAccountsByCategory(category) {
    return accounts.value.filter((account) => account.category === category)
  }

  function getAccount(accountId) {
    return accounts.value.find((account) => Number(account.id) === Number(accountId)) ?? null
  }

  function getAccountByCategory(category, accountId) {
    return (
      getAccountsByCategory(category).find(
        (account) => Number(account.id) === Number(accountId)
      ) ?? null
    )
  }

  async function fetchAccounts(category = null, force = false) {
    if (category) {
      if (loadingCategories.value.includes(category)) return
      if (loadedCategories.value.has(category) && !force) return

      loadingCategories.value = [...loadingCategories.value, category]
    } else {
      if (isLoading.value) return
      isLoading.value = true
    }

    try {
      const filters = category ? { category } : {}
      const { data, error } = await fetchAll(1, 500, filters)
      if (error) throw error

      const rows = data?.data ?? []

      if (category) {
        accounts.value = [
          ...accounts.value.filter((account) => account.category !== category),
          ...rows,
        ]
        loadedCategories.value.add(category)
      } else {
        accounts.value = rows
        loadedCategories.value = new Set(rows.map((row) => row.category))
      }
    } finally {
      if (category) {
        loadingCategories.value = loadingCategories.value.filter((item) => item !== category)
      } else {
        isLoading.value = false
      }
    }
  }

  async function fetchAllCategories(force = false) {
    const categories = Object.values(ACCOUNT_CATEGORIES).filter(
      (category) =>
        category !== ACCOUNT_CATEGORIES.CAPITAL &&
        category !== ACCOUNT_CATEGORIES.SALE &&
        category !== ACCOUNT_CATEGORIES.BILLS_RECEIVABLE &&
        category !== ACCOUNT_CATEGORIES.INCOME_RECEIVABLE
    )
    await Promise.all(categories.map((category) => fetchAccounts(category, force)))
  }

  async function createAccount(payload) {
    try {
      const response = await submitData(payload)
      const created = response?.data
      if (created && created.category !== ACCOUNT_CATEGORIES.MAIN) {
        accounts.value.push(created)
        loadedCategories.value.add(created.category)
      } else if (!created && payload.category !== ACCOUNT_CATEGORIES.MAIN) {
        await fetchAccounts(payload.category, true)
      }

      const openingBalance = Number(
        created?.opening_balance ?? payload.opening_balance ?? created?.balance ?? payload.balance ?? 0
      )
      const partyOpeningAmount = Number(payload.opening_amount ?? 0)
      if (openingBalance > 0 || partyOpeningAmount > 0) {
        const { useAccountTransactionStore } = await import('./accountTransactionStore')
        await useAccountTransactionStore().fetchTransactions(true)
      }

      return { ok: true, account: created }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to create account.') }
    }
  }

  async function updateAccount(payload) {
    try {
      await updateData(payload)
      if (payload.category !== ACCOUNT_CATEGORIES.MAIN) {
        await fetchAccounts(payload.category, true)
      }

      if (Number(payload.opening_amount) > 0) {
        const { useAccountTransactionStore } = await import('./accountTransactionStore')
        await useAccountTransactionStore().fetchTransactions(true)
      }

      return { ok: true }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to update account.') }
    }
  }

  async function deleteAccount(id, category = null) {
    try {
      await deleteItem(id)
      accounts.value = accounts.value.filter((account) => Number(account.id) !== Number(id))
      if (category) {
        await fetchAccounts(category, true)
      }
      return { ok: true }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to delete account.') }
    }
  }

  async function deleteAccounts(ids, category = null) {
    try {
      await bulkDelete(ids)
      const idSet = new Set(ids.map((id) => Number(id)))
      accounts.value = accounts.value.filter((account) => !idSet.has(Number(account.id)))
      if (category) {
        await fetchAccounts(category, true)
      }
      return { ok: true }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to delete accounts.') }
    }
  }

  async function fetchAccountById(id) {
    const { data, error } = await fetchOne(id)
    if (error) throw error
    return data?.data ?? null
  }

  async function fetchCategorySummary(category) {
    const { data, error } = await fetchSummary(category)
    if (error) throw error
    return data?.data ?? null
  }

  async function fetchAccountsPage(category, page, perPage, filters = {}) {
    const { data, error } = await fetchAll(page, perPage, {
      category,
      ...filters,
    })
    if (error) throw error
    return {
      rows: data?.data ?? [],
      meta: data?.meta ?? { total: 0, to: 0, links: [] },
    }
  }

  const mainAccounts = computed(() => getAccountsByCategory(ACCOUNT_CATEGORIES.MAIN))

  const mainSummary = computed(() => {
    const rows = mainAccounts.value
    const activeAccounts = rows.filter((account) => account.status === 'Active').length

    return {
      totalAccounts: rows.length,
      activeAccounts,
      inactiveAccounts: rows.length - activeAccounts,
      totalCurrentBalance: rows.reduce(
        (sum, account) => sum + (Number(account.current_balance) || 0),
        0
      ),
      thisMonthTransactions: 0,
    }
  })

  return {
    accounts,
    isLoading,
    isCategoryLoading,
    loadedCategories,
    mainAccounts,
    mainSummary,
    getAccountsByCategory,
    getAccount,
    getAccountByCategory,
    fetchAccounts,
    fetchAllCategories,
    createAccount,
    updateAccount,
    deleteAccount,
    deleteAccounts,
    getApiErrorMessage,
    fetchAccountById,
    fetchCategorySummary,
    fetchAccountsPage,
  }
})
