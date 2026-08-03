import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

const BASE_URL = '/client-bills'

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

export async function fetchByBillNo(billNo, jobId) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${billNo}?job_id=${jobId}`)
  return response(api)
}

export async function sendMailToClient(payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/send-mail`, 'POST', payload)
  if (api.error.value) {
    throw api.error.value
  }

  return api.data.value
}


export async function fetchWorkOrders() {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/data`)
  return response(api)
}

export async function generateInvoice(payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/generate-invoice`, 'POST', payload)
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function collectInvoiceFn(payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/collect-invoice`, 'POST', payload)
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function cancelledInvoiceFn(payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/cancel-invoice`, 'POST', payload)
  if (api.error.value) throw api.error.value
  return api.data.value
}
