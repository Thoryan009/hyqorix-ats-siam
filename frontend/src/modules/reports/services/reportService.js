import { useApi } from '@/shared/composables/useApi'

const normalizeListResponse = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  return []
}

const response = (api) => ({
  data: normalizeListResponse(api.data.value),
  error: api.error.value,
})

export async function fetchProcess() {
  const api = useApi()
  await api.sendRequest('report-processes')
  return response(api)
}

export async function fetchWorkOrders() {
  const api = useApi()
  await api.sendRequest('report-work-orders')
  return response(api)
}

export async function fetchJobs() {
  const api = useApi()
  await api.sendRequest('report-jobs')
  return response(api)
}

export async function fetchStatus() {
  const api = useApi()
  await api.sendRequest('report-transaction-status')
  return response(api)
}

export async function fetchMethods() {
  const api = useApi()
  await api.sendRequest('report-payment-methods')
  return response(api)
}

export async function fetchClients() {
  const api = useApi()
  await api.sendRequest('report-clients')
  return response(api)
}

export async function fetchCountries() {
  const api = useApi()
  await api.sendRequest('report-countries')
  return response(api)
}

export async function fetchAgents() {
  const api = useApi()
  await api.sendRequest('report-agents')
  return response(api)
}


export async function fetchReportData() {
  const api = useApi()
  await api.sendRequest('report-data')
  return response(api)
}

export async function fetchReportPrincipals(){
  const api = useApi()
  await api.sendRequest('report-principals')
  return response(api)
}
