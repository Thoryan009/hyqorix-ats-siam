export function parseSubLedgerPassports(value) {
  return String(value ?? '')
    .split(',')
    .map((item) => item.trim().toUpperCase())
    .filter(Boolean)
}

export function formatSubLedgerPassports(passports = []) {
  return passports
    .map((item) => String(item ?? '').trim().toUpperCase())
    .filter(Boolean)
    .join(', ')
}
