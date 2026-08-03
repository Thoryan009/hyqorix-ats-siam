import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

const BASE_URL = '/ats'

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

export async function fetchOne(jobId = null, processId = null, applicationId = null) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/single`, {
    job_id: jobId,
    process_id: processId,
    application_id: applicationId,
  })

  await api.sendRequest(url)
  return response(api)
}

export async function fetchWorkOrders(clientId = null) {
  const api = useApi()
  const url = clientId ? `?client_id=${clientId}` : ''
  await api.sendRequest(`ats-work-orders${url}`)
  return response(api)
}

export async function fetchClients() {
  const api = useApi()
  await api.sendRequest('ats-clients')
  return response(api)
}

export async function fetchAtsData(clientId = null) {
  const url = clientId ? `?client_id=${clientId}` : ''
  const api = useApi()
  await api.sendRequest(`ats-data${url}`)
  return response(api)
}

export async function fetchAtsQuickSearch(search = '') {
  const api = useApi()
  const url = buildUrl('ats-quick-search', { search })

  await api.sendRequest(url)
  return response(api)
}

export async function submitNextProcessData({ applicationId, nextProcessId, processData }) {
  const api = useApi()

  const payload = {
    application_id: applicationId,
    next_process_id: nextProcessId,
    processData: processData,
  }

  await api.sendRequest(`${BASE_URL}/next-process`, 'POST', payload)

  if (api.error.value) {
    throw api.error.value
  }

  return api.data.value
}

export async function submitBulkNextProcessData({ next_process_id, application_ids }) {
  const api = useApi()

  const payload = {
    application_ids,
    next_process_id,
  }

  await api.sendRequest(`${BASE_URL}/bulk-next-process`, 'POST', payload)

  if (api.error.value) {
    throw api.error.value
  }

  return api.data.value
}

export async function updateProcessData(payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/update-process`, 'POST', payload)

  if (api.error.value) throw api.error.value
  console.log('Process updated successfully', api.data.value)
  return api.data.value
}

export async function deleteProcessFn(applicationProcessId) {
  const api = useApi()
  await api.sendRequest(
    `${BASE_URL}/delete-current-process?application_process_id=${applicationProcessId}`,
    'DELETE',
  )

  if (api.error.value) throw api.error.value
  console.log('Process deleted successfully', api.data.value)
  return api.data.value
}
