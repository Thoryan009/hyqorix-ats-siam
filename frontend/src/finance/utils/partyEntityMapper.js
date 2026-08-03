export function mapAgentFromApi(row) {
  return {
    id: row.id,
    agent_code: row.agent_id,
    agent_name: row.name,
    phone: row.phone ?? '',
    status: row.status,
  }
}

export function mapPrincipalFromApi(row) {
  return {
    id: row.id,
    principal_code: row.principal_id,
    principal_name: row.organization_name,
    phone: row.contact_no ?? '',
  }
}

export function mapClientFromApi(row) {
  return {
    id: row.id,
    client_code: row.client_id,
    client_name: row.name,
    phone: row.phone ?? '',
    status: row.status,
  }
}

export function mapStaffFromApi(row) {
  return {
    id: row.id,
    staff_code: row.username,
    staff_name: row.name,
    phone: row.phone ?? '',
    status: row.status,
  }
}

export function mapVendorFromApi(row) {
  return {
    id: row.id,
    vendor_code: row.vendor_id,
    vendor_name: row.organization_name,
    phone: row.phone ?? '',
    status: row.status,
  }
}
