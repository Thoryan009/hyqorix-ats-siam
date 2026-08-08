import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'
import { hasUploadFile, toDynamicFormData } from '../utils/billEntryMapper'

const BASE_URL = '/finance-bill-entries'

const response = (api) => ({
  data: api.data.value,
  error: api.error.value,
})

function resolveRequestBody(payload) {
  if (hasUploadFile(payload)) {
    return toDynamicFormData(payload)
  }
  return payload
}

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

export async function fetchOne(id) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${Number(id)}`)
  return response(api)
}

export async function fetchSummary() {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/summary`)
  return response(api)
}

export async function fetchHeadTotal(headId) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/head-total/${headId}`)
  return response(api)
}

export async function submitData(payload) {
  const api = useApi()
  await api.sendRequest(BASE_URL, 'POST', resolveRequestBody(payload))
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function submitBatch(payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/batch`, 'POST', resolveRequestBody(payload))
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function submitMultiHead(payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/multi-head`, 'POST', resolveRequestBody(payload))
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function updateData(id, payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}`, 'PUT', payload)
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function approveEntry(id, payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}/approve`, 'POST', resolveRequestBody(payload))
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function payPayableEntry(id, payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}/pay-payable`, 'POST', payload)
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function managerApproveEntry(id, payload = {}) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}/manager-approve`, 'POST', payload)
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function managerApproveEntryBatch(payload = {}) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/manager-approve-batch`, 'POST', payload)
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function rejectEntry(id, payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}/reject`, 'POST', resolveRequestBody(payload))
  if (api.error.value) throw api.error.value
  return api.data.value
}
