
// modules/job/utils/tabStyles.js
export const colorClasses = Object.freeze({
   blue: {
    active: 'bg-blue-600 text-white border-blue-100',
    inactive: 'bg-blue-100 text-blue-700 hover:bg-blue-200',
    badgeActive: 'bg-white/20 text-white',
    badgeInactive: 'bg-blue-200 text-blue-800',
  },
  green: {
    active: 'bg-green-600 text-white border-green-100',
    inactive: 'bg-green-100 text-green-700 hover:bg-green-200',
    badgeActive: 'bg-white/20 text-white',
    badgeInactive: 'bg-green-200 text-green-800',
  },
  purple: {
    active: 'bg-purple-600 text-white border-purple-100',
    inactive: 'bg-purple-100 text-purple-700 hover:bg-purple-200',
    badgeActive: 'bg-white/20 text-white',
    badgeInactive: 'bg-purple-200 text-purple-800',
  },
  red: {
    active: 'bg-red-600 text-white border-red-100',
    inactive: 'bg-red-100 text-red-700 hover:bg-red-200',
    badgeActive: 'bg-white/20 text-white',
    badgeInactive: 'bg-red-200 text-red-800',
  },
  yellow: {
    active: 'bg-yellow-600 text-white border-yellow-100',
    inactive: 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200',
    badgeActive: 'bg-white/20 text-white',
    badgeInactive: 'bg-yellow-200 text-yellow-800',
  },
  gray: {
    active: 'bg-gray-600 text-white border-gray-100',
    inactive: 'bg-gray-100 text-gray-700 hover:bg-gray-200',
    badgeActive: 'bg-white/20 text-white',
    badgeInactive: 'bg-gray-200 text-gray-800',
  },
})

export const getTabClasses = (tab, modelValue) => {
  const selectedColor = colorClasses[tab?.color] ?? colorClasses.blue
  return modelValue === tab?.value
    ? selectedColor.active
    : selectedColor.inactive
}

export const getTabBadgeClasses = (tab, modelValue) => {
  const color = colorClasses[tab.color] || colorClasses.blue

  return modelValue === tab.value
    ? color.badgeActive
    : color.badgeInactive
}
