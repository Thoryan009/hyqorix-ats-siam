import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import {
  getAccountCategoryLabel,
  getTransactionTypeLabel,
  isAdjustmentTransactionType,
} from '../data/accountTransactionData'
import { fetchAll, submitData } from '../services/accountTypeTransactionService'
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

  const financeAccountStore = useFinanceAccountStore()

  function getApiErrorMessage(error, fallback = 'Request failed.') {
    return (
      Object.values(error?.errors || {})?.flat()?.[0] ||
      error?.message ||
      fallback
    )
  }

  async function fetchTransactions(force = false) {
    if (isLoading.value) return
    if (isLoaded.value && !force) return

    isLoading.value = true

    try {
      const { data, error } = await fetchAll(1, 500, {})
      if (error) throw error

      transactions.value = mapAccountTypeTransactionsFromApi(extractPaginatedRows(data))
      isLoaded.value = true
    } catch {
      transactions.value = []
    } finally {
      isLoading.value = false
    }
  }

  const totalTransactionCount = computed(() => transactions.value.length)

  const thisMonthTransactionCount = computed(() => {
    const now = new Date()
    return transactions.value.filter((item) => {
      const entryDate = new Date(item.date || item.created_at)
      return (
        entryDate.getMonth() === now.getMonth() && entryDate.getFullYear() === now.getFullYear()
      )
    }).length
  })

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
      transactions.value.unshift(saved)
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
    totalTransactionCount,
    thisMonthTransactionCount,
    fetchTransactions,
    getAccountBalance,
    resolveAccountLabel,
    getAccountCategoryLabel,
    getTransactionTypeLabel,
    submitTransaction,
  }
})
