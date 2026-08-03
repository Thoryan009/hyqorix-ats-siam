import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

const BASE_URL = '/dashboard'

const response = (api) => ({
  data: api.data.value,
  error: api.error.value,
})

export async function fetchAll(page = 1, perPage = 10) {
  const api = useApi()
  const url = buildUrl(BASE_URL, {
    page,
    per_page: perPage,
  })

  await api.sendRequest(url)
  return response(api)
}

export async function fetchFlightSummary(page = 1, perPage = 10, filters = {}) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/flight-summary`, {
    page,
    per_page: perPage,
    ...filters,
  })

  await api.sendRequest(url)
  return response(api)
}
