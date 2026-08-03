export const formatDate = (date) => {
  if (!date) return ''

  return new Date(date).toLocaleDateString('en-GB', {
    weekday: 'short',
    day: '2-digit',
    month: 'short',
    year: '2-digit'
  })
}

