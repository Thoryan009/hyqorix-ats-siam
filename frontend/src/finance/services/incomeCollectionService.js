import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

const BASE_URL = '/finance-income-collections'

const response = (api) => ({
  data: api.data.value,
  error: api.error.value,
})

export async function fetchIncomeCollections(page = 1, perPage = 100, filters = {}) {
  const api = useApi()
  const url = buildUrl(BASE_URL, {
    page,
    per_page: perPage,
    ...filters,
  })
  await api.sendRequest(url)
  return response(api)
}

export async function submitIncomeCollection(payload) {
  const api = useApi()
  await api.sendRequest(BASE_URL, 'POST', payload)
  if (api.error.value) throw api.error.value
  return api.data.value
}
