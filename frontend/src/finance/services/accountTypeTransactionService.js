import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

const BASE_URL = '/finance-account-type-transactions'

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

export async function fetchSummary(filters = {}) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/summary`, filters)
  await api.sendRequest(url)
  return response(api)
}

export async function submitData(payload) {
  const api = useApi()
  await api.sendRequest(BASE_URL, 'POST', payload)
  if (api.error.value) throw api.error.value
  return api.data.value
}
