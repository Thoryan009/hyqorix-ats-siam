import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import {
  collectPayment,
  fetchBillsReceivable,
  fetchBillReceivable,
  fetchSaleCollections,
} from '../services/financeAccountService'
import { useAgentAccountStore } from './agentAccountStore'
import { useAccountStore } from './accountStore'
import { useAccountLedgerStore } from './accountLedgerStore'
import { usePartyAccountsStore } from './partyAccountsStore'
import { useFinanceAccountStore } from './financeAccountStore'
import { useAccountTransactionStore } from './accountTransactionStore'

function getYearSuffix(date = new Date()) {
  return String(new Date(date).getFullYear()).slice(-2)
}

export function generateSaleEntryNo(date, existingNos = []) {
  const yearSuffix = getYearSuffix(date)
  const prefix = 'SE'
  const count =
    existingNos.filter((no) => {
      const voucher = String(no || '')
      return voucher.startsWith(`${prefix}-`) && voucher.endsWith(`/${yearSuffix}`)
    }).length + 1

  return `${prefix}-${String(count).padStart(3, '0')}/${yearSuffix}`
}

function normalizeCandidateRow(candidate) {
  return {
    candidate_id: Number(candidate.candidate_id),
    passport_no: candidate.passport_no ?? '',
    candidate_name: candidate.candidate_name ?? '',
    amount: Number(candidate.amount) || 0,
    sale_price: Number(candidate.sale_price) || 0,
  }
}

function normalizeSaleEntry(entry) {
  return {
    ...entry,
    payer_type: entry.payer_type ?? 'agent',
    payer_id: entry.payer_id ?? null,
    payer_code: entry.payer_code ?? '',
    payer_name: entry.payer_name ?? '',
    total_amount: Number(entry.total_amount) || 0,
    candidates: (entry.candidates ?? []).map(normalizeCandidateRow),
  }
}

function getApiErrorMessage(error, fallback = 'Request failed.') {
  return Object.values(error?.errors || {})?.flat()?.[0] || error?.message || fallback
}

