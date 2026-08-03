import { defineStore } from 'pinia'
import { ref, watch } from 'vue'
import {
  partyLedgerSeedByType,
  partyLedgerTypes,
  vendorLedgerEntries,
} from '../data/partyLedgerData'
import { computeLedgerBalances } from '../data/agentLedgerData'
import { getPartyConfig } from '../config/partyAccountConfigs'
import {
  financeStorageKeys,
  loadFinanceJson,
  normalizeAgentEntriesMap,
  saveFinanceJson,
} from '../utils/financeStorage'
import { usePartyAccountsStore } from './partyAccountsStore'

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

function normalizePartyTypeEntries(data = {}) {
  return Object.entries(normalizeAgentEntriesMap(data)).reduce((acc, [key, entries]) => {
    acc[key] = entries.map((entry) => normalizeLedgerEntry(entry))
    return acc
  }, {})
}

function buildEmptyPartyLedgerMap() {
  return partyLedgerTypes.reduce((acc, partyType) => {
    acc[partyType] = {}
    return acc
  }, {})
}

function loadInitialPartyLedgerMap() {
  const stored = loadFinanceJson(financeStorageKeys.partyLedger, null)

  if (stored && typeof stored === 'object' && !Array.isArray(stored)) {
    const map = buildEmptyPartyLedgerMap()
    partyLedgerTypes.forEach((partyType) => {
      map[partyType] = normalizePartyTypeEntries(stored[partyType] ?? partyLedgerSeedByType[partyType])
    })
    return map
  }

  const legacyVendor = loadFinanceJson(financeStorageKeys.vendorLedger, vendorLedgerEntries)
  return partyLedgerTypes.reduce((acc, partyType) => {
    if (partyType === 'vendor') {
      acc.vendor = normalizePartyTypeEntries(legacyVendor)
    } else {
      acc[partyType] = normalizePartyTypeEntries(partyLedgerSeedByType[partyType])
    }
    return acc
  }, {})
}

export function isPartyLedgerType(partyType) {
  return partyLedgerTypes.includes(partyType)
}

