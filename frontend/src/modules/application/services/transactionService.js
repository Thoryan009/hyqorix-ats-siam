import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

const BASE_URL = '/transactions'

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

export async function fetchOne(transactionId) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${transactionId}`)
  return response(api)
}

export async function fetchPosTransactions({ search = '' } = {}) {
  const api = useApi()
  const url = buildUrl(`/pos-transactions`, {
    search,
  })
  await api.sendRequest(url)
  return response(api)
}

export async function fetchTransactionData() {
  const api = useApi()
  await api.sendRequest(`/transaction/data`)
  return response(api)
}

export async function submitData(payload) {
  const api = useApi()
  await api.sendRequest(BASE_URL, 'POST', payload)
  if (api.error.value) {
    throw api.error.value
  }

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
