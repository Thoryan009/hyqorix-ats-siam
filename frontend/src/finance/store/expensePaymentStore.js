import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { buildRequestedByFields } from '../data/expensePaymentData'
import {
  approveEntry,
  fetchAll,
  managerApproveEntry,
  managerApproveEntryBatch,
  payPayableEntry,
  rejectEntry,
  submitBatch,
  submitData,
  submitMultiHead,
  updateData,
} from '../services/billEntryService'
import { extractPaginatedRows } from '@/shared/utils/extractPaginatedRows'
import {
  buildBillEntryBatchPayload,
  buildBillEntryMultiHeadPayload,
  buildBillEntryPayload,
  buildBillEntryUpdatePayload,
  buildPayableBillPaymentPayload,
  extractBillEntryRow,
  extractBillEntryRows,
  mapBillEntriesFromApi,
  mapBillEntryFromApi,
} from '../utils/billEntryMapper'
import { useExpenseHeadStore } from './expenseHeadStore'
import { useFinanceAccountStore } from './financeAccountStore'
import { useAccountStore } from './accountStore'
import { ACCOUNT_CATEGORIES } from '../data/accountCategoryCodes'
import { usePartyAccountsStore } from './partyAccountsStore'
import { useExpenseCostAccountsStore } from './expenseCostAccountsStore'
import { useExpenseCostLedgerStore } from './expenseCostLedgerStore'
import { getExpenseCostConfig } from '../config/expenseCostAccountConfigs'
import {
  isExpenseCostAccountCategory,
  normalizeLinkedAccounts,
  getCostTypeFromAccountCategory,
} from '../data/expenseHeadAccountLinkData'
import { getPartyConfig } from '../config/partyAccountConfigs'
import { isPayableBill, isPaidBill, getPayableRemainingAmount } from '../utils/payableBillUtils'
import { useExpenseCategoryStore } from './expenseCategoryStore'
import { useAgentAccountStore } from './agentAccountStore'
import { useAccountLedgerStore } from './accountLedgerStore'
import { useAgentLedgerStore } from './agentLedgerStore'
import { isPartyLedgerType, usePartyLedgerStore } from './partyLedgerStore'
import { useAccountTransactionStore } from './accountTransactionStore'
import {
  DIRECT_COST_CATEGORY_CODE,
  getDirectCostApplication,
  getDirectCostJob,
} from '../data/directCostApplicationData'
import {
  CLIENT_RECRUITMENT_COST_CATEGORY_CODE,
  resolveDemandLetterMeta,
} from '../data/clientRecruitmentDemandLetterData'
import { isCategoryCode, OPERATING_COST_CATEGORY_CODE } from '../data/expenseCategoryCodes'

