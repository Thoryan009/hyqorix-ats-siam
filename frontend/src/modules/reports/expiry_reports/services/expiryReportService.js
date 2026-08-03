import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

const BASE_URL = '/reports/expiry'

const response = (api) => ({
  data: api.data.value,
  error: api.error.value,
})

export async function fetchExpiryReport(filters = {}) {
  const api = useApi()
  const url = buildUrl(BASE_URL, {
    ...filters,
  })

  await api.sendRequest(url)
  return response(api)
}

export async function fetchExpiryFilterData(filters = {}) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/filter-data`, {
    ...filters,
  })

  await api.sendRequest(url)
  return response(api)
}

// exportExpiryReportCSV

export async function exportExpiryReportCSV(filters = {}) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/export-csv`, {
    ...filters,
  })

  await api.sendRequest(url)
  return response(api)
}
