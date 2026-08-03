export const clientMasterList = [
  {
    id: 1,
    client_code: 'CLT001',
    client_name: 'Bengal Trade International',
    phone: '+880 1719001122',
  },
  {
    id: 2,
    client_code: 'CLT002',
    client_name: 'Metro Hiring Solutions',
    phone: '+880 1829002233',
  },
  {
    id: 3,
    client_code: 'CLT003',
    client_name: 'Summit Overseas Ltd',
    phone: '+880 1939003344',
  },
  {
    id: 4,
    client_code: 'CLT004',
    client_name: 'Horizon Recruitment Co',
    phone: '+880 1649004455',
  },
  {
    id: 5,
    client_code: 'CLT005',
    client_name: 'Northern Staffing Group',
    phone: '+880 1759005566',
  },
  {
    id: 6,
    client_code: 'CLT006',
    client_name: 'Capital Overseas Partners',
    phone: '+880 1869006677',
  },
]

export function loadClientMasterList(saved) {
  if (!Array.isArray(saved) || saved.length === 0) {
    return structuredClone(clientMasterList)
  }

  return clientMasterList.map((base) => {
    const existing = saved.find((item) => item.id === base.id)
    if (!existing) return structuredClone(base)

    return {
      ...base,
      ...existing,
      client_code: base.client_code,
      client_name: base.client_name,
      phone: base.phone,
    }
  })
}
