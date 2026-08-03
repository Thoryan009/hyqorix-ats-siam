import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'
import { ACCOUNT_CATEGORIES } from '../data/accountCategoryCodes'
import { financeStorageKeys, loadFinanceJson, saveFinanceJson } from '../utils/financeStorage'
import { useAccountLedgerStore } from './accountLedgerStore'
import { useFinanceAccountStore } from './financeAccountStore'
import {
  depositFunds,
  transferFunds,
  withdrawFunds,
} from '../services/financeAccountService'

const accountTypeIcons = {
  Cash: 'fa fa-money',
  Bank: 'fa fa-university',
}

export const useAccountStore = defineStore('paymentAccount', () => {
  const { item, isModal, isEditModal, handleToggleModal, handleReset } = useModalHelpers()
  const financeAccountStore = useFinanceAccountStore()

  const moduleName = 'Account'
  const mainAccounts = ref([])
  const activeAccounts = ref([])
  const accountByIdCache = ref({})
  const paginationMeta = ref({ total: 0, to: 0, links: [] })
  const summaryData = ref({
    total_accounts: 0,
    active_accounts: 0,
    inactive_accounts: 0,
    total_current_balance: 0,
  })
  const isLoading = ref(false)
  const isLoadingActiveAccounts = ref(false)
  const lastFetchParams = ref({ page: 1, perPage: 10, filters: {} })
  const transactions = ref(loadFinanceJson(financeStorageKeys.accountTransactions, []))

  const accounts = computed(() => mainAccounts.value)

  let nextTransactionId =
    Math.max(...transactions.value.map((item) => Number(item.id) || 0), 0) + 1

  const summary = computed(() => ({
    totalAccounts: summaryData.value.total_accounts ?? 0,
    activeAccounts: summaryData.value.active_accounts ?? 0,
    inactiveAccounts: summaryData.value.inactive_accounts ?? 0,
    totalCurrentBalance: summaryData.value.total_current_balance ?? 0,
    thisMonthTransactions: 0,
  }))

  function cacheAccounts(rows = []) {
    rows.forEach((account) => {
      accountByIdCache.value[Number(account.id)] = account
    })
  }

  function buildApiFilters(filters = {}) {
    const status =
      filters.status && filters.status !== 'all' ? filters.status : undefined

    return {
      ...(filters.search?.trim() ? { search: filters.search.trim() } : {}),
      ...(filters.accountType ? { account_type: filters.accountType } : {}),
      ...(status ? { status } : {}),
    }
  }

  async function fetchAccountsPage(page = 1, perPage = 10, filters = {}) {
    isLoading.value = true
    lastFetchParams.value = { page, perPage, filters }

    try {
      const { rows, meta } = await financeAccountStore.fetchAccountsPage(
        ACCOUNT_CATEGORIES.MAIN,
        page,
        perPage,
        buildApiFilters(filters)
      )

      mainAccounts.value = rows
      paginationMeta.value = meta
      cacheAccounts(rows)
    } finally {
      isLoading.value = false
    }
  }

  async function fetchSummary() {
    const data = await financeAccountStore.fetchCategorySummary(ACCOUNT_CATEGORIES.MAIN)
    if (data) {
      summaryData.value = data
    }
  }

  async function fetchActiveAccounts(force = false) {
    if ((activeAccounts.value.length && !force) || isLoadingActiveAccounts.value) return

    isLoadingActiveAccounts.value = true

    try {
      const { rows } = await financeAccountStore.fetchAccountsPage(
        ACCOUNT_CATEGORIES.MAIN,
        1,
        300,
        { status: 'Active' }
      )

      activeAccounts.value = rows
      cacheAccounts(rows)
    } finally {
      isLoadingActiveAccounts.value = false
    }
  }

  async function refreshAccounts() {
    const { page, perPage, filters } = lastFetchParams.value
    await Promise.all([
      fetchAccountsPage(page, perPage, filters),
      fetchSummary(),
      fetchActiveAccounts(true),
    ])
  }

  async function fetchAccounts(force = false) {
    const { page, perPage, filters } = lastFetchParams.value
    await fetchAccountsPage(page, perPage, filters)
    if (force) {
      await Promise.all([fetchSummary(), fetchActiveAccounts(true)])
    }
  }

  async function ensureAccount(accountId) {
    const id = Number(accountId)
    if (!id) return null

    const cached = accountByIdCache.value[id]
    if (cached) return cached

    try {
      const account = await financeAccountStore.fetchAccountById(id)
      if (account) {
        accountByIdCache.value[id] = account
      }
      return account
    } catch {
      return null
    }
  }

  watch(
    transactions,
    (value) => {
      saveFinanceJson(financeStorageKeys.accountTransactions, value)
    },
    { deep: true },
  )

  function getAccountLabel(accountId) {
    const account = getAccount(accountId)
    if (!account) return ''
    return `${account.account_name} - ${account.account_label}`
  }

  function recordTransaction(payload) {
    transactions.value.unshift({
      id: nextTransactionId++,
      type: payload.type,
      account_id: payload.accountId ? Number(payload.accountId) : null,
      from_account_id: payload.fromAccountId ? Number(payload.fromAccountId) : null,
      to_account_id: payload.toAccountId ? Number(payload.toAccountId) : null,
      amount: Number(payload.amount) || 0,
      particular: payload.particular || '',
      reference_no: payload.referenceNo || '',
      date: payload.date || new Date().toISOString().slice(0, 10),
      remarks: payload.remarks || '',
      created_at: new Date().toISOString(),
    })
  }

  function getActiveAccounts() {
    if (!activeAccounts.value.length && !isLoadingActiveAccounts.value) {
      fetchActiveAccounts()
    }

    return activeAccounts.value
  }

  function getMovementErrorMessage(error, fallback = 'Request failed.') {
    return (
      Object.values(error?.errors || {})?.flat()?.[0] ||
      error?.message ||
      fallback
    )
  }

  async function transferBetweenAccounts(payload) {
    try {
      await transferFunds(payload)
      await refreshAccounts()
      useAccountLedgerStore().invalidateAccountLedger(Number(payload.fromAccountId))
      useAccountLedgerStore().invalidateAccountLedger(Number(payload.toAccountId))
      const { useAccountTransactionStore } = await import('./accountTransactionStore')
      await useAccountTransactionStore().fetchTransactions(true)
      return { ok: true }
    } catch (error) {
      return { ok: false, message: getMovementErrorMessage(error, 'Transfer failed.') }
    }
  }

  async function depositToAccount(payload) {
    try {
      await depositFunds(payload)
      await refreshAccounts()
      useAccountLedgerStore().invalidateAccountLedger(Number(payload.fromAccountId))
      useAccountLedgerStore().invalidateAccountLedger(Number(payload.toAccountId))
      const { useAccountTransactionStore } = await import('./accountTransactionStore')
      await useAccountTransactionStore().fetchTransactions(true)
      return { ok: true }
    } catch (error) {
      return { ok: false, message: getMovementErrorMessage(error, 'Deposit failed.') }
    }
  }

  async function withdrawFromAccount(payload) {
    try {
      await withdrawFunds(payload)
      await refreshAccounts()
      useAccountLedgerStore().invalidateAccountLedger(Number(payload.fromAccountId))
      useAccountLedgerStore().invalidateAccountLedger(Number(payload.toAccountId))
      const { useAccountTransactionStore } = await import('./accountTransactionStore')
      await useAccountTransactionStore().fetchTransactions(true)
      return { ok: true }
    } catch (error) {
      return { ok: false, message: getMovementErrorMessage(error, 'Withdraw failed.') }
    }
  }

  function getIconForType(accountType) {
    return accountTypeIcons[accountType] || 'fa fa-bank'
  }

  function getAccount(accountId) {
    return accountByIdCache.value[Number(accountId)] ?? null
  }

  function findMutableAccount(accountId) {
    const id = Number(accountId)
    return (
      mainAccounts.value.find((account) => Number(account.id) === id) ??
      activeAccounts.value.find((account) => Number(account.id) === id) ??
      accountByIdCache.value[id] ??
      null
    )
  }

  function updateBalance(accountId, amountDelta) {
    const account = findMutableAccount(accountId)
    if (!account) return null

    const nextBalance = Number(account.current_balance ?? account.balance) + Number(amountDelta)
    account.balance = nextBalance
    account.current_balance = nextBalance
    return nextBalance
  }

  function creditAccount(accountId, amount) {
    return updateBalance(accountId, Number(amount))
  }

  function debitAccount(accountId, amount) {
    return updateBalance(accountId, -Number(amount))
  }

  async function addAccount(payload) {
    const openingBalance = Number(payload.current_balance) || 0

    const result = await financeAccountStore.createAccount({
      category: ACCOUNT_CATEGORIES.MAIN,
      account_name: payload.account_name,
      account_label: payload.account_label,
      account_type: payload.account_type,
      bank_id: payload.account_type === 'Bank' ? Number(payload.bank_id) || null : null,
      icon: getIconForType(payload.account_type),
      balance: openingBalance,
      opening_balance: openingBalance,
      status: payload.status ?? 'Active',
    })

    if (result.ok) {
      await refreshAccounts()
    }

    return result
  }

  async function updateAccount(payload) {
    const updatePayload = {
      id: payload.id,
      category: ACCOUNT_CATEGORIES.MAIN,
      account_name: payload.account_name,
      account_label: payload.account_label,
      account_type: payload.account_type,
      bank_id: payload.account_type === 'Bank' ? Number(payload.bank_id) || null : null,
      icon: getIconForType(payload.account_type),
      status: payload.status ?? 'Active',
    }

    if (Number(payload.current_balance) <= 0) {
      updatePayload.balance = Number(payload.current_balance) || 0
    }

    const result = await financeAccountStore.updateAccount(updatePayload)

    if (result.ok) {
      await refreshAccounts()
    }

    return result
  }

  async function deleteAccount(id) {
    const result = await financeAccountStore.deleteAccount(id)
    if (result.ok) {
      await refreshAccounts()
    }
    return result
  }

  async function deleteAccounts(ids) {
    const result = await financeAccountStore.deleteAccounts(ids)
    if (result.ok) {
      await refreshAccounts()
    }
    return result
  }

  return {
    item,
    isModal,
    isEditModal,
    moduleName,
    accounts,
    transactions,
    summary,
    paginationMeta,
    isLoading,
    handleToggleModal,
    handleReset,
    fetchAccounts,
    fetchAccountsPage,
    fetchSummary,
    fetchActiveAccounts,
    ensureAccount,
    refreshAccounts,
    getAccount,
    getActiveAccounts,
    getAccountLabel,
    creditAccount,
    debitAccount,
    updateBalance,
    transferBetweenAccounts,
    depositToAccount,
    withdrawFromAccount,
    addAccount,
    updateAccount,
    deleteAccount,
    deleteAccounts,
  }
})
