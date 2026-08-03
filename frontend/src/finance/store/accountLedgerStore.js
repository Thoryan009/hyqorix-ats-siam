import { defineStore } from 'pinia'
import { ref, watch } from 'vue'
import { accountLedgerEntries } from '../data/accountLedgerData'
import { computeLedgerBalances } from '../data/accountLedgerData'
import { fetchLedger } from '../services/financeAccountService'
import {
  financeStorageKeys,
  loadFinanceJson,
  normalizeAgentEntriesMap,
  saveFinanceJson,
} from '../utils/financeStorage'

function getYearSuffix(date = new Date()) {
  return String(date.getFullYear()).slice(-2)
}

function countEntriesByPrefix(entries, prefix, yearSuffix) {
  return entries.filter((entry) => {
    const voucher = String(entry.voucher_no || '')
    return voucher.startsWith(`${prefix}-`) && voucher.endsWith(`/${yearSuffix}`)
  }).length
}

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

function resolveAccountKey(accountId) {
  return String(Number(accountId))
}

function normalizeLoadedEntriesMap(data) {
  return Object.entries(normalizeAgentEntriesMap(data)).reduce((acc, [key, entries]) => {
    acc[key] = entries.map((entry) => normalizeLedgerEntry(entry))
    return acc
  }, {})
}

function isOpeningLedgerParticular(particular = '') {
  const value = String(particular || '').trim()
  if (!value) return false
  return (
    value === 'Opening Balance' ||
    value === 'Opening Receivable' ||
    value === 'Opening Payable' ||
    value.startsWith('Opening Balance')
  )
}

function sortLedgerEntries(entries = []) {
  return [...entries].sort((a, b) => {
    const openingA = isOpeningLedgerParticular(a.particular) ? 0 : 1
    const openingB = isOpeningLedgerParticular(b.particular) ? 0 : 1
    if (openingA !== openingB) return openingA - openingB

    const dateA = String(a.date || a.entry_date || '').slice(0, 10)
    const dateB = String(b.date || b.entry_date || '').slice(0, 10)
    if (dateA !== dateB) return dateA.localeCompare(dateB)

    return Number(a.id || 0) - Number(b.id || 0)
  })
}

