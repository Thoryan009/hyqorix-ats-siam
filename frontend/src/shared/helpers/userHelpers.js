export const getUserInitials = (name = '') => {
  if (!name) return ''
  return name
    .split(' ')
    .map(word => word[0])
    .slice(0, 2)
    .join('')
    .toUpperCase()
}

export const getFormattedUserType = (type = '') => {
  return type ? type.replace('_', ' ').toUpperCase() : ''
}

export const getUserTypeClasses = (type = '') => {
  const map = {
    super_admin: {
      badge: 'bg-purple-100 text-purple-700',
      avatar: 'bg-purple-600',
    },
    admin: {
      badge: 'bg-red-100 text-red-700',
      avatar: 'bg-red-600',
    },
    staff: {
      badge: 'bg-blue-100 text-blue-700',
      avatar: 'bg-blue-600',
    },
    default: {
      badge: 'bg-gray-100 text-gray-700',
      avatar: 'bg-gray-600',
    },
  }

  return map[type] || map.default
}
