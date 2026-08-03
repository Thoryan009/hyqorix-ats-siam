import { useJobListStore } from '../store/jobListStore'

export { DIRECT_COST_CATEGORY_CODE, DIRECT_COST_CATEGORY_CODE as DIRECT_COST_CATEGORY_ID } from './expenseCategoryCodes'

export const directCostApplicationStatuses = [
  'Medical',
  'Police Clearance',
  'Air Ticket',
  'MOFA',
  'Training',
  'Visa',
  'Manpower',
]

export function getDirectCostJob(jobId) {
  return useJobListStore().getJob(jobId)
}

export function getDirectCostApplication(applicationId) {
  return useJobListStore().getApplication(applicationId)
}

export function getDirectCostApplications(applicationIds = []) {
  return useJobListStore().getApplications(applicationIds)
}

export function getApplicationsForJob(jobId, expenseHeadName = '') {
  return useJobListStore().getApplicationsForJob(jobId, expenseHeadName)
}

export function getAvailableJobs(expenseHeadName = '') {
  return useJobListStore().getAvailableJobs(expenseHeadName)
}

export function getJobWiseApplications(expenseHeadName = '') {
  return getAvailableJobs(expenseHeadName).map((job) => ({
    job,
    applications: getApplicationsForJob(job.id, expenseHeadName),
  }))
}