export const usePartyLedgerStore = defineStore('partyLedger', () => {
  const entriesByParty = ref(loadInitialPartyLedgerMap())

  watch(
    entriesByParty,
    (value) => {
      saveFinanceJson(financeStorageKeys.partyLedger, value)
    },
    { deep: true }
  )

  function ensurePartyEntries(partyType, accountId) {
    if (!isPartyLedgerType(partyType)) return []

    if (!entriesByParty.value[partyType]) {
      entriesByParty.value[partyType] = {}
    }

    const id = resolveAccountKey(accountId)
    if (!entriesByParty.value[partyType][id]) {
      entriesByParty.value[partyType][id] = []
    }

    return entriesByParty.value[partyType][id]
  }

  function getNextEntryId() {
    const allIds = partyLedgerTypes
      .flatMap((partyType) => Object.values(entriesByParty.value[partyType] ?? {}).flat())
      .map((entry) => Number(entry.id) || 0)

    return Math.max(...allIds, 0) + 1
  }

  function nextVoucherNo(partyType, accountId, prefix, date = new Date()) {
    const yearSuffix = getYearSuffix(date)
    const entries = ensurePartyEntries(partyType, accountId)
    const count = countEntriesByPrefix(entries, prefix, yearSuffix) + 1
    return `${prefix}-${String(count).padStart(3, '0')}/${yearSuffix}`
  }

  function syncPartyBalance(partyType, accountId) {
    const key = resolveAccountKey(accountId)
    const rawEntries = entriesByParty.value[partyType]?.[key]
    if (!rawEntries?.length) return

    const entries = getLedgerEntries(partyType, accountId)
    const account = usePartyAccountsStore().getAccount(partyType, accountId)

    if (!account) return

    account.balance = entries.length ? Number(entries[entries.length - 1].balance) : 0
  }

  function syncAllPartyBalances() {
    partyLedgerTypes.forEach((partyType) => {
      const accountsMap = entriesByParty.value[partyType] ?? {}
      Object.keys(accountsMap).forEach((accountId) => {
        syncPartyBalance(partyType, Number(accountId))
      })
    })
  }

  function addEntry(partyType, accountId, entry) {
    const entries = ensurePartyEntries(partyType, accountId)
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
    syncPartyBalance(partyType, accountId)
  }

  function getLedgerEntries(partyType, accountId) {
    const entries = entriesByParty.value[partyType]?.[resolveAccountKey(accountId)] ?? []
    return computeLedgerBalances(entries)
  }

  function addOpeningBalanceEntry(partyType, accountId, { amount, date, remarks = '' } = {}) {
    addEntry(partyType, accountId, {
      date,
      particular: 'Opening Balance',
      voucher_no: nextVoucherNo(partyType, accountId, 'OB', date ? new Date(date) : new Date()),
      dr_amount: 0,
      discount: 0,
      cr_amount: Number(amount) || 0,
      payment_method: '',
      remarks: remarks || 'Opening balance forwarded',
    })
  }

  function addBillEntry(
    partyType,
    accountId,
    {
      billId,
      billNo,
      amount,
      particular,
      demandLetter = '',
      job = '',
      clientName = '',
      paymentMethod = '',
      date,
      remarks = '',
      billStatus = 'pending',
    } = {}
  ) {
    const config = getPartyConfig(partyType)

    addEntry(partyType, accountId, {
      date,
      particular: particular || 'Bill Entry',
      voucher_no: billNo,
      demand_letter: demandLetter,
      job,
      client_name: clientName,
      dr_amount: Number(amount) || 0,
      discount: 0,
      cr_amount: 0,
      payment_method: paymentMethod,
      remarks: remarks || `Bill entry submitted to ${config?.partyLabel?.toLowerCase() ?? 'party'}`,
      bill_id: billId ?? null,
      bill_status: billStatus,
    })
  }

  function addPaymentEntry(
    partyType,
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
    const config = getPartyConfig(partyType)

    addEntry(partyType, accountId, {
      date,
      particular: particular || 'Payment Made',
      voucher_no:
        voucherNo || nextVoucherNo(partyType, accountId, 'PY', date ? new Date(date) : new Date()),
      demand_letter: demandLetter,
      job,
      client_name: clientName,
      dr_amount: 0,
      discount: Number(discount) || 0,
      cr_amount: Number(amount) || 0,
      payment_method: paymentMethod,
      remarks: remarks || `Payment to ${config?.partyLabel?.toLowerCase() ?? 'party'}`,
    })
  }

  function updateBillEntryStatus(partyType, accountId, billId, billStatus, remarks = '') {
    const entries = ensurePartyEntries(partyType, accountId)
    const entry = entries.find((item) => Number(item.bill_id) === Number(billId))

    if (!entry) return false

    entry.bill_status = billStatus
    if (remarks) {
      entry.remarks = remarks
    }

    syncPartyBalance(partyType, accountId)
    return true
  }

  function removeBillEntry(partyType, accountId, billId) {
    const partyEntries = entriesByParty.value[partyType]
    if (!partyEntries) return false

    const key = resolveAccountKey(accountId)
    const entries = partyEntries[key] ?? []
    const nextEntries = entries.filter((entry) => Number(entry.bill_id) !== Number(billId))

    if (nextEntries.length === entries.length) {
      return false
    }

    partyEntries[key] = nextEntries
    syncPartyBalance(partyType, accountId)
    return true
  }

  syncAllPartyBalances()

  return {
    entriesByParty,
    getLedgerEntries,
    addOpeningBalanceEntry,
    addBillEntry,
    addPaymentEntry,
    syncPartyBalance,
    syncAllPartyBalances,
    updateBillEntryStatus,
    removeBillEntry,
  }
})