export const useSaleEntryStore = defineStore('saleEntry', () => {
  const entries = ref([])
  const entriesLoading = ref(false)
  const entriesLoaded = ref(false)
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
    total_collected: 0,
  })
  const lastFetchParams = ref({
    page: 1,
    perPage: 10,
    filters: {},
  })

  const billsReceivable = ref([])
  const billsReceivableLoading = ref(false)

  const receivableBillCount = computed(() => billsReceivable.value.length)

  const totalReceivableAmount = computed(() =>
    billsReceivable.value.reduce(
      (sum, item) => sum + (Number(item.receivable_remaining) || 0),
      0
    )
  )

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

  async function fetchSaleEntries(options = true) {
    const { force, page, perPage, filters } = normalizeFetchOptions(options)

    if (entriesLoading.value) return entries.value
    if (!force && entriesLoaded.value) return entries.value

    entriesLoading.value = true
    lastFetchParams.value = { page, perPage, filters }

    try {
      const payload = await fetchSaleCollections(page, perPage, filters)
      entries.value = (payload.rows || []).map(normalizeSaleEntry)
      paginationMeta.value = {
        total: Number(payload.meta?.total) || 0,
        from: Number(payload.meta?.from) || 0,
        to: Number(payload.meta?.to) || 0,
        current_page: Number(payload.meta?.current_page) || page,
        per_page: Number(payload.meta?.per_page) || perPage,
        last_page: Number(payload.meta?.last_page) || 1,
        links: Array.isArray(payload.meta?.links) ? payload.meta.links : [],
      }
      summaryData.value = {
        total_count: Number(payload.summary?.total_count) || 0,
        this_month_count: Number(payload.summary?.this_month_count) || 0,
        total_collected: Number(payload.summary?.total_collected) || 0,
      }
      entriesLoaded.value = true
    } catch {
      if (!entriesLoaded.value) {
        entries.value = []
        paginationMeta.value = {
          total: 0,
          from: 0,
          to: 0,
          current_page: page,
          per_page: perPage,
          last_page: 1,
          links: [],
        }
        summaryData.value = {
          total_count: 0,
          this_month_count: 0,
          total_collected: 0,
        }
      }
    } finally {
      entriesLoading.value = false
    }

    return entries.value
  }

  async function fetchReceivableBills(force = true) {
    if (billsReceivableLoading.value) return billsReceivable.value
    if (!force && billsReceivable.value.length) return billsReceivable.value

    billsReceivableLoading.value = true
    try {
      billsReceivable.value = await fetchBillsReceivable()
    } finally {
      billsReceivableLoading.value = false
    }

    return billsReceivable.value
  }

  async function getReceivableBill(applicationId) {
    return fetchBillReceivable(applicationId)
  }

  const totalEntryCount = computed(() => summaryData.value.total_count)

  const thisMonthEntryCount = computed(() => summaryData.value.this_month_count)

  const totalCollectedAmount = computed(() => summaryData.value.total_collected)

  function getEntry(entryId) {
    return entries.value.find((item) => item.id === Number(entryId)) ?? null
  }

  function resolveMainAccountLabel(accountId) {
    if (!accountId) return ''
    const account = useAccountStore().accounts.find((item) => item.id === Number(accountId))
    if (!account) return ''
    return `${account.account_name} - ${account.account_label}`
  }

  async function refreshAffectedAccounts(payload, partyAccountId) {
    const financeAccountStore = useFinanceAccountStore()
    const accountLedgerStore = useAccountLedgerStore()
    const categories = new Set()

    if (['cash', 'bank'].includes(payload.paymentMethod) && payload.mainAccountId) {
      categories.add('main')
      accountLedgerStore.invalidateAccountLedger(Number(payload.mainAccountId))
    }

    if (payload.paymentMethod === 'expense_link') {
      ;['direct_expense', 'client_recruitment', 'operating_expense'].forEach((category) => {
        categories.add(category)
      })
    }

    if (partyAccountId) {
      const category = payload.payerType === 'client' ? 'client' : 'agent'
      categories.add(category)
      accountLedgerStore.invalidateAccountLedger(Number(partyAccountId))
    }

    if (payload.payerType === 'candidate') {
      categories.add('applicant')
      const applicantAccountIds = [
        ...new Set(
          (payload.candidates || [])
            .map((row) => Number(row.applicant_account_id))
            .filter((id) => id > 0)
        ),
      ]
      applicantAccountIds.forEach((accountId) => {
        accountLedgerStore.invalidateAccountLedger(accountId)
      })
    }

    await Promise.all(
      [...categories].map((category) => financeAccountStore.fetchAccounts(category, true))
    )

    if (categories.has('main')) {
      await useAccountStore().fetchActiveAccounts(true)
    }

    if (categories.has('agent')) {
      await useAgentAccountStore().fetchAccounts(true)
    }

    if (categories.has('applicant')) {
      await usePartyAccountsStore().fetchAccounts('applicant', true)
    }
  }

  async function saveSaleEntry(payload) {
    const agentStore = useAgentAccountStore()
    const partyAccountsStore = usePartyAccountsStore()

    const payerType = payload.payerType
    if (!['agent', 'candidate', 'client'].includes(payerType)) {
      return { ok: false, message: 'Please select a valid payer type.' }
    }

    const agent = payload.agentAccountId ? agentStore.getAccount(payload.agentAccountId) : null

    if (payerType === 'agent' && !agent) {
      return { ok: false, message: 'Agent account was not found.' }
    }

    let payerId = null
    let payerCode = ''
    let payerName = ''
    let partyAccountId = null

    if (payerType === 'agent') {
      payerId = agent.id
      payerCode = agent.agent_code
      payerName = agent.agent_name
      partyAccountId = agent.id
    } else if (payerType === 'client') {
      const client = partyAccountsStore.getAccount('client', payload.payerId)
      if (!client) {
        return { ok: false, message: 'Please select a client payer.' }
      }
      payerId = client.id
      payerCode = client.client_code
      payerName = client.client_name
      partyAccountId = client.id
    }

    const candidateRows = (payload.candidates ?? [])
      .map((row) => normalizeCandidateRow(row))
      .filter((row) => row.amount > 0)

    if (!candidateRows.length) {
      return { ok: false, message: 'Please select at least one candidate with a payment amount.' }
    }

    const totalAmount = candidateRows.reduce((sum, row) => sum + row.amount, 0)

    if (totalAmount <= 0) {
      return { ok: false, message: 'Total payment amount must be greater than zero.' }
    }

    if (payload.paymentMethod === 'balance') {
      return {
        ok: false,
        message: 'Adjust from balance is not available. Please use cash, bank, or due.',
      }
    }

    const paymentMethod = String(payload.paymentMethod || 'cash').toLowerCase()
    if (!['cash', 'bank', 'due', 'expense_link'].includes(paymentMethod)) {
      return { ok: false, message: 'Please select a valid receive method (Cash, Bank, Due, or Expense Link).' }
    }

    if (['cash', 'bank'].includes(paymentMethod) && !payload.mainAccountId) {
      return {
        ok: false,
        message: `Please select a main ${paymentMethod} account to receive payment.`,
      }
    }

    for (const row of candidateRows) {
      const remaining = getRemainingAmountForCandidate(row.candidate_id, row.sale_price)
      if (remaining <= 0) {
        return {
          ok: false,
          message: `${row.candidate_name || 'Candidate'} is already fully paid.`,
        }
      }
      // Agent / candidate due bills may collect more than sale price / remaining.
      if (payerType !== 'agent' && payerType !== 'candidate' && row.amount > remaining) {
        return {
          ok: false,
          message: `${row.candidate_name || 'Candidate'} has only ${remaining} remaining to collect.`,
        }
      }
    }

    const entryNo = generateSaleEntryNo(
      payload.entryDate,
      entries.value.map((item) => item.entry_no)
    )

    const payerSummary =
      payerType === 'candidate'
        ? candidateRows.map((row) => row.candidate_name).join(', ')
        : payerName

    const particular =
      payload.particular?.trim() ||
      `Payment collection (${payerType}) for ${candidateRows.length} candidate${candidateRows.length === 1 ? '' : 's'}`

    try {
      const collectResult = await collectPayment({
        paymentMethod,
        payerType,
        amount: totalAmount,
        entryDate: payload.entryDate,
        particular,
        referenceNo: payload.referenceNo || entryNo,
        entryNo,
        remarks:
          payload.remarks ||
          (payerSummary
            ? `Payment collection by ${payerSummary}`
            : `Payment collection ${entryNo}`),
        clientName:
          payerType === 'agent'
            ? `${agent.agent_code} - ${agent.agent_name}`
            : payerName || payerSummary,
        demandLetter: payload.demandLetter,
        jobId: payload.jobId,
        jobCode: payload.jobCode,
        jobTitle: payload.jobTitle,
        mainAccountId: ['cash', 'bank'].includes(paymentMethod) ? payload.mainAccountId : null,
        partyAccountId:
          payerType === 'agent' || payerType === 'client' ? partyAccountId : null,
        candidates: candidateRows,
      })

      await refreshAffectedAccounts({ ...payload, paymentMethod }, partyAccountId)
      await useAccountTransactionStore().fetchTransactions(true)
      await fetchSaleEntries({
        force: true,
        page: lastFetchParams.value.page,
        perPage: lastFetchParams.value.perPage,
        filters: lastFetchParams.value.filters,
      })

      const movedToReceivable = Number(
        collectResult?.data?.moved_to_receivable ?? collectResult?.moved_to_receivable ?? 0
      )

      const entry =
        entries.value.find((item) => item.entry_no === entryNo) ??
        normalizeSaleEntry({
          entry_no: entryNo,
          entry_date: payload.entryDate,
          payer_type: payerType,
          payer_id: payerId,
          payer_code: payerCode,
          payer_name: payerName,
          agent_account_id: agent?.id ?? null,
          agent_code: agent?.agent_code ?? '',
          agent_name: agent?.agent_name ?? '',
          job_id: payload.jobId,
          job_code: payload.jobCode,
          job_title: payload.jobTitle,
          payment_method: paymentMethod,
          main_account_id: ['cash', 'bank'].includes(paymentMethod)
            ? Number(payload.mainAccountId)
            : null,
          main_account_name: ['cash', 'bank'].includes(paymentMethod)
            ? resolveMainAccountLabel(payload.mainAccountId)
            : '',
          reference_no: payload.referenceNo ?? '',
          particular,
          remarks: payload.remarks ?? '',
          total_amount: totalAmount,
          candidates: candidateRows,
        })

      return { ok: true, entry, movedToReceivable }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to collect payment.') }
    }
  }

  function deleteEntry(entryId) {
    const index = entries.value.findIndex((item) => item.id === Number(entryId))
    if (index === -1) {
      return { ok: false, message: 'Payment collection was not found.' }
    }

    entries.value.splice(index, 1)
    return { ok: true }
  }

  function getCollectedAmountForCandidate(candidateId) {
    const id = Number(candidateId)
    if (!id) return 0

    return entries.value.reduce((sum, entry) => {
      if (String(entry.payment_method || '').toLowerCase() === 'due') {
        return sum
      }

      const candidateTotal = (entry.candidates ?? [])
        .filter((candidate) => Number(candidate.candidate_id) === id)
        .reduce((inner, candidate) => inner + (Number(candidate.amount) || 0), 0)

      return sum + candidateTotal
    }, 0)
  }

  function getLatestSalePriceForCandidate(candidateId) {
    const id = Number(candidateId)
    if (!id) return 0

    for (const entry of entries.value) {
      const row = (entry.candidates ?? []).find(
        (candidate) => Number(candidate.candidate_id) === id
      )
      const salePrice = Number(row?.sale_price) || 0
      if (salePrice > 0) return salePrice
    }

    return 0
  }

  function getRemainingAmountForCandidate(candidateId, salePrice) {
    const remaining = (Number(salePrice) || 0) - getCollectedAmountForCandidate(candidateId)
    return Math.max(0, Math.round(remaining * 100) / 100)
  }

  function isCandidateFullyPaid(candidateId, salePrice) {
    const price = Number(salePrice) || 0
    if (price <= 0) {
      return getCollectedAmountForCandidate(candidateId) > 0
    }

    return getRemainingAmountForCandidate(candidateId, price) <= 0
  }

  return {
    entries,
    entriesLoading,
    entriesLoaded,
    paginationMeta,
    summaryData,
    lastFetchParams,
    billsReceivable,
    billsReceivableLoading,
    receivableBillCount,
    totalReceivableAmount,
    totalEntryCount,
    thisMonthEntryCount,
    totalCollectedAmount,
    getEntry,
    saveSaleEntry,
    deleteEntry,
    generateSaleEntryNo,
    getCollectedAmountForCandidate,
    getLatestSalePriceForCandidate,
    getRemainingAmountForCandidate,
    isCandidateFullyPaid,
    fetchSaleEntries,
    fetchReceivableBills,
    getReceivableBill,
  }
})
