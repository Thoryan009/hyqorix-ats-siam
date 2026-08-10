import { defineStore } from 'pinia'
import { ref } from 'vue'
import { fetchAll as fetchJobs } from '@/modules/job/services/jobService'
import { fetchAll as fetchApplications } from '@/modules/application/services/applicationService'
import { fetchAll as fetchAgents } from '@/modules/agent/services/agentService'
import { fetchSaleCollectionSummary } from '@/finance/services/financeAccountService'
import { extractPaginatedRows } from '@/shared/utils/extractPaginatedRows'
import {
  mapApplicationFromApi,
  mapJobFromApi,
  mapSalePayerAgentFromApi,
  matchesExpenseHead,
  formatJobSelectOption,
  formatSalePayerAgentSelectOption,
  sortJobsAlphabetically,
  sortApplicationsAlphabetically,
  FINANCE_ATS_APPLICATION_STATUS,
} from '../utils/jobListMapper'

/** Still shown on Sale Entry Due Bills (not fully paid, not on Bills Receivable). */
function isApplicationStillCollectable(summary) {
  if (!summary) return true
  if (Boolean(summary.has_due)) return false

  const salePrice = Number(summary.sale_price) || 0
  const remaining =
    summary.receivable_remaining != null
      ? Number(summary.receivable_remaining) || 0
      : Math.max(0, salePrice - (Number(summary.collected_amount) || 0))

  if (salePrice <= 0) return true
  return remaining > 0
}

async function loadSaleCollectionSummaryById(applicationIds) {
  const ids = [...new Set(applicationIds.map((id) => Number(id)).filter((id) => id > 0))]
  if (!ids.length) return {}

  const summaryById = {}
  const chunkSize = 200

  for (let index = 0; index < ids.length; index += chunkSize) {
    const chunk = ids.slice(index, index + chunkSize)
    try {
      const rows = await fetchSaleCollectionSummary(chunk)
      for (const row of rows) {
        summaryById[Number(row.application_id)] = row
      }
    } catch {
      // Keep counting ATS applicants if summary API fails.
    }
  }

  return summaryById
}

