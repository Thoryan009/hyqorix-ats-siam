export const formatDateToISO = (dateStr) => {
  if (!dateStr) return ''

  const months = {
    JAN: '01',
    FEB: '02',
    MAR: '03',
    APR: '04',
    MAY: '05',
    JUN: '06',
    JUL: '07',
    AUG: '08',
    SEP: '09',
    OCT: '10',
    NOV: '11',
    DEC: '12',
  }

  const parts = dateStr.split(' ')
  if (parts.length !== 3) return ''

  const [day, mon, year] = parts
  const month = months[mon.toUpperCase()]

  if (!month) return ''

  return `${year}-${month}-${day.padStart(2, '0')}`
}
