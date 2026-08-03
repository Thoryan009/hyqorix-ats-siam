export const principalMasterList = [
  {
    id: 1,
    principal_code: 'PRC001',
    principal_name: 'Al Noor Overseas',
    phone: '+880 1611223344',
  },
  {
    id: 2,
    principal_code: 'PRC002',
    principal_name: 'Gulf Star Manpower',
    phone: '+880 1722334455',
  },
  {
    id: 3,
    principal_code: 'PRC003',
    principal_name: 'Royal Workforce LLC',
    phone: '+880 1833445566',
  },
  {
    id: 4,
    principal_code: 'PRC004',
    principal_name: 'Eastern Employment Co',
    phone: '+880 1644556677',
  },
  {
    id: 5,
    principal_code: 'PRC005',
    principal_name: 'Prime Gulf Recruiters',
    phone: '+880 1755667788',
  },
]

export function loadPrincipalMasterList(saved) {
  if (!Array.isArray(saved) || saved.length === 0) {
    return structuredClone(principalMasterList)
  }

  return principalMasterList.map((base) => {
    const existing = saved.find((item) => item.id === base.id)
    if (!existing) return structuredClone(base)

    return {
      ...base,
      ...existing,
      principal_code: base.principal_code,
      principal_name: base.principal_name,
      phone: base.phone,
    }
  })
}