export const useJobListStore = defineStore('financeJobList', () => {
  const jobs = ref([])
  const applicationsByJob = ref({})
  const agentJobIds = ref([])
  const agentJobApplicantCounts = ref({})
  const salePayerAgents = ref([])
  const paymentResponsibilityJobIds = ref({
    candidate: [],
    client: [],
  })
  const paymentResponsibilityJobCounts = ref({
    candidate: {},
    client: {},
  })
  const salePayerClientMasterIds = ref([])
  const clientJobApplicantCounts = ref({})

  const isLoadingJobs = ref(false)
  const isLoadedJobs = ref(false)
  const loadingApplications = ref({})
  const loadingAgentJobs = ref(false)
  const loadingPaymentResponsibilityJobs = ref({
    candidate: false,
    client: false,
  })
  const isLoadingSalePayerAgents = ref(false)
  const isLoadedSalePayerAgents = ref(false)
  const isLoadingSalePayerClients = ref(false)
  const isLoadedSalePayerClients = ref(false)

  async function fetchJobList(force = false) {
    if (isLoadingJobs.value) return
    if (isLoadedJobs.value && !force) return

    isLoadingJobs.value = true

    try {
      const { data, error } = await fetchJobs(1, 500, {
        status: 'open',
        has_ats_applications: 1,
        include_ats_count: 1,
      })
      if (error) throw error
      jobs.value = sortJobsAlphabetically(
        extractPaginatedRows(data)
          .map(mapJobFromApi)
          .filter((job) => job.status === 'open' && job.ats_applications_count > 0)
      )
      isLoadedJobs.value = true
    } catch {
      jobs.value = []
    } finally {
      isLoadingJobs.value = false
    }
  }

  async function fetchApplicationsForJob(jobId, force = false) {
    const id = Number(jobId)
    if (!id) return

    if (loadingApplications.value[id]) return
    if (applicationsByJob.value[id] && !force) return

    loadingApplications.value = { ...loadingApplications.value, [id]: true }

    try {
      const { data, error } = await fetchApplications(1, 500, {
        job_list_id: id,
        application_status: FINANCE_ATS_APPLICATION_STATUS,
        exclude_rejected_declined: 1,
      })
      if (error) throw error

      const rows = sortApplicationsAlphabetically(
        extractPaginatedRows(data).map(mapApplicationFromApi)
      )

      applicationsByJob.value = {
        ...applicationsByJob.value,
        [id]: rows,
      }
    } catch {
      applicationsByJob.value = {
        ...applicationsByJob.value,
        [id]: [],
      }
    } finally {
      const next = { ...loadingApplications.value }
      delete next[id]
      loadingApplications.value = next
    }
  }

  async function fetchJobIdsForAgent(agentId, force = false) {
    const id = Number(agentId)
    if (!id) {
      agentJobIds.value = []
      agentJobApplicantCounts.value = {}
      return
    }

    if (loadingAgentJobs.value && !force) return

    loadingAgentJobs.value = true

    try {
      const { data, error } = await fetchApplications(1, 500, {
        agent_id: id,
        application_status: FINANCE_ATS_APPLICATION_STATUS,
        payment_responsibility: 'agent',
        exclude_rejected_declined: 1,
      })
      if (error) throw error

      const rows = extractPaginatedRows(data)
      const applicationIds = rows.map((row) => Number(row.id)).filter((id) => id > 0)
      const summaryById = await loadSaleCollectionSummaryById(applicationIds)
      const counts = {}

      for (const row of rows) {
        const applicationId = Number(row.id)
        const jobId = Number(row.job_list_id)
        if (!jobId || !applicationId) continue
        if (!isApplicationStillCollectable(summaryById[applicationId])) continue
        counts[jobId] = (counts[jobId] || 0) + 1
      }

      agentJobApplicantCounts.value = counts
      agentJobIds.value = Object.keys(counts)
        .map(Number)
        .filter((jobId) => counts[jobId] > 0)
    } catch {
      agentJobIds.value = []
      agentJobApplicantCounts.value = {}
    } finally {
      loadingAgentJobs.value = false
    }
  }

  async function fetchJobIdsByPaymentResponsibility(payerType, force = false) {
    const type = String(payerType || '')
      .trim()
      .toLowerCase()

    if (!['candidate', 'client'].includes(type)) return

    if (loadingPaymentResponsibilityJobs.value[type] && !force) return

    loadingPaymentResponsibilityJobs.value = {
      ...loadingPaymentResponsibilityJobs.value,
      [type]: true,
    }

    try {
      const { data, error } = await fetchApplications(1, 500, {
        application_status: FINANCE_ATS_APPLICATION_STATUS,
        payment_responsibility: type,
        exclude_rejected_declined: 1,
      })
      if (error) throw error

      const rows = extractPaginatedRows(data)
      const applicationIds = rows.map((row) => Number(row.id)).filter((id) => id > 0)
      const summaryById = await loadSaleCollectionSummaryById(applicationIds)
      const counts = {}
      const clientIds = new Set()
      const countsByClient = {}

      for (const row of rows) {
        const applicationId = Number(row.id)
        const jobId = Number(row.job_list_id)
        if (!jobId || !applicationId) continue
        if (!isApplicationStillCollectable(summaryById[applicationId])) continue

        counts[jobId] = (counts[jobId] || 0) + 1

        if (type === 'client') {
          const clientId = Number(row.client_id)
          if (!clientId) continue
          clientIds.add(clientId)
          if (!countsByClient[clientId]) countsByClient[clientId] = {}
          countsByClient[clientId][jobId] = (countsByClient[clientId][jobId] || 0) + 1
        }
      }

      paymentResponsibilityJobCounts.value = {
        ...paymentResponsibilityJobCounts.value,
        [type]: counts,
      }
      paymentResponsibilityJobIds.value = {
        ...paymentResponsibilityJobIds.value,
        [type]: Object.keys(counts)
          .map(Number)
          .filter((jobId) => counts[jobId] > 0),
      }

      if (type === 'client') {
        // Keep clients that still have at least one collectable application.
        salePayerClientMasterIds.value = Object.keys(countsByClient)
          .map(Number)
          .filter((clientId) =>
            Object.values(countsByClient[clientId] || {}).some((count) => Number(count) > 0)
          )
        clientJobApplicantCounts.value = countsByClient
        isLoadedSalePayerClients.value = true
      }
    } catch {
      paymentResponsibilityJobCounts.value = {
        ...paymentResponsibilityJobCounts.value,
        [type]: {},
      }
      paymentResponsibilityJobIds.value = {
        ...paymentResponsibilityJobIds.value,
        [type]: [],
      }

      if (type === 'client') {
        salePayerClientMasterIds.value = []
        clientJobApplicantCounts.value = {}
      }
    } finally {
      loadingPaymentResponsibilityJobs.value = {
        ...loadingPaymentResponsibilityJobs.value,
        [type]: false,
      }
    }
  }

  async function fetchSalePayerClients(force = false) {
    if (isLoadingSalePayerClients.value) return
    if (isLoadedSalePayerClients.value && !force) return

    isLoadingSalePayerClients.value = true

    try {
      await fetchJobIdsByPaymentResponsibility('client', true)
    } finally {
      isLoadingSalePayerClients.value = false
    }
  }

  function isLoadingPaymentResponsibilityJobs(payerType) {
    const type = String(payerType || '')
      .trim()
      .toLowerCase()
    return Boolean(loadingPaymentResponsibilityJobs.value[type])
  }

  async function fetchSalePayerAgents(force = false) {
    if (isLoadingSalePayerAgents.value) return
    if (isLoadedSalePayerAgents.value && !force) return

    isLoadingSalePayerAgents.value = true

    try {
      const { data, error } = await fetchAgents(1, 500, {
        status: 1,
        has_ats_applications: 1,
        include_ats_count: 1,
      })
      if (error) throw error

      salePayerAgents.value = extractPaginatedRows(data)
        .map(mapSalePayerAgentFromApi)
        .sort((left, right) =>
          left.agent_name.localeCompare(right.agent_name, undefined, { sensitivity: 'base' })
        )
      isLoadedSalePayerAgents.value = true
    } catch {
      salePayerAgents.value = []
    } finally {
      isLoadingSalePayerAgents.value = false
    }
  }

  function getSalePayerAgentOptions() {
    return salePayerAgents.value.map(formatSalePayerAgentSelectOption)
  }

  function isLoadingApplications(jobId) {
    return Boolean(loadingApplications.value[Number(jobId)])
  }

  function getJob(jobId) {
    return jobs.value.find((job) => job.id === Number(jobId)) ?? null
  }

  function getApplication(applicationId) {
    const id = Number(applicationId)

    for (const applications of Object.values(applicationsByJob.value)) {
      const match = applications.find((application) => Number(application.id) === id)
      if (match) return match
    }

    return null
  }

  function getApplications(applicationIds = []) {
    const idSet = new Set(applicationIds.map((value) => Number(value)).filter(Boolean))
    return Object.values(applicationsByJob.value)
      .flat()
      .filter((application) => idSet.has(Number(application.id)))
  }

  function getApplicationsForJob(jobId, expenseHeadName = '', { filterByExpenseHead = false } = {}) {
    const applications = applicationsByJob.value[Number(jobId)] ?? []

    if (!filterByExpenseHead || !expenseHeadName?.trim()) {
      return sortApplicationsAlphabetically(applications)
    }

    const filtered = applications.filter(
      (application) =>
        matchesExpenseHead(application.process_status, expenseHeadName) ||
        matchesExpenseHead(application.status, expenseHeadName)
    )

    return sortApplicationsAlphabetically(filtered.length ? filtered : applications)
  }

  function getAvailableJobs(expenseHeadName = '') {
    if (!expenseHeadName?.trim()) {
      return jobs.value
    }

    return jobs.value.filter((job) =>
      getApplicationsForJob(job.id, expenseHeadName).length > 0
    )
  }

  function getJobSelectOptions() {
    return sortJobsAlphabetically(
      jobs.value.filter((job) => job.status === 'open' && job.ats_applications_count > 0)
    ).map(formatJobSelectOption)
  }

  function getSaleJobOptions(agentId = null) {
    const openJobs = jobs.value.filter((job) => job.status === 'open')

    if (!agentId) {
      return sortJobsAlphabetically(
        openJobs.filter((job) => job.ats_applications_count > 0)
      )
    }

    return sortJobsAlphabetically(
      openJobs
        .filter((job) => agentJobIds.value.includes(job.id))
        .map((job) => ({
          ...job,
          sale_applicants_count: Number(agentJobApplicantCounts.value[job.id]) || 0,
        }))
        .filter((job) => job.sale_applicants_count > 0)
    )
  }

  function getSaleJobOptionsByPaymentResponsibility(payerType) {
    const type = String(payerType || '')
      .trim()
      .toLowerCase()
    const jobIds = paymentResponsibilityJobIds.value[type] ?? []
    const counts = paymentResponsibilityJobCounts.value[type] ?? {}

    return sortJobsAlphabetically(
      jobs.value
        .filter((job) => job.status === 'open' && jobIds.includes(job.id))
        .map((job) => ({
          ...job,
          sale_applicants_count: Number(counts[job.id]) || 0,
        }))
        .filter((job) => job.sale_applicants_count > 0)
    )
  }

  function getSaleJobOptionsForClient(masterClientId) {
    const clientId = Number(masterClientId)
    if (!clientId) return []

    const counts = clientJobApplicantCounts.value[clientId] ?? {}
    const jobIds = Object.keys(counts)
      .map(Number)
      .filter((jobId) => counts[jobId] > 0)

    return sortJobsAlphabetically(
      jobs.value
        .filter((job) => job.status === 'open' && jobIds.includes(job.id))
        .map((job) => ({
          ...job,
          sale_applicants_count: Number(counts[job.id]) || 0,
        }))
        .filter((job) => job.sale_applicants_count > 0)
    )
  }

  function getSalePayerClientMasterIds() {
    return salePayerClientMasterIds.value
  }

  return {
    jobs,
    applicationsByJob,
    agentJobIds,
    agentJobApplicantCounts,
    paymentResponsibilityJobIds,
    paymentResponsibilityJobCounts,
    salePayerClientMasterIds,
    clientJobApplicantCounts,
    salePayerAgents,
    isLoadingJobs,
    isLoadingApplications,
    loadingApplications,
    loadingAgentJobs,
    loadingPaymentResponsibilityJobs,
    isLoadingSalePayerAgents,
    isLoadingSalePayerClients,
    fetchJobList,
    fetchApplicationsForJob,
    fetchJobIdsForAgent,
    fetchJobIdsByPaymentResponsibility,
    isLoadingPaymentResponsibilityJobs,
    fetchSalePayerAgents,
    fetchSalePayerClients,
    getJob,
    getApplication,
    getApplications,
    getApplicationsForJob,
    getAvailableJobs,
    getJobSelectOptions,
    getSaleJobOptions,
    getSaleJobOptionsByPaymentResponsibility,
    getSaleJobOptionsForClient,
    getSalePayerAgentOptions,
    getSalePayerClientMasterIds,
  }
})