export const useAccountLedgerStore = defineStore('accountLedger', () => {
  const entriesByAccount = ref(
    normalizeLoadedEntriesMap(
      loadFinanceJson(financeStorageKeys.accountLedger, accountLedgerEntries)
    )
  )
  const apiEntriesByAccount = ref({})
  const isLoadingLedger = ref(false)

  watch(
    entriesByAccount,
    (value) => {
      saveFinanceJson(financeStorageKeys.accountLedger, value)
    },
    { deep: true }
  )

  function ensureAccountEntries(accountId) {
    const id = resolveAccountKey(accountId)
    if (!entriesByAccount.value[id]) {
      entriesByAccount.value[id] = []
    }
    return entriesByAccount.value[id]
  }

  function getNextEntryId() {
    const allIds = Object.values(entriesByAccount.value)
      .flat()
      .map((entry) => Number(entry.id) || 0)

    return Math.max(...allIds, 0) + 1
  }

  function nextVoucherNo(accountId, prefix, date = new Date()) {
    const yearSuffix = getYearSuffix(date)
    const entries = ensureAccountEntries(accountId)
    const count = countEntriesByPrefix(entries, prefix, yearSuffix) + 1
    return `${prefix}-${String(count).padStart(3, '0')}/${yearSuffix}`
  }

  function addEntry(accountId, entry) {
    const entries = ensureAccountEntries(accountId)
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
        ...entry,
      })
    )
  }

  function getAccountLedgerEntries(accountId) {
    const key = resolveAccountKey(accountId)
    const apiRows = apiEntriesByAccount.value[key]

    if (apiRows) {
      return computeLedgerBalances(sortLedgerEntries(apiRows))
    }

    const entries = entriesByAccount.value[key] ?? []
    return computeLedgerBalances(sortLedgerEntries(entries))
  }

  function invalidateAccountLedger(accountId) {
    const key = resolveAccountKey(accountId)
    delete apiEntriesByAccount.value[key]
  }

  async function fetchAccountLedger(accountId, filters = {}) {
    isLoadingLedger.value = true

    try {
      const { data, error } = await fetchLedger(accountId, 1, 500, filters)
      if (error) throw error

      const rows = sortLedgerEntries(data?.data ?? [])
      apiEntriesByAccount.value[resolveAccountKey(accountId)] = rows
      return computeLedgerBalances(rows)
    } finally {
      isLoadingLedger.value = false
    }
  }

  function addOpeningBalanceEntry(accountId, { amount, date, remarks = '' } = {}) {
    addEntry(accountId, {
      date,
      particular: 'Opening Balance',
      voucher_no: nextVoucherNo(accountId, 'OB', date ? new Date(date) : new Date()),
      dr_amount: 0,
      discount: 0,
      cr_amount: Number(amount) || 0,
      payment_method: '',
      remarks: remarks || 'Opening balance forwarded',
    })
  }

  function addReceiptEntry(
    accountId,
    {
      amount,
      particular,
      voucherNo = '',
      demandLetter = '',
      job = '',
      clientName = '',
      paymentMethod = 'Cash',
      discount = 0,
      date,
      remarks = '',
    } = {}
  ) {
    addEntry(accountId, {
      date,
      particular: particular || 'Payment Received',
      voucher_no: voucherNo || nextVoucherNo(accountId, 'RC', date ? new Date(date) : new Date()),
      demand_letter: demandLetter,
      job,
      client_name: clientName,
      dr_amount: 0,
      discount: Number(discount) || 0,
      cr_amount: Number(amount) || 0,
      payment_method: paymentMethod,
      remarks,
    })
  }

  function addPaymentEntry(
    accountId,
    {
      amount,
      particular,
      voucherNo = '',
      demandLetter = '',
      job = '',
      clientName = '',
      paymentMethod = 'Cash',
      discount = 0,
      date,
      remarks = '',
    } = {}
  ) {
    const resolvedVoucher =
      voucherNo || nextVoucherNo(accountId, 'PY', date ? new Date(date) : new Date())

    addEntry(accountId, {
      date,
      particular: particular || 'Payment Made',
      voucher_no: resolvedVoucher,
      demand_letter: demandLetter,
      job,
      client_name: clientName,
      dr_amount: Number(amount) || 0,
      discount: Number(discount) || 0,
      cr_amount: 0,
      payment_method: paymentMethod,
      remarks,
    })

    return resolvedVoucher
  }

  function recordTransfer(
    fromAccountId,
    toAccountId,
    {
      amount,
      particular = 'Account Transfer',
      voucherNo = '',
      date,
      remarks = '',
      fromLabel = '',
      toLabel = '',
    } = {}
  ) {
    const resolvedDate = date ? new Date(date) : new Date()
    const transferVoucher =
      voucherNo || nextVoucherNo(fromAccountId, 'TR', resolvedDate)

    addPaymentEntry(fromAccountId, {
      amount,
      particular,
      voucherNo: transferVoucher,
      clientName: toLabel,
      paymentMethod: 'Transfer',
      date,
      remarks: remarks || (toLabel ? `Transfer to ${toLabel}` : 'Account transfer out'),
    })

    addReceiptEntry(toAccountId, {
      amount,
      particular,
      voucherNo: transferVoucher,
      clientName: fromLabel,
      paymentMethod: 'Transfer',
      date,
      remarks: remarks || (fromLabel ? `Transfer from ${fromLabel}` : 'Account transfer in'),
    })

    return transferVoucher
  }

  return {
    entriesByAccount,
    apiEntriesByAccount,
    isLoadingLedger,
    getAccountLedgerEntries,
    fetchAccountLedger,
    invalidateAccountLedger,
    addOpeningBalanceEntry,
    addReceiptEntry,
    addPaymentEntry,
    recordTransfer,
  }
})
