import { defineStore } from 'pinia'
import { ref, watch } from 'vue'
import { initialAgentBills } from '../data/agentBillData'
import { useAgentAccountStore } from './agentAccountStore'
import { financeStorageKeys, loadFinanceJson, saveFinanceJson } from '../utils/financeStorage'

export function getBillDueAmount(bill) {
  return Math.max(Number(bill.total_amount) - Number(bill.paid_amount || 0), 0)
}

export function getBillPaymentStatus(bill) {
  const due = getBillDueAmount(bill)
  if (due <= 0) return 'Paid'
  if (Number(bill.paid_amount || 0) > 0) return 'Partial'
  return 'Unpaid'
}

function hydrateBills(saved) {
  const list = Array.isArray(saved) ? saved : structuredClone(initialAgentBills)

  return list.map((bill) => {
    const hydrated = { ...bill }
    hydrated.status = getBillPaymentStatus(hydrated)
    return hydrated
  })
}

function getNextBillId(billList) {
  if (!billList.length) return 1
  return Math.max(...billList.map((bill) => Number(bill.id) || 0), 0) + 1
}

export const useAgentBillStore = defineStore('agentBill', () => {
  const bills = ref(hydrateBills(loadFinanceJson(financeStorageKeys.bills, initialAgentBills)))

  watch(
    bills,
    (value) => {
      saveFinanceJson(financeStorageKeys.bills, value)
    },
    { deep: true }
  )

  function syncBillStatus(bill) {
    bill.status = getBillPaymentStatus(bill)
  }

  function addBill(payload) {
    const bill = {
      paid_amount: 0,
      status: 'Unpaid',
      ...payload,
      id: getNextBillId(bills.value),
    }
    syncBillStatus(bill)
    bills.value.unshift(bill)
  }

  function getAgentCodeByBillAgentId(billAgentId) {
    const accountStore = useAgentAccountStore()
    const account = accountStore.accounts.find(
      (item) => item.bill_agent_id === Number(billAgentId)
    )
    return account?.agent_code ?? null
  }

  function getBillsByBillAgent(billAgentId) {
    const agentCode = getAgentCodeByBillAgentId(billAgentId)
    if (!agentCode) return []

    return bills.value.filter((bill) => bill.agent_code === agentCode)
  }

  function getPendingBillsByBillAgent(billAgentId) {
    return getBillsByBillAgent(billAgentId).filter((bill) => getBillDueAmount(bill) > 0)
  }

  function getAgentBalanceSummary(billAgentId, account) {
    const agentBills = getBillsByBillAgent(billAgentId)
    const totalBilled = agentBills.reduce((sum, bill) => sum + Number(bill.total_amount), 0)
    const totalPaid = agentBills.reduce((sum, bill) => sum + Number(bill.paid_amount || 0), 0)
    const totalDue = totalBilled - totalPaid

    return {
      opening_balance: Number(account?.opening_balance || 0),
      total_billed: totalBilled,
      total_paid: totalPaid,
      total_due: totalDue,
      current_balance: Number(account?.balance || 0),
    }
  }

  function applyBillCollection(billId, amount) {
    const bill = bills.value.find((item) => item.id === Number(billId))
    if (!bill) return null

    const due = getBillDueAmount(bill)
    const collectAmount = Math.min(Number(amount), due)
    bill.paid_amount = Number(bill.paid_amount || 0) + collectAmount
    syncBillStatus(bill)

    return { bill, collectAmount }
  }

  function markBillCollected(billId) {
    const bill = bills.value.find((item) => item.id === Number(billId))
    if (!bill) return

    bill.paid_amount = bill.total_amount
    syncBillStatus(bill)
  }

  function importBills(rows) {
    let importedCount = 0

    rows.forEach((payload) => {
      const bill = {
        paid_amount: Number(payload.paid_amount || 0),
        currency: payload.currency || 'BDT',
        status: payload.status || 'Unpaid',
        ...payload,
        id: getNextBillId(bills.value),
      }
      syncBillStatus(bill)
      bills.value.unshift(bill)
      importedCount += 1
    })

    return importedCount
  }

  return {
    bills,
    addBill,
    importBills,
    getBillsByBillAgent,
    getPendingBillsByBillAgent,
    getAgentBalanceSummary,
    applyBillCollection,
    markBillCollected,
    getBillDueAmount,
    getBillPaymentStatus,
  }
})
