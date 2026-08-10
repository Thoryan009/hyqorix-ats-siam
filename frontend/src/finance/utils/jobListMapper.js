const expenseHeadMatchers = {
  medical: ['medical'],
  police_clearance: ['police'],
  air_ticket: ['pta', 'ticket', 'air'],
  mofa: ['mofa', 'embassy', 'embassy_submission'],
  training: ['training'],
  visa: ['visa'],
  manpower: ['manpower', 'bmet', 'immigration'],
  commission: ['commission'],
}

function normalizeKey(value) {
  return String(value || '')
    .trim()
    .toLowerCase()
    .replace(/\s+/g, '_')
}

export const FINANCE_ATS_APPLICATION_STATUS = 'ATS'

export function mapJobFromApi(row) {
  return {
    id: row.id,
    job_name: row.name,
    job_title: row.name,
    job_code: row.job_code,
    client_name: row.client_name ?? '',
    client: row.client_name ?? '',
    demand_letter: row.work_order ?? '',
    work_order_id: row.work_order_id,
    price: Number(row.price) || 0,
    client_commission_per_candidate: Number(row.client_commission_per_candidate) || 0,
    status: row.status ?? '',
    ats_applications_count: Number(row.ats_applications_count) || 0,
  }
}

export function sortJobsAlphabetically(jobs = []) {
  return [...jobs].sort((left, right) =>
    left.job_name.localeCompare(right.job_name, undefined, { sensitivity: 'base' })
  )
}

export function mapApplicationFromApi(row) {
  const computedApplicationStatus = String(row.application_status ?? '').trim()
  const rawApplicationStatus = String(row.raw_application_status ?? '').trim()
  const processName = String(row.status ?? '').trim()
  const normalizedComputed = computedApplicationStatus.toLowerCase()
  const isRejectedOrDeclined =
    normalizedComputed === 'rejected' || normalizedComputed === 'declined'

  // Prefer computed application_status (can be rejected/declined) over raw ATS bucket.
  const applicationStatus = computedApplicationStatus || rawApplicationStatus || ''
  const processStatus = isRejectedOrDeclined ? computedApplicationStatus : processName

  return {
    id: row.id,
    job_id: row.job_list_id,
    name: row.full_name,
    candidate_name: row.full_name,
    passport_no: row.passport_no ?? '',
    status: processStatus || processName,
    application_status: applicationStatus,
    process_status: processStatus || processName,
    agent_id: row.agent_id,
    client_id: row.client_id ?? null,
    payment_responsibility: Array.isArray(row.payment_responsibility)
      ? row.payment_responsibility
      : Array.isArray(row.payer)
        ? row.payer
        : [],
    application_price: Number(row.application_price) || 0,
    sale_price: Number(row.application_price) || 0,
    charge: Number(row.application_price) || 0,
  }
}

export function isRejectedOrDeclinedApplication(application) {
  const values = [
    application?.process_status,
    application?.status,
    application?.application_status,
  ]

  return values.some((value) => {
    const key = String(value || '').trim().toLowerCase()
    return key === 'rejected' || key === 'declined'
  })
}

export function sortApplicationsAlphabetically(applications = []) {
  return [...applications].sort((left, right) =>
    left.name.localeCompare(right.name, undefined, { sensitivity: 'base' })
  )
}

export function formatApplicationStatusLabel(status) {
  return String(status || '')
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (char) => char.toUpperCase())
}

export function matchesExpenseHead(applicationStatus, expenseHeadName = '') {
  const head = expenseHeadName?.trim()
  if (!head) return true

  const normalizedStatus = normalizeKey(applicationStatus)
  const normalizedHead = normalizeKey(head)

  if (!normalizedStatus) return false
  if (normalizedStatus === normalizedHead) return true
  if (normalizedStatus.includes(normalizedHead)) return true
  if (normalizedHead.includes(normalizedStatus)) return true

  const patterns = expenseHeadMatchers[normalizedHead] ?? [normalizedHead.split('_')[0]]
  return patterns.some((pattern) => normalizedStatus.includes(pattern))
}

function formatApplicantsCount(count) {
  const total = Number(count) || 0
  if (total <= 0) return ''

  return ` · ${total} ${total === 1 ? 'Applicant' : 'Applicants'}`
}

export function formatJobSelectOption(job) {
  const applicantsSuffix = formatApplicantsCount(job.ats_applications_count)

  return {
    id: job.id,
    name: `${job.job_name} (${job.job_code})${applicantsSuffix}`,
  }
}

export function formatSaleJobSelectOption(job) {
  const count =
    job.sale_applicants_count ??
    job.ats_applications_count
  const applicantsSuffix = formatApplicantsCount(count)

  return {
    id: job.id,
    name: `${job.job_code} - ${job.job_title}${applicantsSuffix}`,
  }
}

export function mapSalePayerAgentFromApi(row) {
  return {
    id: row.id,
    agent_code: row.agent_id ?? '',
    agent_name: row.name ?? '',
    ats_applications_count: Number(row.ats_applications_count) || 0,
    status: row.status,
  }
}

export function formatSalePayerAgentSelectOption(agent) {
  const applicantsSuffix = formatApplicantsCount(agent.ats_applications_count)
  const agentId = agent.agent_code || agent.id

  return {
    id: agent.id,
    agent_code: agent.agent_code,
    agent_name: agent.agent_name,
    ats_applications_count: agent.ats_applications_count,
    name: `${agentId} - ${agent.agent_name}${applicantsSuffix}`,
  }
}
