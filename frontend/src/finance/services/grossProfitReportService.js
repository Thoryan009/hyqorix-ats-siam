import { useApi } from '@/shared/composables/useApi'
import { buildUrl } from '@/shared/utils/buildUrl'

const BASE_URL = '/finance-reports/gross-profit'

function downloadBlob(blob, filename) {
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.setAttribute('download', filename)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)
}

export async function fetchGrossProfitReport(filters = {}) {
  const api = useApi()
  const url = buildUrl(BASE_URL, filters)
  await api.sendRequest(url)
  if (api.error.value) throw api.error.value
  return api.data.value
}

export async function exportGrossProfitCsv(filters = {}) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/export-csv`, filters)
  await api.sendRequest(url, 'GET', null, { responseType: 'blob' })
  if (api.error.value) throw api.error.value
  const blob = api.data.value instanceof Blob
    ? api.data.value
    : new Blob([api.data.value], { type: 'text/csv;charset=utf-8;' })
  downloadBlob(blob, `gross-profit-report-${new Date().toISOString().slice(0, 10)}.csv`)
}

export async function exportGrossProfitPdf(filters = {}) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/export-pdf`, filters)
  await api.sendRequest(url, 'GET', null, { responseType: 'blob' })
  if (api.error.value) throw api.error.value
  const blob = api.data.value instanceof Blob
    ? api.data.value
    : new Blob([api.data.value], { type: 'application/pdf' })
  downloadBlob(blob, `gross-profit-report-${new Date().toISOString().slice(0, 10)}.pdf`)
}
