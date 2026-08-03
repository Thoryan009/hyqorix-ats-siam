import { defineStore } from 'pinia'
import { ref, watch } from 'vue'
import {
  buildExpenseCostLedgerKey,
  parseExpenseCostLedgerKey,
} from '../config/expenseCostAccountConfigs'
import { computeLedgerBalances } from '../data/agentLedgerData'
import {
  financeStorageKeys,
  loadFinanceJson,
  saveFinanceJson,
} from '../utils/financeStorage'
import { useExpenseCostAccountsStore } from './expenseCostAccountsStore'

function resolveEntryDate(date) {
  if (date && String(date).trim()) {
    return String(date).slice(0, 10)
  }

  return new Date().toISOString().slice(0, 10)
}

function normalizeLedgerEntry(entry) {
  return {
    ...entry,
    date: resolveEntryDate(entry?.date),
  }
}

function normalizeLoadedEntriesMap(data) {
  if (!data || typeof data !== 'object' || Array.isArray(data)) {
    return {}
  }

  return Object.entries(data).reduce((acc, [key, entries]) => {
    if (Array.isArray(entries)) {
      acc[key] = entries.map((entry) => normalizeLedgerEntry(entry))
    }
    return acc
  }, {})
}

export const useExpenseCostLedgerStore = defineStore('expenseCostLedger', () => {
  const entriesByAccount = ref(
    normalizeLoadedEntriesMap(loadFinanceJson(financeStorageKeys.expenseCostLedger, {}))
  )

  function persistEntries() {
    saveFinanceJson(financeStorageKeys.expenseCostLedger, entriesByAccount.value)
  }

  watch(
    entriesByAccount,
    (value) => {
      saveFinanceJson(financeStorageKeys.expenseCostLedger, value)
    },
    { deep: true }
  )

  function ensureAccountEntries(costType, accountId) {
    const key = buildExpenseCostLedgerKey(costType, accountId)
    if (!entriesByAccount.value[key]) {
      entriesByAccount.value[key] = []
    }
    return entriesByAccount.value[key]
  }

  function getNextEntryId() {
    const allIds = Object.values(entriesByAccount.value)
      .flat()
      .map((entry) => Number(entry.id) || 0)

    return Math.max(...allIds, 0) + 1
  }

  function addEntry(costType, accountId, entry) {
    const entries = ensureAccountEntries(costType, accountId)
    entries.push(
      normalizeLedgerEntry({
        id: getNextEntryId(),
        demand_letter: '',
        job: '',
        client_name: '',
        dr_amount: 0,
        discount: 0,
        cr_amount: 0,
        payment_method: '',
        remarks: '',
        bill_id: null,
        bill_status: '',
        ...entry,
      })
    )
    syncAccountBalance(costType, accountId)
    persistEntries()
  }

  function getLedgerEntries(costType, accountId) {
    const key = buildExpenseCostLedgerKey(costType, accountId)
    const entries = entriesByAccount.value[key] ?? []
    return computeLedgerBalances(entries)
  }

  function syncAccountBalance(costType, accountId) {
    const entries = getLedgerEntries(costType, accountId)
    const accountStore = useExpenseCostAccountsStore()
    const account = accountStore.getAccount(costType, accountId)

    if (!account) return

    account.balance = entries.length ? Number(entries[entries.length - 1].balance) : 0
  }

  function addOpeningBalanceEntry(costType, accountId, { amount, date, remarks = '' } = {}) {
    const openingAmount = Number(amount) || 0
    if (openingAmount === 0) return

    addEntry(costType, accountId, {
      date,
      particular: 'Opening Balance',
      voucher_no: `OB-${String(accountId).padStart(3, '0')}`,
      dr_amount: 0,
      discount: 0,
      cr_amount: openingAmount,
      payment_method: '',
      remarks: remarks || 'Opening balance forwarded',
      bill_id: null,
      bill_status: '',
    })
  }

  function addBillEntry(
    costType,
    accountId,
    {
      billId,
      amount,
      particular,
      voucherNo = '',
      demandLetter = '',
      job = '',
      clientName = '',
      paymentMethod = '',
      date,
      remarks = '',
      billStatus = 'pending',
    } = {}
  ) {
    addEntry(costType, accountId, {
      date,
      particular: particular || 'Bill Entry',
      voucher_no: voucherNo,
      demand_letter: demandLetter,
      job,
      client_name: clientName,
      dr_amount: Number(amount) || 0,
      discount: 0,
      cr_amount: 0,
      payment_method: paymentMethod,
      remarks,
      bill_id: billId,
      bill_status: billStatus,
    })
  }

  function updateBillEntryStatus(costType, accountId, billId, billStatus, remarks = '') {
    const entries = ensureAccountEntries(costType, accountId)
    const entry = entries.find((item) => Number(item.bill_id) === Number(billId))

    if (!entry) return false

    entry.bill_status = billStatus
    if (remarks) {
      entry.remarks = remarks
    }

    syncAccountBalance(costType, accountId)
    persistEntries()
    return true
  }

  function removeBillEntry(costType, accountId, billId) {
    const key = buildExpenseCostLedgerKey(costType, accountId)
    const entries = entriesByAccount.value[key] ?? []
    const nextEntries = entries.filter((entry) => Number(entry.bill_id) !== Number(billId))

    if (nextEntries.length === entries.length) {
      return false
    }

    entriesByAccount.value[key] = nextEntries
    syncAccountBalance(costType, accountId)
    persistEntries()
    return true
  }

  return {
    entriesByAccount,
    getLedgerEntries,
    addOpeningBalanceEntry,
    addBillEntry,
    updateBillEntryStatus,
    removeBillEntry,
    syncAccountBalance,
    parseExpenseCostLedgerKey,
  }
})
