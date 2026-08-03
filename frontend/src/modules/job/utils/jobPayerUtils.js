export default function getJobPayerOptions(t) {
  return [
    {
      id: 'agent',
      label: t('shared.labels.agent'),
      description: t('application.agent_pays_recruitment_charges'),
      icon: 'fa fa-user',
      iconBg: 'bg-blue-100 text-blue-700',
      activeRing: 'ring-blue-500',
      activeBorder: 'border-blue-500',
      activeBg: 'bg-blue-50',
    },
    {
      id: 'client',
      label: t('shared.labels.client'),
      description: t('application.client_pays_in_usd_pricing'),
      icon: 'fa fa-building',
      iconBg: 'bg-orange-100 text-orange-700',
      activeRing: 'ring-orange-500',
      activeBorder: 'border-orange-500',
      activeBg: 'bg-orange-50',
    },
    {
      id: 'candidate',
      label: t('shared.labels.candidate'),
      description: t('application.candidate_pays_in_bdt_pricing'),
      icon: 'fa fa-id-card',
      iconBg: 'bg-emerald-100 text-emerald-700',
      activeRing: 'ring-emerald-500',
      activeBorder: 'border-emerald-500',
      activeBg: 'bg-emerald-50',
    },
  ]
}

export function normalizeJobPayers(value) {
  if (Array.isArray(value)) {
    return [...new Set(value.map((item) => String(item).trim().toLowerCase()).filter(Boolean))]
  }

  if (typeof value === 'string' && value.trim()) {
    if (value === 'both') return ['client', 'candidate']
    return [value.trim().toLowerCase()]
  }

  return []
}

export function formatJobPayers(value, t) {
  return normalizeJobPayers(value)
    .map((item) => {
      switch (item) {
        case 'agent':
          return t('shared.labels.agent')
        case 'client':
          return t('shared.labels.client')
        case 'candidate':
          return t('shared.labels.candidate')
        default:
          return item
      }
    })
    .join(', ')
}

export function hasJobPayer(value, payerId) {
  return normalizeJobPayers(value).includes(String(payerId).toLowerCase())
}
