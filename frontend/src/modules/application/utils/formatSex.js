export const formatSex = (sex) => {
  if (!sex) return ''

  const map = {
    M: 'male',
    F: 'female',
    MALE: 'male',
    FEMALE: 'female',
  }

  return map[sex.toUpperCase()] || 'other'
}
