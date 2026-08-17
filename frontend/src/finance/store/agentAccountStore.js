import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'
import { ACCOUNT_CATEGORIES } from '../data/accountCategoryCodes'
import { agentAccounts as initialAccounts, loadAgentAccounts } from '../data/agentAccountData'
import { useAgentMasterStore } from './agentMasterStore'
import { useAgentLedgerStore } from './agentLedgerStore'
import { useAccountStore } from './accountStore'
import { useAccountLedgerStore } from './accountLedgerStore'
import { jobs } from '../data/agentBillData'
import { financeStorageKeys, loadFinanceJson, saveFinanceJson } from '../utils/financeStorage'
import { useFinanceAccountStore } from './financeAccountStore'

const initialTransactions = [
  {
    id: 1,
    agent_id: 1,
    type: 'deposit',
    particular: 'Initial wallet deposit',
    amount: 125000,
    balance_after: 125000,
    created_at: '2025-03-01 10:30:00',
  },
  {
    id: 2,
    agent_id: 2,
    type: 'deposit',
    particular: 'Cash deposit via bank',
    amount: 100000,
    balance_after: 87500,
    created_at: '2025-03-05 14:15:00',
  },
]

export const useAgentAccountStore = defineStore('agentAccount', () => {
  const financeAccountStore = useFinanceAccountStore()
  const accounts = computed(() =>
    financeAccountStore.getAccountsByCategory(ACCOUNT_CATEGORIES.AGENT)
  )
  const transactions = ref(loadFinanceJson(financeStorageKeys.transactions, initialTransactions))
  const isCreateModalOpen = ref(false)
  const isEditModalOpen = ref(false)
  const editingAccount = ref(null)

  watch(
    transactions,
    (value) => {
      saveFinanceJson(financeStorageKeys.transactions, value)
    },
    { deep: true }
  )

  let nextTransactionId =
    Math.max(...transactions.value.map((item) => Number(item.id) || 0), 0) + 1

  async function fetchAccounts(force = false) {
    await financeAccountStore.fetchAccounts(ACCOUNT_CATEGORIES.AGENT, force)
  }

  function findMutableAccount(accountId) {
    return financeAccountStore.accounts.find(
      (account) =>
        account.category === ACCOUNT_CATEGORIES.AGENT &&
        Number(account.id) === Number(accountId)
    ) ?? null
  }

  function openCreateModal() {
    isCreateModalOpen.value = true
  }

  function closeCreateModal() {
    isCreateModalOpen.value = false
  }

  function openEditModal(account) {
    editingAccount.value = { ...account }
    isEditModalOpen.value = true
  }

  function closeEditModal() {
    isEditModalOpen.value = false
    editingAccount.value = null
  }

  async function updateAccount(accountId, payload = {}) {
    const account = getAccount(accountId)

    if (!account) {
      return { ok: false, message: 'Agent account was not found.' }
    }

    const accountName = String(
      payload.account_name || account.account_name || account.agent_name || ''
    ).trim()
    if (!accountName) {
      return { ok: false, message: 'Please enter an account name.' }
    }

    const status = payload.status ?? account.status
    if (!['Active', 'Inactive'].includes(status)) {
      return { ok: false, message: 'Please select a valid status.' }
    }

    return financeAccountStore.updateAccount({
      id: account.id,
      category: ACCOUNT_CATEGORIES.AGENT,
      account_name: accountName,
      code: account.agent_code ?? account.code,
      phone: account.phone,
      entity_id: account.agent_id ?? account.entity_id,
      bill_agent_id: account.bill_agent_id ?? account.entity_id,
      balance: account.balance,
      opening_balance: account.opening_balance,
      status,
    })
  }

  function hasAccountForAgent(agentId) {
    const id = Number(agentId)
    if (!id) return false

    const masterAgent = useAgentMasterStore().getAgent(id)

    return accounts.value.some((account) => {
      const accountAgentId = Number(account.agent_id ?? account.entity_id ?? account.bill_agent_id)
      if (accountAgentId && accountAgentId === id) {
        return true
      }

      if (!masterAgent) return false

      return (
        String(account.agent_code ?? account.code ?? '') === String(masterAgent.agent_code ?? '')
      )
    })
  }

  function getAvailableAgents() {
    const masterStore = useAgentMasterStore()

    return masterStore.agents.filter((agent) => !hasAccountForAgent(agent.id))
  }

  async function createAccount(agentId) {
    const masterStore = useAgentMasterStore()
    const masterAgent = masterStore.getAgent(agentId)

    if (!masterAgent) {
      return { ok: false, message: 'Selected agent was not found.' }
    }

    if (hasAccountForAgent(agentId)) {
      return { ok: false, message: 'An account already exists for this agent.' }
    }

    return financeAccountStore.createAccount({
      category: ACCOUNT_CATEGORIES.AGENT,
      account_name: masterAgent.agent_name,
      code: masterAgent.agent_code,
      phone: masterAgent.phone,
      entity_id: masterAgent.id,
      bill_agent_id: masterAgent.id,
      balance: 0,
      opening_balance: 0,
      status: 'Active',
    })
  }

  const paymentTypes = [
    { id: 'deposit', label: 'Deposit Balance', effect: 'credit' },
    { id: 'loan', label: 'Provide Loan', effect: 'debit' },
    { id: 'collect_bill', label: 'Collect Generated Bill', effect: 'debit' },
  ]

  function getAccount(agentId) {
    return accounts.value.find((account) => Number(account.id) === Number(agentId)) ?? null
  }

  function getAccountIdByBillAgentId(billAgentId) {
    return (
      accounts.value.find((account) => account.bill_agent_id === Number(billAgentId))?.id ?? null
    )
  }

  function getAccountByBillAgentId(billAgentId) {
    const accountId = getAccountIdByBillAgentId(billAgentId)
    return accountId ? getAccount(accountId) : null
  }

  function resolveJobMeta(jobCode) {
    const job = jobs.find((item) => item.job_code === jobCode)
    return {
      demand_letter: job?.demand_letter ?? '',
      client_name: job?.client ?? '',
      job_title: job?.job_title ?? '',
    }
  }

  function getTransactions(agentId) {
    return transactions.value
      .filter((item) => item.agent_id === Number(agentId))
      .sort((a, b) => b.id - a.id)
  }

  function updateBalance(agentId, amountDelta) {
    const account = findMutableAccount(agentId)
    if (!account) return null

    account.balance = Number(account.balance) + Number(amountDelta)
    account.current_balance = account.balance
    return account.balance
  }

  function recordTransaction(agentId, payload) {
    const account = getAccount(agentId)
    let balanceAfter = Number(account?.balance || 0)

    if (payload.amountDelta) {
      balanceAfter = updateBalance(agentId, payload.amountDelta)
    }

    transactions.value.unshift({
      id: nextTransactionId++,
      agent_id: Number(agentId),
      type: payload.type,
      particular: payload.particular,
      amount: payload.amount ?? Math.abs(payload.amountDelta || 0),
      bill_no: payload.bill_no ?? null,
      reference_no: payload.reference_no ?? null,
      account_id: payload.account_id ?? null,
      payment_method: payload.payment_method ?? null,
      balance_after: balanceAfter,
      created_at: new Date().toLocaleString('en-GB'),
    })
  }

  function resolveAgentLabel(agentId) {
    const agent = getAccount(agentId)
    if (!agent) return ''
    return `${agent.agent_code} - ${agent.agent_name}`
  }

  function recordAccountReceipt(accountId, payload) {
    if (!accountId) return

    useAccountStore().creditAccount(accountId, payload.amount)
    useAccountLedgerStore().addReceiptEntry(accountId, payload)
  }

  function recordAccountPayment(accountId, payload) {
    if (!accountId) return

    useAccountStore().debitAccount(accountId, payload.amount)
    useAccountLedgerStore().addPaymentEntry(accountId, payload)
  }

  function depositBalance(agentId, amount, particular, accountId = null, date = null) {
    recordTransaction(agentId, {
      type: 'deposit',
      particular,
      amountDelta: Number(amount),
      account_id: accountId ? Number(accountId) : null,
    })

    useAgentLedgerStore().addDepositEntry(agentId, {
      amount,
      particular,
      paymentMethod: 'Cash',
      date,
      remarks: '',
    })

    if (accountId) {
      recordAccountPayment(Number(accountId), {
        amount,
        particular: particular || 'Agent Deposit',
        clientName: resolveAgentLabel(agentId),
        paymentMethod: 'Cash',
        date,
        remarks: `Deposit to ${resolveAgentLabel(agentId)}`,
      })
    }
  }

  function provideLoan(agentId, amount, particular, accountId = null, date = null) {
    recordTransaction(agentId, {
      type: 'loan',
      particular,
      amountDelta: -Number(amount),
      account_id: accountId ? Number(accountId) : null,
    })

    useAgentLedgerStore().addLoanEntry(agentId, {
      amount,
      particular,
      date,
    })

    if (accountId) {
      recordAccountPayment(Number(accountId), {
        amount,
        particular: particular || 'Agent Loan',
        clientName: resolveAgentLabel(agentId),
        paymentMethod: 'Cash',
        date,
        remarks: `Loan to ${resolveAgentLabel(agentId)}`,
      })
    }
  }

  function addPayment(agentId, amount, particular) {
    recordTransaction(agentId, {
      type: 'add_payment',
      particular,
      amountDelta: Number(amount),
    })
  }

  function collectGeneratedBill(agentId, bill, particular) {
    const dueAmount = Math.max(Number(bill.total_amount) - Number(bill.paid_amount || 0), 0)
    recordTransaction(agentId, {
      type: 'collect_bill',
      particular,
      amountDelta: 0,
      amount: dueAmount,
      bill_no: bill.bill_no,
    })
  }

  function saveSalePayment(agentId, payload) {
    const amountDelta =
      payload.paymentMethod === 'balance' && payload.payerType === 'agent'
        ? -Number(payload.totalAmount)
        : 0

    recordTransaction(agentId, {
      type: 'sale_payment',
      particular: payload.particular,
      amountDelta,
      amount: Number(payload.totalAmount),
      bill_no: payload.entryNo,
      reference_no: payload.referenceNo,
      account_id: payload.accountId ? Number(payload.accountId) : null,
      payment_method: payload.paymentMethod,
    })

    const jobMeta = payload.jobCode ? resolveJobMeta(payload.jobCode) : {}

    useAgentLedgerStore().addSalePaymentEntry(agentId, {
      amount: payload.totalAmount,
      particular: payload.particular,
      entryNo: payload.entryNo,
      demandLetter: payload.demandLetter ?? jobMeta.demand_letter,
      job: payload.jobTitle ?? jobMeta.job_title,
      clientName: payload.clientName ?? jobMeta.client_name,
      paymentMethod: payload.paymentMethod,
      discount: payload.discount ?? 0,
      referenceNo: payload.referenceNo,
      date: payload.entryDate,
      remarks: payload.remarks,
      payer: payload.payerSummary ?? '',
    })

    if (payload.paymentMethod === 'cash' && payload.accountId) {
      recordAccountReceipt(Number(payload.accountId), {
        amount: payload.totalAmount,
        particular: payload.particular || 'Payment Collection',
        voucherNo: payload.referenceNo || payload.entryNo,
        demandLetter: payload.demandLetter ?? jobMeta.demand_letter,
        job: payload.jobTitle ?? jobMeta.job_title,
        clientName: resolveAgentLabel(agentId),
        paymentMethod: 'Cash',
        discount: payload.discount ?? 0,
        date: payload.entryDate,
        remarks:
          payload.remarks ||
          (payload.entryNo ? `Payment collection ${payload.entryNo}` : 'Payment collection received'),
      })
    }
  }

  function saveBillCollection(agentId, payload) {
    const amountDelta =
      payload.paymentMethod === 'balance' ? -Number(payload.totalAmount) : 0

    recordTransaction(agentId, {
      type: 'collect_bill',
      particular: payload.particular,
      amountDelta,
      amount: Number(payload.totalAmount),
      bill_no: payload.billItems.map((item) => item.bill_no).join(', '),
      reference_no: payload.referenceNo,
      account_id: payload.accountId ? Number(payload.accountId) : null,
      payment_method: payload.paymentMethod,
    })

    const firstBill = payload.billItems[0]
    const jobMeta = firstBill?.job_code ? resolveJobMeta(firstBill.job_code) : {}
    const billNos = payload.billItems.map((item) => item.bill_no)

    useAgentLedgerStore().addBillCollectionEntry(agentId, {
      amount: payload.totalAmount,
      particular: payload.particular,
      billNos,
      demandLetter: firstBill?.demand_letter ?? jobMeta.demand_letter,
      job: firstBill?.job_title ?? jobMeta.job_title,
      clientName: firstBill?.client_name ?? jobMeta.client_name,
      paymentMethod: payload.paymentMethod,
      discount: payload.discount ?? 0,
      referenceNo: payload.referenceNo,
      date: payload.collectionDate,
      remarks: payload.remarks,
    })

    if (payload.paymentMethod === 'cash' && payload.accountId) {
      recordAccountReceipt(Number(payload.accountId), {
        amount: payload.totalAmount,
        particular: payload.particular || 'Bill Collection',
        voucherNo: payload.referenceNo || billNos.join(', '),
        demandLetter: firstBill?.demand_letter ?? jobMeta.demand_letter,
        job: firstBill?.job_title ?? jobMeta.job_title,
        clientName: resolveAgentLabel(agentId),
        paymentMethod: 'Cash',
        discount: payload.discount ?? 0,
        date: payload.collectionDate,
        remarks: payload.remarks || (billNos.length ? `Collection against ${billNos.join(', ')}` : ''),
      })
    }
  }

  function recordBillGeneration(billAgentId, payload) {
    const accountId = getAccountIdByBillAgentId(billAgentId)
    if (!accountId) return null

    updateBalance(accountId, -Number(payload.amount))

    useAgentLedgerStore().addBillChargeEntry(accountId, {
      billNo: payload.billNo,
      amount: payload.amount,
      job: payload.job,
      clientName: payload.clientName,
      demandLetter: payload.demandLetter,
      candidateCount: payload.candidateCount,
      date: payload.date,
      remarks: payload.remarks,
    })

    return accountId
  }

  return {
    accounts,
    transactions,
    isCreateModalOpen,
    isEditModalOpen,
    editingAccount,
    paymentTypes,
    openCreateModal,
    closeCreateModal,
    openEditModal,
    closeEditModal,
    fetchAccounts,
    updateAccount,
    getAvailableAgents,
    hasAccountForAgent,
    createAccount,
    getAccount,
    getAccountIdByBillAgentId,
    getAccountByBillAgentId,
    getTransactions,
    updateBalance,
    recordTransaction,
    depositBalance,
    provideLoan,
    addPayment,
    collectGeneratedBill,
    saveBillCollection,
    saveSalePayment,
    recordBillGeneration,
  }
})
