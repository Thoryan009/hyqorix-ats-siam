export const groupPermissions = (permissions) => {
  const groups = {}

  permissions.forEach(p => {
    // 🔥 split by space
    const words = p.name.split(' ')

    // 🔥 last word = module name
    let module = words[words.length - 1] || 'General'

    // 🔥 clean format (Service_package → Service Package)
    module = module
      .replace(/_/g, ' ')
      .split(' ')
      .map(w => w.charAt(0).toUpperCase() + w.slice(1))
      .join(' ')

    if (!groups[module]) groups[module] = []

    groups[module].push(p)
  })

  return Object.entries(groups).map(([label, items]) => ({
    label,
    items
  }))
}
