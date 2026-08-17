import { ACCOUNT_CATEGORIES } from './accountCategoryCodes'
import { getPartyConfig } from '../config/partyAccountConfigs'

export const billPartyPaymentCategories = [
  ACCOUNT_CATEGORIES.CLIENT,
  ACCOUNT_CATEGORIES.VENDOR,
  ACCOUNT_CATEGORIES.PRINCIPAL,
]

export const billAccountCategoryOptions = [
  { id: 'main', name: 'Main Account' },
  { id: ACCOUNT_CATEGORIES.CLIENT, name: 'Client Account' },
  { id: ACCOUNT_CATEGORIES.VENDOR, name: 'Vendor Account' },
  { id: ACCOUNT_CATEGORIES.PRINCIPAL, name: 'Principal Account' },
  { id: 'staff', name: 'Staff Account' },
]

export const mainAccountTypeOptions = [
  { id: 'Cash', name: 'Cash' },
  { id: 'Bank', name: 'Bank' },
]

export function mapPaymentMethodToMainAccountType(paymentMethod) {
  if (paymentMethod === 'bank') return 'Bank'
  if (paymentMethod === 'cash') return 'Cash'
  return ''
}

export function isDuePaymentMethod(paymentMethod) {
  return paymentMethod === 'due'
}

export function isBillPartyPaymentCategory(category) {
  return billPartyPaymentCategories.includes(category)
}

export function getBillPaymentAccountTypeLabel(category) {
  if (category === 'staff') return 'Staff'
  if (isBillPartyPaymentCategory(category)) {
    return getPartyConfig(category)?.partyLabel ?? ''
  }
  return ''
}

export function mapBillPaymentAccountOption(account, formatCurrency) {
  const balance = formatCurrency(account.balance ?? account.current_balance ?? 0)

  if (account.category === ACCOUNT_CATEGORIES.MAIN) {
    return {
      id: account.id,
      name: `${account.account_type} — ${account.account_name} (${account.account_label}) — ${balance}`,
    }
  }

  if (account.category === ACCOUNT_CATEGORIES.STAFF) {
    return {
      id: account.id,
      name: `Staff — ${account.staff_code} — ${account.staff_name} — ${balance}`,
    }
  }

  const config = getPartyConfig(account.category)
  if (config) {
    const code = account[config.codeKey] ?? account.code ?? ''
    const name = account[config.nameKey] ?? account.account_name ?? ''

    return {
      id: account.id,
      name: `${config.partyLabel} — ${code} — ${name} — ${balance}`,
    }
  }

  return {
    id: account.id,
    name: `${account.account_name} — ${balance}`,
  }
}

export function getBillPaymentAccountName(account, category) {
  if (!account) return ''

  if (category === 'main') {
    return `${account.account_name} — ${account.account_label}`
  }

  if (category === 'staff') {
    return `${account.staff_code} — ${account.staff_name}`
  }

  const config = getPartyConfig(category)
  if (config) {
    const code = account[config.codeKey] ?? account.code ?? ''
    const name = account[config.nameKey] ?? account.account_name ?? ''
    return `${code} — ${name}`
  }

  return account.account_name ?? ''
}

export function getBillAccountCategoryLabel(category) {
  return billAccountCategoryOptions.find((option) => option.id === category)?.name ?? category
}

export function requiresAdvanceAdjustmentAsset(category) {
  return Boolean(category && category !== 'main')
}

export function mapAdvanceAdjustmentAssetAccountOption(account, formatCurrency) {
  const rawBalance = Number(account.balance ?? account.current_balance ?? 0)
  const balance = formatCurrency(Math.abs(rawBalance))
  const name = account.account_name ?? account.account_label ?? 'Asset'
  const code = account.code ? `${account.code} — ` : ''

  return {
    id: account.id,
    name: `${code}${name} — ${balance}`,
  }
}

export function mapLiabilitiesAccountOption(account, formatCurrency) {
  const rawBalance = Number(account.balance ?? account.current_balance ?? 0)
  const balance = formatCurrency(Math.abs(rawBalance))
  const name = account.account_name ?? account.account_label ?? 'Liabilities'
  const code = account.code ? `${account.code} — ` : ''

  return {
    id: account.id,
    name: `${code}${name} — ${balance}`,
  }
}
