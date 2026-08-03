export default function getTableColumns(t, userType) {
  const baseTableColumns = [
    { key: 'application_id', label: t('application.app_id') },
    { key: 'worker_image_url', label: t('application.image') },
    { key: 'full_name', label: t('application.name') },
    { key: 'passport_no', label: t('shared.labels.passport_no') },
    { key: 'place_of_birth', label: t('application.place_of_birth') },
    { key: 'job', label: t('shared.labels.job') },
    { key: 'client', label: t('shared.labels.client') },
    { key: 'country_name', label: t('shared.labels.country') },
    { key: 'work_order_id', label: t('shared.labels.demand_letter') },
    { key: 'agent_name', label: t('shared.labels.agent') },
    { key: 'mobile', label: t('shared.labels.phone') },
    { key: 'application_status', label: t('shared.labels.status') },
  ]

  const hiddenColumnsByRole = {
    agent: ['payer'],
    client: ['agent_name', 'payer', 'mobile'],
  }

  const hiddenColumns = hiddenColumnsByRole[userType] || []

  return baseTableColumns.filter(
    column => !hiddenColumns.includes(column.key)
  )
}
