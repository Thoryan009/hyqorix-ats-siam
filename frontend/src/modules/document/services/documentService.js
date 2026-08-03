import { useApi } from '@/shared/composables/useApi'
import axiosInstance from '@/config/axiosConfig'
import { buildUrl } from '@/shared/utils/buildUrl'

const BASE_URL = '/documents'

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

export async function fetchOne(id) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}`)
  return response(api)
}

export async function fetchCategories() {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/categories`)
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
  await api.sendRequest(`${BASE_URL}/${payload.id}`, 'POST', payload.data, {
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

export async function downloadDocument(id, fileName = 'document') {
  const res = await axiosInstance.get(`${BASE_URL}/${id}/download`, {
    responseType: 'blob',
  })

  const url = window.URL.createObjectURL(new Blob([res.data]))
  const link = document.createElement('a')
  link.href = url
  link.setAttribute('download', fileName)
  document.body.appendChild(link)
  link.click()
  link.remove()
  window.URL.revokeObjectURL(url)
}
