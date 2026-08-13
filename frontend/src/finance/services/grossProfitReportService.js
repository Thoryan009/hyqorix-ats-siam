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

function unwrapPayload(payload) {
  return payload?.data ?? payload
}

function decodePdfPayload(payload) {
  const content = payload?.content
  if (!content) throw new Error('No data available for PDF export.')

  const binary = atob(content)
  const bytes = new Uint8Array(binary.length)
  for (let i = 0; i < binary.length; i += 1) {
    bytes[i] = binary.charCodeAt(i)
  }

  return {
    blob: new Blob([bytes], { type: 'application/pdf' }),
    filename:
      payload.filename || `gross-profit-report-${new Date().toISOString().slice(0, 10)}.pdf`,
  }
}

export async function fetchGrossProfitPdf(filters = {}) {
  const api = useApi()
  const url = buildUrl(`${BASE_URL}/export-pdf`, filters)
  await api.sendRequest(url)
  if (api.error.value) throw api.error.value
  return decodePdfPayload(unwrapPayload(api.data.value))
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
  await api.sendRequest(url)
  if (api.error.value) throw api.error.value

  const payload = unwrapPayload(api.data.value)
  const content = payload?.content
  if (!content) throw new Error('No data available for CSV export.')

  downloadBlob(
    new Blob([content], { type: 'text/csv;charset=utf-8;' }),
    payload.filename || `gross-profit-report-${new Date().toISOString().slice(0, 10)}.csv`
  )
}

export async function exportGrossProfitPdf(filters = {}) {
  const { blob, filename } = await fetchGrossProfitPdf(filters)
  downloadBlob(blob, filename)
}
