import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

const BASE_URL = '/finance-accounts'

const response = (api) => ({
  data: api.data.value,
  error: api.error.value,
})

export async function fetchAll(page = 1, perPage = 10, filters = {}) {
  const api = useApi()
  const url = buildUrl(BASE_URL, {
    page,
    per_page: perPage,
    ...filters,
  })

  await api.sendRequest(url)
  return response(api)
}

export async function fetchSummary(category) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/summary`, { category })

  await api.sendRequest(url)
  return response(api)
}

export async function fetchCapitalAccount() {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/capital`)
  if (api.error.value) throw api.error.value
  return api.data.value?.data ?? null
}

export async function fetchSaleAccount() {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/sale`)
  if (api.error.value) throw api.error.value
  return api.data.value?.data ?? null
}

export async function fetchBillsReceivableLedgerAccount() {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/bills-receivable-ledger`)
  if (api.error.value) throw api.error.value
  return api.data.value?.data ?? null
}

export async function fetchOne(id) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}`)
  return response(api)
}

export async function submitData(payload) {
  const api = useApi()
  await api.sendRequest(BASE_URL, 'POST', payload)
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function updateData(payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${payload.id}`, 'POST', payload, {
    headers: { 'X-HTTP-Method-Override': 'PUT' },
  })
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function deleteItem(id) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}`, 'DELETE')
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function bulkDelete(ids) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/bulk-delete`, 'POST', { ids })
  if (api.error.value) throw api.error.value
  return api.data.value
}

function mapMovementPayload(payload = {}) {
  return {
    from_account_id: Number(payload.fromAccountId),
    to_account_id: Number(payload.toAccountId),
    amount: Number(payload.amount),
    particular: payload.particular?.trim() || undefined,
    reference_no: payload.referenceNo?.trim() || undefined,
    remarks: payload.remarks?.trim() || undefined,
    transaction_date: payload.date,
  }
}

async function postMovement(path, payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${path}`, 'POST', mapMovementPayload(payload))
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function transferFunds(payload) {
  return postMovement('transfer', payload)
}

export async function depositFunds(payload) {
  return postMovement('deposit', payload)
}

export async function withdrawFunds(payload) {
  return postMovement('withdraw', payload)
}

export async function collectPayment(payload = {}) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/collect-payment`, 'POST', {
    payment_method: payload.paymentMethod,
    payer_type: payload.payerType,
    amount: Number(payload.amount),
    transaction_date: payload.entryDate || payload.date,
    particular: payload.particular?.trim() || undefined,
    reference_no: payload.referenceNo?.trim() || undefined,
    remarks: payload.remarks?.trim() || undefined,
    client_name: payload.clientName?.trim() || undefined,
    demand_letter: payload.demandLetter?.trim() || undefined,
    job: payload.jobTitle?.trim() || undefined,
    job_list_id: payload.jobId ? Number(payload.jobId) : undefined,
    job_code: payload.jobCode?.trim() || undefined,
    entry_no: payload.entryNo?.trim() || undefined,
    main_account_id: payload.mainAccountId ? Number(payload.mainAccountId) : undefined,
    party_account_id: payload.partyAccountId ? Number(payload.partyAccountId) : undefined,
    candidates: (payload.candidates || []).map((row) => ({
      application_id: Number(row.candidate_id || row.application_id),
      candidate_name: row.candidate_name,
      passport_no: row.passport_no,
      sale_price: Number(row.sale_price) || 0,
      amount: Number(row.amount) || 0,
      collected_amount: Number(row.collected_amount) || 0,
    })),
  })
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function fetchSaleCollectionSummary(applicationIds = []) {
  const ids = [...new Set(applicationIds.map((id) => Number(id)).filter((id) => id > 0))]
  if (!ids.length) return []

  const api = useApi()
  const url = buildUrl(`${BASE_URL}/sale-collection-summary`, {
    application_ids: ids,
  })
  await api.sendRequest(url)
  if (api.error.value) throw api.error.value

  const payload = api.data.value?.data
  return Array.isArray(payload) ? payload : []
}

export async function fetchSaleCollections(page = 1, perPage = 10, filters = {}) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/sale-collections`, {
    page,
    per_page: perPage,
    ...filters,
  })
  await api.sendRequest(url)
  if (api.error.value) throw api.error.value

  const payload = api.data.value?.data
  if (Array.isArray(payload)) {
    return {
      rows: payload,
      meta: {
        total: payload.length,
        from: payload.length ? 1 : 0,
        to: payload.length,
        current_page: 1,
        per_page: perPage,
        last_page: 1,
        links: [],
      },
      summary: {
        total_count: payload.length,
        this_month_count: 0,
        total_collected: payload.reduce((sum, row) => sum + (Number(row.total_amount) || 0), 0),
      },
    }
  }

  return {
    rows: Array.isArray(payload?.rows) ? payload.rows : [],
    meta: payload?.meta ?? {
      total: 0,
      from: 0,
      to: 0,
      current_page: page,
      per_page: perPage,
      last_page: 1,
      links: [],
    },
    summary: payload?.summary ?? {
      total_count: 0,
      this_month_count: 0,
      total_collected: 0,
    },
  }
}

export async function fetchBillsReceivable() {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/bills-receivable`)
  if (api.error.value) throw api.error.value
  const payload = api.data.value?.data
  return Array.isArray(payload) ? payload : []
}

export async function fetchBillReceivable(applicationId) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/bills-receivable/${Number(applicationId)}`)
  if (api.error.value) throw api.error.value
  return api.data.value?.data ?? null
}

export async function fetchLedger(accountId, page = 1, perPage = 100, filters = {}) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/${accountId}/ledger`, {
    page,
    per_page: perPage,
    ...filters,
  })

  await api.sendRequest(url)
  return response(api)
}
