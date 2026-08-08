import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import {
  getAccountCategoryLabel,
  getTransactionTypeLabel,
  isAdjustmentTransactionType,
} from '../data/accountTransactionData'
import { fetchAll, fetchSummary, submitData } from '../services/accountTypeTransactionService'
import {
  buildAccountTypeTransactionPayload,
  extractAccountTypeTransactionRow,
  extractAccountTypeTransactionRows,
  mapAccountTypeTransactionFromApi,
  mapAccountTypeTransactionsFromApi,
} from '../utils/accountTypeTransactionMapper'
import { extractPaginatedRows } from '@/shared/utils/extractPaginatedRows'
import { useFinanceAccountStore } from './financeAccountStore'
import { useAccountStore } from './accountStore'
import { useAccountLedgerStore } from './accountLedgerStore'
import { getPartyConfig } from '../config/partyAccountConfigs'

export const useAccountTransactionStore = defineStore('accountTransaction', () => {
  const transactions = ref([])
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
  })
  const lastFetchParams = ref({
    page: 1,
    perPage: 10,
    filters: {},
  })

  const financeAccountStore = useFinanceAccountStore()

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

  async function fetchTransactionSummary(filters = lastFetchParams.value.filters) {
    try {
      const { data, error } = await fetchSummary(filters)
      if (error) throw error
      const payload = data?.data ?? {}
      summaryData.value = {
        total_count: Number(payload.total_count) || 0,
        this_month_count: Number(payload.this_month_count) || 0,
      }
    } catch {
      // Keep prior summary on failure.
    }
  }

  async function fetchTransactions(options = true) {
    const { force, page, perPage, filters } = normalizeFetchOptions(options)

    if (isLoading.value) return
    if (isLoaded.value && !force) return

    isLoading.value = true
    lastFetchParams.value = { page, perPage, filters }

    try {
      const [{ data, error }] = await Promise.all([
        fetchAll(page, perPage, filters),
        fetchTransactionSummary(filters),
      ])
      if (error) throw error

      const rows = mapAccountTypeTransactionsFromApi(extractPaginatedRows(data))
      transactions.value = rows
      paginationMeta.value = extractPaginationMeta(data, page, perPage, rows.length)
      isLoaded.value = true
    } catch {
      transactions.value = []
      paginationMeta.value = extractPaginationMeta(null, page, perPage, 0)
    } finally {
      isLoading.value = false
    }
  }

  const totalTransactionCount = computed(() => summaryData.value.total_count)

  const thisMonthTransactionCount = computed(() => summaryData.value.this_month_count)

  function getAccountBalance(category, accountId) {
    if (!category || !accountId) return null

    const account =
      financeAccountStore.getAccountByCategory(category, accountId) ??
      financeAccountStore.getAccount(accountId)

    if (!account) return null

    return Number(account.balance ?? account.current_balance ?? 0)
  }

  function resolveAccountLabel(category, accountId) {
    if (!category || !accountId) return ''

    const account =
      financeAccountStore.getAccountByCategory(category, accountId) ??
      financeAccountStore.getAccount(accountId)

    if (!account) return ''

    if (category === 'main') {
      return `${account.account_name} - ${account.account_label}`
    }

    if (category === 'agent') {
      return `${account.agent_code} — ${account.agent_name}`
    }

    const config = getPartyConfig(category)
    if (!config) return account.account_name ?? ''

    return `${account[config.codeKey]} — ${account[config.nameKey]}`
  }

  function isAccountActive(category, accountId) {
    if (!category || !accountId) return false

    const account =
      financeAccountStore.getAccountByCategory(category, accountId) ??
      financeAccountStore.getAccount(accountId)

    return Boolean(account && account.status === 'Active')
  }

  async function refreshAffectedAccounts(payload) {
    const accountLedgerStore = useAccountLedgerStore()
    const categories = new Set()

    if (isAdjustmentTransactionType(payload.transactionType)) {
      categories.add(payload.accountCategory)
      accountLedgerStore.invalidateAccountLedger(Number(payload.accountId))
    } else {
      categories.add(payload.fromAccountCategory)
      categories.add(payload.toAccountCategory)
      accountLedgerStore.invalidateAccountLedger(Number(payload.fromAccountId))
      accountLedgerStore.invalidateAccountLedger(Number(payload.toAccountId))

      if (payload.assetAccountId) {
        categories.add('asset')
        accountLedgerStore.invalidateAccountLedger(Number(payload.assetAccountId))
      }

      if (payload.liabilitiesAccountId) {
        categories.add('liabilities')
        accountLedgerStore.invalidateAccountLedger(Number(payload.liabilitiesAccountId))
      }

      if (payload.ownersEquityAccountId) {
        categories.add('owners_equity')
        accountLedgerStore.invalidateAccountLedger(Number(payload.ownersEquityAccountId))
      }
    }

    await Promise.all(
      [...categories].map((category) => financeAccountStore.fetchAccounts(category, true))
    )

    if (
      payload.accountCategory === 'main' ||
      payload.fromAccountCategory === 'main' ||
      payload.toAccountCategory === 'main'
    ) {
      await useAccountStore().fetchActiveAccounts(true)
    }
  }

  async function submitTransaction(payload) {
    const transactionType = payload.transactionType
    const amount = Number(payload.amount)
    const date = payload.date || new Date().toISOString().slice(0, 10)
    const particular = payload.particular?.trim() || getTransactionTypeLabel(transactionType)
    const referenceNo = payload.referenceNo?.trim() || ''
    const remarks = payload.remarks?.trim() || ''

    if (!transactionType) {
      return { ok: false, message: 'Please select a transaction type.' }
    }

    if (!date) {
      return { ok: false, message: 'Please select a transaction date.' }
    }

    if (!amount || amount <= 0) {
      return { ok: false, message: 'Please enter a valid amount.' }
    }

    if (isAdjustmentTransactionType(transactionType)) {
      const category = payload.accountCategory
      const accountId = Number(payload.accountId)

      if (!category || !accountId) {
        return { ok: false, message: 'Please select an account.' }
      }

      if (!isAccountActive(category, accountId)) {
        return { ok: false, message: 'Selected account is not active.' }
      }

      const delta = transactionType === 'adjust_minus' ? -amount : amount
      const balance = getAccountBalance(category, accountId)

      if (delta < 0 && balance !== null && balance < amount) {
        return { ok: false, message: 'Insufficient balance for adjustment.' }
      }
    } else {
      const fromCategory = payload.fromAccountCategory
      const toCategory = payload.toAccountCategory
      const fromAccountId = Number(payload.fromAccountId)
      const toAccountId = Number(payload.toAccountId)

      if (!fromCategory || !toCategory || !fromAccountId || !toAccountId) {
        return { ok: false, message: 'Please select both from and to accounts.' }
      }

      if (fromCategory === toCategory && fromAccountId === toAccountId) {
        return { ok: false, message: 'From and to accounts must be different.' }
      }

      if (!isAccountActive(fromCategory, fromAccountId) || !isAccountActive(toCategory, toAccountId)) {
        return { ok: false, message: 'Both accounts must be active.' }
      }
    }

    try {
      const response = await submitData(
        buildAccountTypeTransactionPayload({
          ...payload,
          particular,
          referenceNo,
          remarks,
          date,
        })
      )

      const saved = mapAccountTypeTransactionFromApi(extractAccountTypeTransactionRow(response))
      await fetchTransactions({
        force: true,
        page: lastFetchParams.value.page,
        perPage: lastFetchParams.value.perPage,
        filters: lastFetchParams.value.filters,
      })
      await refreshAffectedAccounts(payload)

      return { ok: true, transaction: saved }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to submit transaction.') }
    }
  }

  return {
    transactions,
    isLoading,
    isLoaded,
    paginationMeta,
    summaryData,
    lastFetchParams,
    totalTransactionCount,
    thisMonthTransactionCount,
    fetchTransactions,
    fetchTransactionSummary,
    getAccountBalance,
    resolveAccountLabel,
    getAccountCategoryLabel,
    getTransactionTypeLabel,
    submitTransaction,
  }
})
