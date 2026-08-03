import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

const BASE_URL = '/expense-categories'

const response = (api) => ({
  data: api.data.value,
  error: api.error.value,
})

export async function fetchAll(page = 1, perPage = 100, filters = {}) {
  const api = useApi()
  const url = buildUrl(BASE_URL, {
    page,
    per_page: perPage,
    ...filters,
  })

  await api.sendRequest(url)
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
