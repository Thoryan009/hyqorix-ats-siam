export function mapDemandLetterFromApi(row) {
  return {
    id: row.id,
    dl_no: row.work_order_id ?? '',
    client_name: row.client_name ?? row.client ?? '',
    country: row.country ?? '',
    jobs_count: Number(row.jobs_count) || 0,
    applications_count: Number(row.applications_count) || 0,
    status: 'Active',
  }
}

export function sortDemandLettersAlphabetically(demandLetters = []) {
  return [...demandLetters].sort((left, right) =>
    left.dl_no.localeCompare(right.dl_no, undefined, { sensitivity: 'base' })
  )
}

function formatApplicationsCount(count) {
  const total = Number(count) || 0
  if (total <= 0) return ''

  return ` · ${total} ${total === 1 ? 'Application' : 'Applications'}`
}

export function formatDemandLetterLabel(demandLetter) {
  if (!demandLetter) return ''
  return `${demandLetter.dl_no} (${demandLetter.client_name})`
}

export function formatDemandLetterSelectOption(demandLetter) {
  const applicationsSuffix = formatApplicationsCount(demandLetter.applications_count)

  return {
    id: demandLetter.id,
    name: `${demandLetter.dl_no} (${demandLetter.client_name})${applicationsSuffix}`,
  }
}
