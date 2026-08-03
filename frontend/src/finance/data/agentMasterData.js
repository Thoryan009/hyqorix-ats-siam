export const agentMasterList = [
  {
    id: 1,
    agent_code: 'AGT001',
    agent_name: 'Al Falah Recruitment',
    phone: '+880 1712345678',
  },
  {
    id: 2,
    agent_code: 'AGT002',
    agent_name: 'Rahman Overseas Services',
    phone: '+880 1822334455',
  },
  {
    id: 3,
    agent_code: 'AGT003',
    agent_name: 'Global Manpower Solutions',
    phone: '+880 1933445566',
  },
  {
    id: 4,
    agent_code: 'AGT004',
    agent_name: 'Prime HR Consultancy',
    phone: '01644556677',
  },
  {
    id: 5,
    agent_code: 'AGT005',
    agent_name: 'Elite Placement House',
    phone: '01555667788',
  },
  {
    id: 6,
    agent_code: 'AGT006',
    agent_name: 'Horizon Overseas Agency',
    phone: '01711223344',
  },
  {
    id: 7,
    agent_code: 'AGT007',
    agent_name: 'Summit Talent Bureau',
    phone: '01822334455',
  },
]

export function loadAgentMasterList(saved) {
  if (!Array.isArray(saved) || saved.length === 0) {
    return structuredClone(agentMasterList)
  }

  return agentMasterList.map((base) => {
    const existing = saved.find((item) => item.id === base.id)
    if (!existing) return structuredClone(base)

    return {
      ...base,
      ...existing,
      agent_code: base.agent_code,
      agent_name: base.agent_name,
      phone: base.phone,
    }
  })
}
