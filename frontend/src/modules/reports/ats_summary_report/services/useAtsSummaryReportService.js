import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

const BASE_URL = '/report-ats-summary'

const response = (api) => ({
  data: api.data.value,
  error: api.error.value,
})

export async function fetchAtsSummaryReport(filters = {}) {
  const api = useApi()
  const url = buildUrl(BASE_URL, {
    ...filters,
  })

  await api.sendRequest(url)
  return response(api)
}

//export csv function
export async function exportAtsSummaryReportCsv(filters = {}) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/export-csv`, {
    ...filters,
  })

  await api.sendRequest(url)
  return response(api)
}
