import { fetchAll as fetchClients } from '@/modules/client/services/clientService'
import { fetchAll as fetchAgents } from '@/modules/agent/services/agentService'
import { fetchAll as fetchPrincipals } from '@/modules/principal/services/principalService'
import { fetchAll as fetchVendors } from '@/modules/vendor/services/vendorService'
import { fetchAll as fetchEmployees } from '@/modules/work_order/services/employeeService'
import { fetchPartySourceOptions } from '@/modules/parties/services/partyService'

export const partySourceConfigs = {
  Client: {
    selectLabelKey: 'parties.select_client',
    fetch: () => fetchClients(1, 500, {}),
    mapItem: (row) => ({
      id: row.id,
      code: row.client_id,
      name: row.name,
    }),
  },
  Principal: {
    selectLabelKey: 'parties.select_principal',
    fetch: () => fetchPrincipals(1, 500, {}),
    mapItem: (row) => ({
      id: row.id,
      code: row.principal_id,
      name: row.organization_name,
    }),
  },
  Agent: {
    selectLabelKey: 'parties.select_agent',
    fetch: () => fetchAgents(1, 500, {}),
    mapItem: (row) => ({
      id: row.id,
      code: row.agent_id,
      name: row.name,
    }),
  },
  Candidate: {
    selectLabelKey: 'parties.select_candidate',
    fetch: (filters = {}) => fetchPartySourceOptions('Candidate', filters),
    requiresJobFilter: true,
    mapItem: (row) => ({
      id: row.id,
      code: row.passport_no,
      name: row.full_name,
      applicationId: row.application_id,
    }),
  },
  Vendor: {
    selectLabelKey: 'parties.select_vendor',
    fetch: () => fetchVendors(1, 500, {}),
    mapItem: (row) => ({
      id: row.id,
      code: row.vendor_id,
      name: row.organization_name ?? row.name,
    }),
  },
  Staff: {
    selectLabelKey: 'parties.select_staff',
    fetch: () => fetchEmployees(1, 500, {}),
    mapItem: (row) => ({
      id: row.id,
      code: row.username,
      name: row.name,
    }),
  },
}

export function hasPartySource(partyType) {
  return Boolean(partySourceConfigs[partyType])
}

export function requiresPartyJobFilter(partyType) {
  return Boolean(partySourceConfigs[partyType]?.requiresJobFilter)
}

export function getPartySourceConfig(partyType) {
  return partySourceConfigs[partyType] ?? null
}
