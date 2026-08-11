import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

const BASE_URL = '/reports/ats'

const response = (api) => ({
  data: api.data.value,
  error: api.error.value,
})

export async function fetchAll(filters = {}) {
  const api = useApi()
  const url = buildUrl(BASE_URL, {
    ...filters,
  })

  await api.sendRequest(url)
  return response(api)
}

export async function fetchOne(filters = {}) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/show`, {
    ...filters,
  })

  await api.sendRequest(url)
  if (api.error.value) throw api.error.value
  return response(api)
}

// New function to fetch CSV data
export async function fetchExportCSV(filters = {}) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/export-csv`, {
    ...filters,
  })

  await api.sendRequest(url)
  return response(api)
}

