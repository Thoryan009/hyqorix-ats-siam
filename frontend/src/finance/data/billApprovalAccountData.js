export const billAccountCategoryOptions = [
  { id: 'main', name: 'Main Account' },
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

export function getBillAccountCategoryLabel(category) {
  return billAccountCategoryOptions.find((option) => option.id === category)?.name ?? category
}
