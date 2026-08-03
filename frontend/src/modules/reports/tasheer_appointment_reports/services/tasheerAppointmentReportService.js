import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

const BASE_URL = '/reports/tasheer-appointment'

const response = (api) => ({
  data: api.data.value,
  error: api.error.value,
})

export async function fetchTasheerAppointmentReport(filters = {}) {
  const api = useApi()
  const url = buildUrl(BASE_URL, {
    ...filters,
  })

  await api.sendRequest(url)
  return response(api)
}

export function getTasheerAppointmentExportUrl(filters = {}) {
  return buildUrl(`${BASE_URL}/export-csv`, {
    ...filters,
  })
}

export async function exportTasheerAppointmentCsv(filters = {}) {
  const api = useApi()

  await api.sendRequest(getTasheerAppointmentExportUrl(filters), 'GET', null, {
    responseType: 'blob',
  })

  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function fetchTasheerFilterData() {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/filter-data`)
  return response(api)
}

export async function bulkStatusUpdate({ ids, status }) {
  const api = useApi()
  await api.sendRequest(`${BASE_URL}/bulk-status-update`, 'POST', {
    ids,
    status,
  })
  return response(api)
}
