export const hiddenColumnsMap = {
  client: ['client', 'agent', 'principal', 'mobile'],
  agent: ['agent', 'principal'],
  principal: ['principal', 'agent', 'mobile'],
}

export const filterColumns = (columns, type) => {
  const hidden = hiddenColumnsMap[type] || []
  return columns.filter(col => !hidden.includes(col.key))
}

// 🔥 reusable title builder
export const buildReportTitle = ({
  jobName,
  workOrderCode,
  countryName,
  clientName,
  agentName,
  principalName,
  fromDate,
  toDate,
  formatDate,
}) => {
  const parts = []

  if (jobName) parts.push(jobName)
  if (workOrderCode) parts.push(workOrderCode)
  if (countryName) parts.push(countryName.replaceAll('_', ' '))
  if (clientName) parts.push(clientName.replaceAll('_', ' '))
  if (agentName) parts.push(agentName.replaceAll('_', ' '))
  if (principalName) parts.push(principalName.replaceAll('_', ' '))

  if (fromDate || toDate) {
    const dateText = `${formatDate(fromDate) || 'Start'} → ${
      formatDate(toDate) || 'Now'
    }`
    parts.push(dateText)
  }

  return parts.length ? `${parts.join(' | ')} - Report` : 'Report'
}
