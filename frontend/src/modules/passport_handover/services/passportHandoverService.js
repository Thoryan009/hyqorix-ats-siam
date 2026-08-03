import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'
import { fetchAll as fetchApplications } from '@/modules/application/services/applicationService'

const BASE_URL = '/passport-handovers'

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

export async function submitData(payload) {
  const api = useApi()
  await api.sendRequest(BASE_URL, 'POST', payload)
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function deleteItem(id) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}`, 'DELETE')
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function searchApplicationsByPassport(search = '', page = 1, perPage = 15) {
  return fetchApplications(page, perPage, {
    search: search?.trim() || undefined,
  })
}

export async function searchHandoversByPassport(passportNo = '') {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/search-by-passport`, {
    passport_no: passportNo?.trim() || undefined,
  })

  await api.sendRequest(url)
  return response(api)
}

export async function collectPassports(id, payload) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/${id}/collect`, 'POST', payload)
  if (api.error.value) throw api.error.value
  return api.data.value
}