export const useExpensePaymentStore = defineStore('expensePayment', () => {
  const payments = ref([])
  const isLoading = ref(false)
  const isLoaded = ref(false)

  function getApiErrorMessage(error, fallback = 'Request failed.') {
    return (
      Object.values(error?.errors || {})?.flat()?.[0] ||
      error?.message ||
      fallback
    )
  }

  async function fetchBillEntries(force = false) {
    if (isLoading.value) return
    if (isLoaded.value && !force) return

    isLoading.value = true

    try {
      const { data, error } = await fetchAll(1, 500, {})
      if (error) throw error

      payments.value = mapBillEntriesFromApi(extractPaginatedRows(data))
      isLoaded.value = true
    } catch {
      payments.value = []
    } finally {
      isLoading.value = false
    }
  }

  function upsertPayment(entry) {
    const index = payments.value.findIndex((payment) => payment.id === entry.id)
    if (index === -1) {
      payments.value.unshift(entry)
      return
    }

    payments.value[index] = entry
  }

  const totalPaidAmount = computed(() =>
    payments.value
      .filter((payment) => isPaidBill(payment))
      .reduce((sum, payment) => sum + Number(payment.amount || 0), 0),
  )

  const thisMonthPaidAmount = computed(() => {
    const now = new Date()
    return payments.value
      .filter((payment) => {
        if (!isPaidBill(payment)) return false

        const paymentDate = new Date(payment.payment_date || payment.created_at)
        return (
          paymentDate.getMonth() === now.getMonth() &&
          paymentDate.getFullYear() === now.getFullYear()
        )
      })
      .reduce((sum, payment) => sum + Number(payment.amount || 0), 0)
  })

  const submittedBillCount = computed(
    () => payments.value.filter((payment) => payment.status === 'submitted').length,
  )

  const submittedExpenseBillCount = computed(
    () =>
      payments.value.filter(
        (payment) =>
          payment.status === 'submitted' &&
          (payment.entry_type || 'expense_bill') !== 'asset_purchase',
      ).length,
  )

  const submittedPurchaseCount = computed(
    () =>
      payments.value.filter(
        (payment) =>
          payment.status === 'submitted' && payment.entry_type === 'asset_purchase',
      ).length,
  )

  const pendingBillCount = computed(
    () => payments.value.filter((payment) => payment.status === 'pending').length,
  )

  const approvedBillCount = computed(
    () => payments.value.filter((payment) => isPaidBill(payment)).length,
  )

  const payableBillCount = computed(
    () => payments.value.filter((payment) => isPayableBill(payment)).length,
  )

  function getBillEntry(id) {
    return payments.value.find((payment) => payment.id === Number(id)) ?? null
  }

  function getPaymentsByHead(headId) {
    return payments.value.filter((payment) => Number(payment.head_id) === Number(headId))
  }

  function getTotalPaidByHead(headId) {
    return getPaymentsByHead(headId).reduce((sum, payment) => sum + Number(payment.amount || 0), 0)
  }

  function getBilledApplicationIdsByHead(headId) {
    if (!headId) return new Set()

    const ids = payments.value
      .filter(
        (payment) =>
          Number(payment.head_id) === Number(headId) &&
          payment.application_id &&
          ['submitted', 'pending', 'approved'].includes(payment.status),
      )
      .map((payment) => Number(payment.application_id))

    return new Set(ids)
  }

  function hasApplicationBillForHead(applicationId, headId) {
    return getBilledApplicationIdsByHead(headId).has(Number(applicationId))
  }

  function resolveRequestedBy(payload) {
    const requestedBy = buildRequestedByFields(payload.requested_by ?? payload)

    if (!requestedBy.requested_by_name && !requestedBy.requested_by_email) {
      return {}
    }

    return {
      ...requestedBy,
      requested_at: new Date().toISOString(),
    }
  }

  async function resolveExpenseCostAccount(categoryId, headId, headName) {
    const costAccountsStore = useExpenseCostAccountsStore()
    const accountMeta = await costAccountsStore.resolveAccountMetaAsync(categoryId, headId)

    if (accountMeta) {
      return { ok: true, data: accountMeta }
    }

    const costType = costAccountsStore.getCostTypeByCategoryId(categoryId)

    if (!costType) {
      return { ok: true, data: null }
    }

    const config = getExpenseCostConfig(costType)

    return {
      ok: false,
      message: `No ${config?.costTypeLabel ?? 'expense cost'} account found for "${headName}". Please create the expense head account in Accounts first.`,
    }
  }

  async function resolveLinkedBillAccount(payload, head) {
    const linkedAccounts = normalizeLinkedAccounts(head?.linked_accounts)
    const costAccountsStore = useExpenseCostAccountsStore()
    const categoryId = Number(payload.category_id)
    const costType = costAccountsStore.getCostTypeByCategoryId(categoryId)

    if (costType) {
      await costAccountsStore.fetchAccounts(costType)
    }

    if (!linkedAccounts.length) {
      return resolveExpenseCostAccount(categoryId, Number(payload.head_id), head?.name ?? '')
    }

    const linkedCategory = payload.linked_account_category
    const linkedAccountId = Number(payload.linked_account_id)

    if (!linkedCategory || !linkedAccountId) {
      return {
        ok: false,
        message: 'Please select an account from the linked accounts list.',
      }
    }

    const isValidLink = linkedAccounts.some((link) => link.account_category === linkedCategory)
    if (!isValidLink) {
      return {
        ok: false,
        message: 'Selected account type is not linked to this expense head.',
      }
    }

    if (isExpenseCostAccountCategory(linkedCategory)) {
      const costAccountsStore = useExpenseCostAccountsStore()
      const account = costAccountsStore.getAccount(linkedCategory, linkedAccountId)
      const costType = getCostTypeFromAccountCategory(linkedCategory) ?? linkedCategory

      if (!account || account.status !== 'Active') {
        return { ok: false, message: 'Selected expense cost account was not found.' }
      }

      const accountName = String(account.head_name || account.account_name || '')
        .trim()
        .toLowerCase()
      const headName = String(head.name || '').trim().toLowerCase()
      const headMatches =
        Number(account.head_id) === Number(head.id) ||
        (headName && accountName === headName)

      if (!headMatches) {
        return {
          ok: false,
          message: 'Selected account does not belong to this expense head.',
        }
      }

      return {
        ok: true,
        data: {
          linked_account_category: costType,
          linked_account_id: linkedAccountId,
          linked_account_name: account.head_name,
          linked_account_type: '',
          expense_cost_type: costType,
          expense_cost_account_id: linkedAccountId,
          expense_cost_account_name: account.head_name,
          expense_cost_category_name: account.category_name,
        },
      }
    }

    if (linkedCategory === 'main') {
      const accountStore = useAccountStore()
      const account = accountStore.getAccount(linkedAccountId)

      if (!account || account.status !== 'Active') {
        return { ok: false, message: 'Selected main account was not found.' }
      }

      return {
        ok: true,
        data: {
          linked_account_category: linkedCategory,
          linked_account_id: linkedAccountId,
          linked_account_name: `${account.account_name} — ${account.account_label}`,
          linked_account_type: account.account_type,
        },
      }
    }

    if (linkedCategory === 'agent') {
      const agentStore = useAgentAccountStore()
      const account = agentStore.getAccount(linkedAccountId)

      if (!account || account.status !== 'Active') {
        return { ok: false, message: 'Selected agent account was not found.' }
      }

      return {
        ok: true,
        data: {
          linked_account_category: linkedCategory,
          linked_account_id: linkedAccountId,
          linked_account_name: `${account.agent_code} — ${account.agent_name}`,
          linked_account_type: 'Agent',
        },
      }
    }

    const partyStore = usePartyAccountsStore()
    const account = partyStore.getAccount(linkedCategory, linkedAccountId)

    if (!account || account.status !== 'Active') {
      return { ok: false, message: 'Selected account was not found.' }
    }

    const config = getPartyConfig(linkedCategory)

    return {
      ok: true,
      data: {
        linked_account_category: linkedCategory,
        linked_account_id: linkedAccountId,
        linked_account_name: config
          ? `${account[config.codeKey]} — ${account[config.nameKey]}`
          : '',
        linked_account_type: config?.partyLabel ?? linkedCategory,
      },
    }
  }

  function buildBillLedgerContext(payment) {
    return {
      demandLetter: payment.demand_letter || '',
      job: payment.job_name || '',
      clientName: payment.client_name || '',
    }
  }

  function recordBillInExpenseCostLedger(payment) {
    if (!payment.expense_cost_type || !payment.expense_cost_account_id) return

    const ledgerStore = useExpenseCostLedgerStore()
    const context = buildBillLedgerContext(payment)

    ledgerStore.addBillEntry(payment.expense_cost_type, payment.expense_cost_account_id, {
      billId: payment.id,
      amount: payment.amount,
      particular: payment.particular,
      voucherNo: payment.voucher_no,
      date: payment.payment_date,
      paymentMethod: payment.payment_method,
      remarks: payment.remarks || 'Bill entry submitted',
      billStatus: payment.status || 'pending',
      ...context,
    })
  }

  function recordBillInLinkedLedger(payment) {
    if (payment.expense_cost_type && payment.expense_cost_account_id) {
      recordBillInExpenseCostLedger(payment)
      return
    }

    const context = buildBillLedgerContext(payment)

    if (payment.linked_account_category === 'main' && payment.linked_account_id) {
      useAccountLedgerStore().addPaymentEntry(payment.linked_account_id, {
        amount: payment.amount,
        particular: payment.particular,
        voucherNo: payment.voucher_no,
        date: payment.payment_date,
        paymentMethod: payment.payment_method,
        remarks: payment.remarks || 'Bill entry submitted',
        ...context,
      })
      return
    }

    if (payment.linked_account_category === 'agent' && payment.linked_account_id) {
      useAgentLedgerStore().addBillChargeEntry(payment.linked_account_id, {
        billNo: payment.voucher_no,
        amount: payment.amount,
        job: payment.job_name || '',
        clientName: payment.client_name || '',
        demandLetter: payment.demand_letter || '',
        date: payment.payment_date,
        remarks: payment.remarks || payment.particular || 'Bill entry submitted',
      })
      return
    }

    if (isPartyLedgerType(payment.linked_account_category) && payment.linked_account_id) {
      usePartyLedgerStore().addBillEntry(
        payment.linked_account_category,
        payment.linked_account_id,
        {
          billId: payment.id,
          billNo: payment.voucher_no,
          amount: payment.amount,
          particular: payment.particular,
          demandLetter: payment.demand_letter || '',
          job: payment.job_name || '',
          clientName: payment.client_name || '',
          paymentMethod: payment.payment_method,
          date: payment.payment_date,
          remarks: payment.remarks || 'Bill entry submitted',
          billStatus: payment.status || 'pending',
        }
      )
    }
  }

  function syncBillLedgerStatus(payment, billStatus, remarks = '') {
    if (payment.expense_cost_type && payment.expense_cost_account_id) {
      const ledgerStore = useExpenseCostLedgerStore()
      ledgerStore.updateBillEntryStatus(
        payment.expense_cost_type,
        payment.expense_cost_account_id,
        payment.id,
        billStatus,
        remarks
      )
      return
    }

    if (isPartyLedgerType(payment.linked_account_category) && payment.linked_account_id) {
      usePartyLedgerStore().updateBillEntryStatus(
        payment.linked_account_category,
        payment.linked_account_id,
        payment.id,
        billStatus,
        remarks
      )
    }
  }

  function removeBillFromLinkedLedger(payment) {
    if (payment.expense_cost_type && payment.expense_cost_account_id) {
      removeBillFromExpenseCostLedger(payment)
      return
    }

    if (isPartyLedgerType(payment.linked_account_category) && payment.linked_account_id) {
      usePartyLedgerStore().removeBillEntry(
        payment.linked_account_category,
        payment.linked_account_id,
        payment.id
      )
    }
  }

  function removeBillFromExpenseCostLedger(payment) {
    if (!payment.expense_cost_type || !payment.expense_cost_account_id) return

    const ledgerStore = useExpenseCostLedgerStore()
    ledgerStore.removeBillEntry(
      payment.expense_cost_type,
      payment.expense_cost_account_id,
      payment.id,
    )
  }

  function resolveApplicationMeta(applicationId) {
    const application = getDirectCostApplication(applicationId)
    const job = application ? getDirectCostJob(application.job_id) : null

    if (!application || !job) return null

    return {
      application_id: application.id,
      candidate_name: application.name,
      passport_no: application.passport_no,
      application_status: application.process_status || application.status,
      job_id: job.id,
      job_name: job.job_name,
      job_code: job.job_code,
      client_name: job.client_name,
    }
  }

  async function payExpenseBatch(payload) {
    const categoryStore = useExpenseCategoryStore()
    const headStore = useExpenseHeadStore()

    const categoryId = Number(payload.category_id)
    const headId = Number(payload.head_id)
    const unitAmount = Number(payload.amount)
    const paymentDate = payload.payment_date || new Date().toISOString().slice(0, 10)
    const applicationIds = (payload.application_ids || []).map(Number).filter(Boolean)
    const jobId = Number(payload.job_id)

    if (!categoryId) {
      return { ok: false, message: 'Please select an expense category.' }
    }

    if (!headId) {
      return { ok: false, message: 'Please select an expense head.' }
    }

    if (!paymentDate) {
      return { ok: false, message: 'Please select a payment date.' }
    }

    if (!unitAmount || unitAmount <= 0) {
      return { ok: false, message: 'Please enter a valid bill amount.' }
    }

    if (!applicationIds.length) {
      return { ok: false, message: 'Please select at least one candidate application.' }
    }

    if (!jobId) {
      return { ok: false, message: 'Please select a job.' }
    }

    const category = categoryStore.categories.find((item) => item.id === categoryId)
    const head = headStore.heads.find((item) => item.id === headId)

    if (!category || !isCategoryCode(category, DIRECT_COST_CATEGORY_CODE)) {
      return { ok: false, message: 'Batch bill entry is only available for Direct Expense.' }
    }

    if (!head || head.category_id !== categoryId || head.status !== 'Active') {
      return { ok: false, message: 'Selected expense head is invalid.' }
    }

    const accountResult = await resolveLinkedBillAccount(payload, head)
    if (!accountResult.ok) {
      return accountResult
    }

    const applicationMetaList = applicationIds.map((applicationId) =>
      resolveApplicationMeta(applicationId),
    )

    if (applicationMetaList.some((meta) => !meta)) {
      return { ok: false, message: 'One or more selected applications were not found.' }
    }

    if (applicationMetaList.some((meta) => meta.job_id !== jobId)) {
      return { ok: false, message: 'All selected applications must belong to the chosen job.' }
    }

    const billedApplicationIds = getBilledApplicationIdsByHead(headId)
    if (applicationIds.some((applicationId) => billedApplicationIds.has(applicationId))) {
      return {
        ok: false,
        message:
          'One or more selected candidates already have a pending or approved bill for this expense head.',
      }
    }

    const totalAmount = unitAmount * applicationIds.length

    try {
      const apiPayload = buildBillEntryBatchPayload(
        {
          ...payload,
          category_id: categoryId,
          head_id: headId,
          amount: unitAmount,
          payment_date: paymentDate,
          ...resolveRequestedBy(payload),
        },
        accountResult.data ?? {},
      )

      const response = await submitBatch(apiPayload)
      const savedPayments = mapBillEntriesFromApi(extractBillEntryRows(response))

      savedPayments.forEach((payment) => {
        upsertPayment(payment)
      })

      return {
        ok: true,
        payments: savedPayments,
        count: savedPayments.length,
        totalAmount,
      }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to save bill entries.') }
    }
  }

  async function payExpenseMultiHead(payload) {
    const categoryStore = useExpenseCategoryStore()
    const headStore = useExpenseHeadStore()
    const costAccountsStore = useExpenseCostAccountsStore()

    const categoryId = Number(payload.category_id)
    const paymentDate = payload.payment_date || new Date().toISOString().slice(0, 10)
    const lines = Array.isArray(payload.lines) ? payload.lines : []

    if (!categoryId) {
      return { ok: false, message: 'Please select an expense category.' }
    }

    if (!paymentDate) {
      return { ok: false, message: 'Please select a payment date.' }
    }

    if (!lines.length) {
      return { ok: false, message: 'Please select at least one expense head.' }
    }

    const category = categoryStore.categories.find((item) => item.id === categoryId)
    if (!category) {
      return { ok: false, message: 'Expense category was not found.' }
    }

    if (!isCategoryCode(category, OPERATING_COST_CATEGORY_CODE)) {
      return { ok: false, message: 'Multi-head bill entry is only available for Operating Expense.' }
    }

    const costType = costAccountsStore.getCostTypeByCategoryId(categoryId)
    if (costType) {
      await costAccountsStore.fetchAccounts(costType)
    }

    const resolvedLines = []

    for (const line of lines) {
      const headId = Number(line.head_id)
      const amount = Number(line.amount)
      const head = headStore.heads.find((item) => Number(item.id) === headId)

      if (!head || Number(head.category_id) !== categoryId) {
        return { ok: false, message: 'One or more selected expense heads are invalid.' }
      }

      if (head.status !== 'Active') {
        return { ok: false, message: `Expense head "${head.name}" is not active.` }
      }

      if (!amount || amount <= 0) {
        return { ok: false, message: `Please enter a valid amount for "${head.name}".` }
      }

      let accountData = {}
      const headLinks = normalizeLinkedAccounts(head.linked_accounts)

      if (headLinks.length) {
        const accountResult = await resolveLinkedBillAccount(
          {
            category_id: categoryId,
            head_id: headId,
            linked_account_category: line.linked_account_category,
            linked_account_id: line.linked_account_id,
          },
          head,
        )

        if (!accountResult.ok) {
          return {
            ok: false,
            message: accountResult.message
              ? `${head.name}: ${accountResult.message}`
              : `Could not resolve linked account for "${head.name}".`,
          }
        }

        accountData = accountResult.data ?? {}
      } else {
        const softResult = await resolveExpenseCostAccount(categoryId, headId, head.name)
        if (softResult.ok) {
          accountData = softResult.data ?? {}
        }
      }

      resolvedLines.push({
        head_id: headId,
        amount,
        bill_no: line.bill_no?.trim() || line.voucher_no?.trim() || '',
        particular:
          line.particular?.trim() ||
          `${category.name} - ${head.name}`,
        ...accountData,
      })
    }

    const totalAmount = resolvedLines.reduce((sum, line) => sum + Number(line.amount || 0), 0)

    try {
      const response = await submitMultiHead(
        buildBillEntryMultiHeadPayload({
          ...payload,
          ...resolveRequestedBy(payload),
          lines: resolvedLines,
        })
      )
      const savedPayments = mapBillEntriesFromApi(extractBillEntryRows(response))

      savedPayments.forEach((payment) => {
        upsertPayment(payment)
      })

      return {
        ok: true,
        payments: savedPayments,
        count: savedPayments.length,
        totalAmount,
      }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to save multi-head bill entries.') }
    }
  }

  async function payExpense(payload) {
    const categoryStore = useExpenseCategoryStore()
    const headStore = useExpenseHeadStore()

    const categoryId = Number(payload.category_id)
    const headId = Number(payload.head_id)
    const amount = Number(payload.amount)
    const paymentDate = payload.payment_date || new Date().toISOString().slice(0, 10)

    if (!categoryId) {
      return { ok: false, message: 'Please select an expense category.' }
    }

    if (!headId) {
      return { ok: false, message: 'Please select an expense head.' }
    }

    if (!paymentDate) {
      return { ok: false, message: 'Please select a payment date.' }
    }

    if (!amount || amount <= 0) {
      return { ok: false, message: 'Please enter a valid payment amount.' }
    }

    const category = categoryStore.categories.find((item) => item.id === categoryId)
    const head = headStore.heads.find((item) => item.id === headId)

    if (!category) {
      return { ok: false, message: 'Expense category was not found.' }
    }

    if (!head) {
      return { ok: false, message: 'Expense head was not found.' }
    }

    if (head.category_id !== categoryId) {
      return { ok: false, message: 'Selected expense head does not belong to this category.' }
    }

    if (head.status !== 'Active') {
      return { ok: false, message: 'Selected expense head is not active.' }
    }

    const accountResult = await resolveLinkedBillAccount(payload, head)
    if (!accountResult.ok) {
      return accountResult
    }

    let applicationMeta = null
    let demandLetterMeta = null

    if (isCategoryCode(category, DIRECT_COST_CATEGORY_CODE)) {
      const applicationId = Number(payload.application_id)

      if (!applicationId) {
        return { ok: false, message: 'Please select a candidate application.' }
      }

      applicationMeta = resolveApplicationMeta(applicationId)

      if (!applicationMeta) {
        return { ok: false, message: 'Selected application was not found.' }
      }
    }

    if (isCategoryCode(category, CLIENT_RECRUITMENT_COST_CATEGORY_CODE)) {
      const demandLetterId = Number(payload.demand_letter_id)

      if (!demandLetterId) {
        return { ok: false, message: 'Please select a demand letter (DL).' }
      }

      demandLetterMeta = resolveDemandLetterMeta(demandLetterId)

      if (!demandLetterMeta) {
        return { ok: false, message: 'Selected demand letter was not found.' }
      }
    }

    const particular =
      payload.particular?.trim() ||
      (applicationMeta
        ? `${category.name} - ${head.name} - ${applicationMeta.candidate_name} (${applicationMeta.passport_no})`
        : demandLetterMeta
          ? `${category.name} - ${head.name} - ${demandLetterMeta.demand_letter} (${demandLetterMeta.client_name})`
          : `${category.name} - ${head.name}`)

    try {
      const apiPayload = buildBillEntryPayload(
        {
          ...payload,
          category_id: categoryId,
          head_id: headId,
          amount,
          payment_date: paymentDate,
          particular,
          application_id: applicationMeta?.application_id ?? payload.application_id,
          demand_letter_id: demandLetterMeta?.demand_letter_id ?? payload.demand_letter_id,
          ...resolveRequestedBy(payload),
        },
        accountResult.data ?? {},
      )

      const response = await submitData(apiPayload)
      const payment = mapBillEntryFromApi(extractBillEntryRow(response))

      upsertPayment(payment)

      return { ok: true, payment }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to save bill entry.') }
    }
  }

  async function payAssetPurchase(payload) {
    const amount = Number(payload.amount)
    const paymentDate = payload.payment_date || new Date().toISOString().slice(0, 10)
    const assetAccountId = Number(payload.asset_account_id)
    const vendorAccountId = Number(payload.vendor_account_id)

    if (!paymentDate) {
      return { ok: false, message: 'Please select a bill date.' }
    }

    if (!assetAccountId) {
      return { ok: false, message: 'Please select an asset account.' }
    }

    if (!vendorAccountId) {
      return { ok: false, message: 'Please select a vendor account.' }
    }

    if (!amount || amount <= 0) {
      return { ok: false, message: 'Please enter a valid payment amount.' }
    }

    const { useFinanceAccountStore } = await import('./financeAccountStore')
    const { ACCOUNT_CATEGORIES } = await import('../data/accountCategoryCodes')
    const financeAccountStore = useFinanceAccountStore()
    const partyAccountsStore = usePartyAccountsStore()
    await Promise.all([
      financeAccountStore.fetchAccounts(ACCOUNT_CATEGORIES.ASSET, true),
      partyAccountsStore.fetchAccounts('vendor', true),
    ])

    const assetAccount = financeAccountStore
      .getAccountsByCategory(ACCOUNT_CATEGORIES.ASSET)
      .find((account) => Number(account.id) === assetAccountId)

    if (!assetAccount) {
      return { ok: false, message: 'Asset account was not found.' }
    }

    if (assetAccount.status !== 'Active') {
      return { ok: false, message: 'Selected asset account is not active.' }
    }

    if (!assetAccount.link_to_purchase) {
      return { ok: false, message: 'Selected asset account is not linked to purchase.' }
    }

    const vendorAccount = partyAccountsStore.getAccount('vendor', vendorAccountId)
    if (!vendorAccount) {
      return { ok: false, message: 'Vendor account was not found.' }
    }

    if (vendorAccount.status !== 'Active') {
      return { ok: false, message: 'Selected vendor account is not active.' }
    }

    const particular =
      payload.particular?.trim() || `Asset Purchase - ${assetAccount.account_name}`

    try {
      const apiPayload = buildBillEntryPayload({
        ...payload,
        entry_type: 'asset_purchase',
        asset_account_id: assetAccountId,
        vendor_account_id: vendorAccountId,
        amount,
        payment_date: paymentDate,
        particular,
        ...resolveRequestedBy(payload),
      })

      const response = await submitData(apiPayload)
      const payment = mapBillEntryFromApi(extractBillEntryRow(response))

      upsertPayment(payment)

      return { ok: true, payment }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to save asset purchase.') }
    }
  }

  function deletePayment(id) {
    payments.value = payments.value.filter((payment) => payment.id !== id)
  }

  function resolvePaymentAccount(payload) {
    const financeAccountStore = useFinanceAccountStore()
    const category = payload.payment_account_category
    const accountId = Number(payload.payment_account_id)

    if (!category) {
      return { ok: false, message: 'Please select an account category.' }
    }

    if (!accountId) {
      return { ok: false, message: 'Please select a payment account.' }
    }

    const account = financeAccountStore.getAccount(accountId)

    if (!account || account.status !== 'Active') {
      return { ok: false, message: 'Selected payment account was not found.' }
    }

    if (category === 'main') {
      if (account.category !== ACCOUNT_CATEGORIES.MAIN) {
        return { ok: false, message: 'Selected account is not a main account.' }
      }

      return {
        ok: true,
        data: {
          payment_account_category: 'main',
          payment_account_type: account.account_type,
          payment_account_id: accountId,
          payment_account_name: `${account.account_name} — ${account.account_label}`,
        },
      }
    }

    if (category === 'staff') {
      if (account.category !== ACCOUNT_CATEGORIES.STAFF) {
        return { ok: false, message: 'Selected account is not a staff account.' }
      }

      return {
        ok: true,
        data: {
          payment_account_category: 'staff',
          payment_account_type: 'Staff',
          payment_account_id: accountId,
          payment_account_name: `${account.staff_code} — ${account.staff_name}`,
        },
      }
    }

    return { ok: false, message: 'Please select a valid account category.' }
  }

  async function refreshAccountsAfterBillAction(entry) {
    const financeAccountStore = useFinanceAccountStore()
    const accountLedgerStore = useAccountLedgerStore()
    const costAccountsStore = useExpenseCostAccountsStore()
    const categories = new Set()

    let expenseAccountId = Number(entry.linked_account_id || entry.expense_cost_account_id || 0)

    if (!expenseAccountId && entry.head_id && entry.category_id) {
      await costAccountsStore.ensureAccountsForCategoryId(entry.category_id)
      const account = costAccountsStore.getAccountByHeadId(entry.category_id, entry.head_id)
      expenseAccountId = account?.id ?? 0
    }

    if (expenseAccountId) {
      let account = financeAccountStore.getAccount(expenseAccountId)
      if (!account) {
        account = await financeAccountStore.fetchAccountById(expenseAccountId)
      }
      if (account?.category) categories.add(account.category)
      accountLedgerStore.invalidateAccountLedger(expenseAccountId)
    }

    const paymentAccountId = Number(entry.payment_account_id || 0)
    if (paymentAccountId) {
      let account = financeAccountStore.getAccount(paymentAccountId)
      if (!account) {
        account = await financeAccountStore.fetchAccountById(paymentAccountId)
      }
      if (account?.category) categories.add(account.category)
      accountLedgerStore.invalidateAccountLedger(paymentAccountId)
    }

    if (!categories.size) return

    await Promise.all(
      [...categories].map((category) => financeAccountStore.fetchAccounts(category, true))
    )

    if (categories.has(ACCOUNT_CATEGORIES.MAIN)) {
      const accountStore = useAccountStore()
      await accountStore.fetchActiveAccounts(true)
    }
  }

  async function managerApproveBillEntry(payload) {
    const existing = payments.value.find((payment) => payment.id === Number(payload.id))

    if (!existing) {
      return { ok: false, message: 'Bill entry was not found.' }
    }

    if (existing.status !== 'submitted') {
      return { ok: false, message: 'Only submitted bills can be approved by a manager.' }
    }

    try {
      const response = await managerApproveEntry(payload.id, {
        approval_remarks: payload.approval_remarks ?? '',
        approved_by: payload.approved_by ?? 'Manager',
      })
      const entry = mapBillEntryFromApi(extractBillEntryRow(response))

      upsertPayment(entry)

      return { ok: true, entry }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to manager-approve bill entry.') }
    }
  }

  async function managerApproveBillEntryBatch(payload) {
    const ids = [...new Set((payload.ids || []).map((id) => Number(id)).filter(Boolean))]

    if (!ids.length) {
      return { ok: false, message: 'Select at least one bill entry to approve.' }
    }

    const selected = ids.map((id) => payments.value.find((payment) => payment.id === id))

    if (selected.some((entry) => !entry)) {
      return { ok: false, message: 'One or more selected bill entries were not found.' }
    }

    if (selected.some((entry) => entry.status !== 'submitted')) {
      return { ok: false, message: 'Only submitted bills can be approved by a manager.' }
    }

    const batchKeys = new Set(
      selected.map((entry) => {
        const requestNo = String(entry.request_no || '').trim()
        if (requestNo) return `req:${requestNo}`
        const batchRef = String(entry.batch_ref || '').trim()
        if (batchRef) return `batch:${batchRef}`
        return `id:${entry.id}`
      })
    )

    if (batchKeys.size !== 1) {
      return { ok: false, message: 'Bulk approve is only allowed for bills from the same batch.' }
    }

    try {
      const response = await managerApproveEntryBatch({
        ids,
        approval_remarks: payload.approval_remarks ?? '',
        approved_by: payload.approved_by ?? 'Manager',
      })
      const entries = mapBillEntriesFromApi(extractBillEntryRows(response))

      entries.forEach((entry) => upsertPayment(entry))

      return { ok: true, entries }
    } catch (error) {
      return {
        ok: false,
        message: getApiErrorMessage(error, 'Failed to bulk manager-approve bill entries.'),
      }
    }
  }

  async function approveBillEntry(payload) {
    const existing = payments.value.find((payment) => payment.id === Number(payload.id))

    if (!existing) {
      return { ok: false, message: 'Bill entry was not found.' }
    }

    if (existing.status === 'approved') {
      return { ok: false, message: 'This bill entry is already approved.' }
    }

    if (existing.status === 'submitted') {
      return { ok: false, message: 'This bill must be approved by a manager first.' }
    }

    if (existing.status !== 'pending') {
      return { ok: false, message: 'Only Bills To Pay entries can be paid/approved here.' }
    }

    const paymentMethod = payload.payment_method || existing.payment_method
    const billTotal = Number(payload.amount)
    const payAmountRaw = payload.pay_amount
    const payAmount =
      payAmountRaw !== undefined && payAmountRaw !== null && payAmountRaw !== ''
        ? Number(payAmountRaw)
        : paymentMethod === 'due'
          ? 0
          : billTotal

    if (!billTotal || billTotal <= 0) {
      return { ok: false, message: 'Please enter a valid bill amount.' }
    }

    if (Number.isNaN(payAmount) || payAmount < 0) {
      return { ok: false, message: 'Please enter a valid pay amount.' }
    }

    if (payAmount > billTotal) {
      return { ok: false, message: 'Pay amount cannot exceed the bill amount.' }
    }

    let accountData = {}

    if (payAmount > 0) {
      const accountResult = resolvePaymentAccount({
        ...payload,
        payment_method: paymentMethod === 'due' ? 'cash' : paymentMethod,
      })
      if (!accountResult.ok) {
        return accountResult
      }
      accountData = accountResult.data
    } else {
      accountData = {
        payment_account_category: '',
        payment_account_type: '',
        payment_account_id: null,
        payment_account_name: '',
      }
    }

    if (existing.is_manual_request && !payload.manual_approval_manager_id) {
      return { ok: false, message: 'Please select the manager who approved this manual bill.' }
    }

    try {
      const response = await approveEntry(payload.id, {
        ...buildBillEntryUpdatePayload({
          ...payload,
          pay_amount: payAmount,
          payment_method: payAmount <= 0 ? 'due' : paymentMethod === 'due' ? 'cash' : paymentMethod,
        }),
        ...accountData,
      })
      const entry = mapBillEntryFromApi(extractBillEntryRow(response))

      upsertPayment(entry)
      await refreshAccountsAfterBillAction(entry)
      await useAccountTransactionStore().fetchTransactions(true)

      return { ok: true, entry }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to approve bill entry.') }
    }
  }

  async function approveBillEntryBatch(payload) {
    const ids = [...new Set((payload.ids || []).map((id) => Number(id)).filter(Boolean))]

    if (!ids.length) {
      return { ok: false, message: 'Select at least one bill entry to pay.' }
    }

    const selected = ids.map((id) => payments.value.find((payment) => payment.id === id))

    if (selected.some((entry) => !entry)) {
      return { ok: false, message: 'One or more selected bill entries were not found.' }
    }

    if (selected.some((entry) => entry.status !== 'pending')) {
      return { ok: false, message: 'Only Bills To Pay entries can be paid here.' }
    }

    const batchKeys = new Set(
      selected.map((entry) => {
        const requestNo = String(entry.request_no || '').trim()
        if (requestNo) return `req:${requestNo}`
        const batchRef = String(entry.batch_ref || '').trim()
        if (batchRef) return `batch:${batchRef}`
        return `id:${entry.id}`
      })
    )

    if (batchKeys.size !== 1) {
      return { ok: false, message: 'Bulk pay is only allowed for bills from the same batch.' }
    }

    const approved = []

    for (const entry of selected) {
      const result = await approveBillEntry({
        ...payload,
        id: entry.id,
        amount: Number(entry.amount) || 0,
        pay_amount: Number(entry.amount) || 0,
        particular: entry.particular || payload.particular || '',
        reference_no: entry.reference_no || payload.reference_no || '',
        voucher_no: entry.voucher_no || payload.voucher_no || '',
        remarks: entry.remarks || payload.remarks || '',
      })

      if (!result.ok) {
        return {
          ok: false,
          message: `Stopped at ${entry.voucher_no || `Bill #${entry.id}`}: ${result.message}`,
          entries: approved,
        }
      }

      approved.push(result.entry)
    }

    return { ok: true, entries: approved }
  }

  async function payPayableBillEntry(payload) {
    const existing = payments.value.find((payment) => payment.id === Number(payload.id))

    if (!existing) {
      return { ok: false, message: 'Bill entry was not found.' }
    }

    if (!isPayableBill(existing)) {
      return { ok: false, message: 'Only approved due bills on expense accounts can be paid here.' }
    }

    const paymentMethod = String(payload.payment_method || 'cash').toLowerCase()
    if (paymentMethod === 'due') {
      return { ok: false, message: 'Select cash, bank, or income link to settle this payable bill.' }
    }

    if (!['cash', 'bank', 'income_link'].includes(paymentMethod)) {
      return { ok: false, message: 'Please select a valid payment method (Cash, Bank, or Income Link).' }
    }

    let accountData = {}
    if (paymentMethod !== 'income_link') {
      const accountResult = resolvePaymentAccount(payload)
      if (!accountResult.ok) {
        return accountResult
      }
      accountData = accountResult.data
    }

    const payAmount = Number(payload.pay_amount ?? payload.amount)
    const remaining = getPayableRemainingAmount(existing)

    if (!payAmount || payAmount <= 0) {
      return { ok: false, message: 'Please enter a valid payment amount.' }
    }

    if (payAmount > remaining) {
      return {
        ok: false,
        message: `Payment amount cannot exceed the remaining payable balance of ৳${remaining.toLocaleString('en-BD')}.`,
      }
    }

    try {
      const response = await payPayableEntry(payload.id, {
        ...buildPayableBillPaymentPayload(payload),
        ...accountData,
        payment_method: paymentMethod,
        payment_account_id:
          paymentMethod === 'income_link' ? null : payload.payment_account_id || accountData.payment_account_id,
        payment_account_category:
          paymentMethod === 'income_link'
            ? null
            : payload.payment_account_category || accountData.payment_account_category,
      })
      const entry = mapBillEntryFromApi(extractBillEntryRow(response))

      upsertPayment(entry)
      await refreshAccountsAfterBillAction(entry)
      if (paymentMethod === 'income_link') {
        const financeAccountStore = useFinanceAccountStore()
        await Promise.all(
          ['other_income', 'recruitment_income', 'client_income'].map((category) =>
            financeAccountStore.fetchAccounts(category, true)
          )
        )
      }
      await useAccountTransactionStore().fetchTransactions(true)

      return { ok: true, entry }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to settle payable bill.') }
    }
  }

  async function rejectBillEntry(payload) {
    const existing = payments.value.find((payment) => payment.id === Number(payload.id))

    if (!existing) {
      return { ok: false, message: 'Bill entry was not found.' }
    }

    if (existing.status === 'approved') {
      return { ok: false, message: 'Approved bills cannot be rejected.' }
    }

    if (!['submitted', 'pending'].includes(existing.status)) {
      return { ok: false, message: 'Only submitted or pending bills can be rejected.' }
    }

    const amount = Number(payload.amount)
    if (!amount || amount <= 0) {
      return { ok: false, message: 'Please enter a valid bill amount.' }
    }

    try {
      const response = await rejectEntry(payload.id, buildBillEntryUpdatePayload(payload))
      const entry = mapBillEntryFromApi(extractBillEntryRow(response))

      upsertPayment(entry)
      removeBillFromLinkedLedger(existing)

      return { ok: true, entry }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to reject bill entry.') }
    }
  }

  async function saveBillEntryChanges(payload) {
    const existing = payments.value.find((payment) => payment.id === Number(payload.id))

    if (!existing) {
      return { ok: false, message: 'Bill entry was not found.' }
    }

    if (existing.status === 'approved') {
      return { ok: false, message: 'Approved bills cannot be edited.' }
    }

    const amount = Number(payload.amount)
    if (!amount || amount <= 0) {
      return { ok: false, message: 'Please enter a valid bill amount.' }
    }

    try {
      const response = await updateData(payload.id, buildBillEntryUpdatePayload(payload))
      const entry = mapBillEntryFromApi(extractBillEntryRow(response))

      upsertPayment(entry)

      return { ok: true, entry }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to update bill entry.') }
    }
  }

  return {
    payments,
    isLoading,
    isLoaded,
    totalPaidAmount,
    thisMonthPaidAmount,
    pendingBillCount,
    submittedBillCount,
    submittedExpenseBillCount,
    submittedPurchaseCount,
    approvedBillCount,
    payableBillCount,
    fetchBillEntries,
    getBillEntry,
    getPaymentsByHead,
    getTotalPaidByHead,
    getBilledApplicationIdsByHead,
    hasApplicationBillForHead,
    payExpense,
    payAssetPurchase,
    payExpenseBatch,
    payExpenseMultiHead,
    managerApproveBillEntry,
    managerApproveBillEntryBatch,
    approveBillEntry,
    approveBillEntryBatch,
    payPayableBillEntry,
    rejectBillEntry,
    saveBillEntryChanges,
    deletePayment,
  }
})
