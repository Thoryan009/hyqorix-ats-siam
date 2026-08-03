export function truncateText(text, maxLength = 20) {
  if (!text) return '—'
  if (text.length <= maxLength) return text
  return `${text.slice(0, maxLength - 3)}...`
}

export function formatClientsSummary(clients = []) {
  const names = clients.filter(Boolean)

  if (!names.length) {
    return { display: '—', full: '' }
  }

  const full = names.join(', ')

  if (names.length === 1) {
    return { display: names[0], full }
  }

  return {
    display: `${truncateText(names[0], 16)} +${names.length - 1}`,
    full,
  }
}
