import { computed, unref } from 'vue'

export const expiryStatusCardConfig = [
  {
    id: 'total',
    processId: null,
    title: 'Total',
    icon: 'fa fa-list-alt',
    color: '#B400C9',
  },
  {
    id: 'medical_test',
    processId: 'medical_test',
    title: 'Medical',
    icon: 'fa fa-medkit',
    color: '#10b981',
  },
  {
    id: 'police_clearance',
    processId: 'police_clearance',
    title: 'Police Clearance',
    icon: 'fa fa-shield',
    color: '#3b82f6',
  },
  {
    id: 'embassy_submission',
    processId: 'embassy_submission',
    title: 'Embassy Submission (Visa)',
    icon: 'fa fa-id-card',
    color: '#f59e0b',
  },
]

export function useExpiryStatusCards(processOptionsRef) {
  const getDocumentCount = (processId) => {
    const options = unref(processOptionsRef) ?? []

    if (!processId) {
      return options.reduce((sum, item) => sum + (item.applications_count ?? 0), 0)
    }

    const document = options.find((item) => item.id === processId)
    return document?.applications_count ?? 0
  }

  const statusCards = computed(() =>
    expiryStatusCardConfig.map((card) => ({
      ...card,
      value: getDocumentCount(card.processId),
    })),
  )

  return {
    statusCards,
    getDocumentCount,
  }
}
