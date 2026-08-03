import { defineStore } from 'pinia'
import { ref, watch } from 'vue'
import { agentLedgerEntries as initialLedgerEntries } from '../data/agentLedgerData'
import { computeLedgerBalances } from '../data/agentLedgerData'
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

function resolveAgentKey(agentId) {
  return String(Number(agentId))
}

function normalizeLoadedEntriesMap(data) {
  return Object.entries(normalizeAgentEntriesMap(data)).reduce((acc, [key, entries]) => {
    acc[key] = entries.map((entry) => normalizeLedgerEntry(entry))
    return acc
  }, {})
}

export const useAgentLedgerStore = defineStore('agentLedger', () => {
  const entriesByAgent = ref(
    normalizeLoadedEntriesMap(
      loadFinanceJson(financeStorageKeys.ledger, initialLedgerEntries)
    )
  )

  watch(
    entriesByAgent,
    (value) => {
      saveFinanceJson(financeStorageKeys.ledger, value)
    },
    { deep: true }
  )

  function ensureAgentEntries(agentId) {
    const id = resolveAgentKey(agentId)
    if (!entriesByAgent.value[id]) {
      entriesByAgent.value[id] = []
    }
    return entriesByAgent.value[id]
  }

  function getNextEntryId() {
    const allIds = Object.values(entriesByAgent.value)
      .flat()
      .map((entry) => Number(entry.id) || 0)

    return Math.max(...allIds, 0) + 1
  }

  function nextVoucherNo(agentId, prefix, date = new Date()) {
    const yearSuffix = getYearSuffix(date)
    const entries = ensureAgentEntries(agentId)
    const count = countEntriesByPrefix(entries, prefix, yearSuffix) + 1
    return `${prefix}-${String(count).padStart(3, '0')}/${yearSuffix}`
  }

  function addEntry(agentId, entry) {
    const entries = ensureAgentEntries(agentId)
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

  function getAgentLedgerEntries(agentId) {
    const entries = entriesByAgent.value[resolveAgentKey(agentId)] ?? []
    return computeLedgerBalances(entries)
  }

  function addDepositEntry(agentId, { amount, particular, paymentMethod = 'Cash', date, remarks = '' } = {}) {
    addEntry(agentId, {
      date,
      particular: particular || 'Advanced Deposit',
      voucher_no: nextVoucherNo(agentId, 'RC', date ? new Date(date) : new Date()),
      dr_amount: 0,
      discount: 0,
      cr_amount: Number(amount) || 0,
      payment_method: paymentMethod,
      remarks: remarks || 'Advance deposit received',
    })
  }

  function addLoanEntry(agentId, { amount, particular, date, remarks = '' } = {}) {
    addEntry(agentId, {
      date,
      particular: particular || 'Loan Provide',
      voucher_no: nextVoucherNo(agentId, 'LN', date ? new Date(date) : new Date()),
      dr_amount: Number(amount) || 0,
      discount: 0,
      cr_amount: 0,
      payment_method: '',
      remarks: remarks || 'Loan provided to agent',
    })
  }

  function addBillChargeEntry(
    agentId,
    {
      billNo,
      amount,
      job = '',
      clientName = '',
      demandLetter = '',
      candidateCount = 0,
      date,
      remarks = '',
    } = {}
  ) {
    addEntry(agentId, {
      date,
      particular: 'Deployment Charge',
      voucher_no: billNo,
      demand_letter: demandLetter,
      job,
      client_name: clientName,
      dr_amount: Number(amount) || 0,
      discount: 0,
      cr_amount: 0,
      payment_method: '',
      remarks:
        remarks ||
        (candidateCount
          ? `Bill generated for ${candidateCount} candidate${candidateCount === 1 ? '' : 's'}`
          : 'Bill generated'),
    })
  }

  function addSalePaymentEntry(
    agentId,
    {
      amount,
      particular,
      entryNo = '',
      demandLetter = '',
      job = '',
      clientName = '',
      paymentMethod = 'cash',
      discount = 0,
      referenceNo = '',
      date,
      remarks = '',
      payer = '',
    } = {}
  ) {
    const isBalance = paymentMethod === 'balance'

    addEntry(agentId, {
      date,
      particular: particular || (isBalance ? 'Payment Collection (Balance)' : 'Payment Collection (Cash)'),
      voucher_no: referenceNo || entryNo,
      demand_letter: demandLetter,
      job,
      client_name: clientName,
      dr_amount: isBalance ? Number(amount) || 0 : 0,
      discount: Number(discount) || 0,
      cr_amount: isBalance ? 0 : Number(amount) || 0,
      payment_method: isBalance ? 'Adjust from Balance' : 'Cash',
      remarks:
        remarks ||
        (payer ? `Payment collection by ${payer}` : entryNo ? `Payment collection ${entryNo}` : 'Payment collection received'),
    })
  }

  function addBillCollectionEntry(
    agentId,
    {
      amount,
      particular,
      billNos = [],
      demandLetter = '',
      job = '',
      clientName = '',
      paymentMethod = 'cash',
      discount = 0,
      referenceNo = '',
      date,
      remarks = '',
    } = {}
  ) {
    const isBalance = paymentMethod === 'balance'
    const billLabel = billNos.filter(Boolean).join(', ')

    addEntry(agentId, {
      date,
      particular: particular || (isBalance ? 'Bill Collection' : 'Partial Payment'),
      voucher_no: referenceNo || billLabel,
      demand_letter: demandLetter,
      job,
      client_name: clientName,
      dr_amount: isBalance ? Number(amount) || 0 : 0,
      discount: Number(discount) || 0,
      cr_amount: isBalance ? 0 : Number(amount) || 0,
      payment_method: isBalance ? 'Adjust from Balance' : 'Cash',
      remarks: remarks || (billLabel ? `Collection against ${billLabel}` : 'Bill collection received'),
    })
  }

  function resetLedgerEntries() {
    entriesByAgent.value = normalizeLoadedEntriesMap(structuredClone(initialLedgerEntries))
  }

  return {
    entriesByAgent,
    getAgentLedgerEntries,
    addDepositEntry,
    addLoanEntry,
    addBillChargeEntry,
    addBillCollectionEntry,
    addSalePaymentEntry,
    resetLedgerEntries,
  }
})
