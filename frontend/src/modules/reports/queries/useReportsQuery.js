import { useQuery } from '@tanstack/vue-query'
import {
  fetchStatus,
  fetchMethods,
  fetchJobs,
  fetchProcess,
  fetchWorkOrders,
  fetchClients,
  fetchCountries,
  fetchReportData,
  fetchAgents,
  fetchReportPrincipals

} from '../services/reportService'

// Report Process
export function useReportProcessQuery() {
  return useQuery({
    queryKey: ['report-processes', 'v2'],
    queryFn: fetchProcess,
    staleTime: Infinity, // rarely changes
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}

// Report Work Orders
export function useReportWorkOrdersQuery() {
  return useQuery({
    queryKey: ['report-work-orders', 'v2'],
    queryFn: fetchWorkOrders,
    staleTime: Infinity, // rarely changes
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}

// Report Jobs
export function useReportJobsQuery() {
  return useQuery({
    queryKey: ['report-jobs', 'v2'],
    queryFn: fetchJobs,
    staleTime: Infinity, // rarely changes
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}

// Payment Status
export function useReportStatusQuery() {
  return useQuery({
    queryKey: ['report-transaction-status', 'v2'],
    queryFn: fetchStatus,
    staleTime: Infinity, // rarely changes
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}

// Payment Method
export function useReportMethodsQuery() {
  return useQuery({
    queryKey: ['report-payment-methods', 'v2'],
    queryFn: fetchMethods,
    staleTime: Infinity, // rarely changes
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}

// Client
export function useReportClientsQuery() {
  return useQuery({
    queryKey: ['report-clients', 'v2'],
    queryFn: fetchClients,
    staleTime: Infinity, // rarely changes
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}


// Country
export function useReportCountryQuery() {
  return useQuery({
    queryKey: ['report-countries', 'v2'],
    queryFn: fetchCountries,
    staleTime: Infinity, // rarely changes
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}

export function useReportAgentsQuery() {
  return useQuery({
    queryKey: ['report-agents', 'v2'],
    queryFn: fetchAgents,
    staleTime: Infinity, // rarely changes
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}

// report-data
export function useReportDataQuery() {
  return useQuery({
    queryKey: ['report-data'],
    queryFn: fetchReportData,
    staleTime: Infinity, // rarely changes
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}

// report-principals
export function useReportPrincipalsQuery() {
  return useQuery({
    queryKey: ['report-principals', 'v2'],
    queryFn: fetchReportPrincipals,
    staleTime: Infinity, // rarely changes
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}
