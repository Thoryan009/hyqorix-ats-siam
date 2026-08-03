export const vendorAccounts = [
  {
    id: 1,
    vendor_id: 1,
    vendor_code: 'VND001',
    vendor_name: 'Skyline Travel Services',
    phone: '+880 1711002200',
    balance: 84500,
    opening_balance: 70000,
    status: 'Active',
  },
  {
    id: 2,
    vendor_id: 2,
    vendor_code: 'VND002',
    vendor_name: 'Gulf Medical Supplies',
    phone: '+880 1822003300',
    balance: -12500,
    opening_balance: 0,
    status: 'Active',
  },
  {
    id: 3,
    vendor_id: 3,
    vendor_code: 'VND003',
    vendor_name: 'Orient Visa Support',
    phone: '+880 1933004400',
    balance: 45200,
    opening_balance: 40000,
    status: 'Active',
  },
]

export function normalizeVendorAccount(account) {
  return {
    ...account,
    vendor_id: account.vendor_id ?? null,
    status: account.status ?? 'Active',
  }
}

export function loadVendorAccounts(saved) {
  if (!Array.isArray(saved) || saved.length === 0) {
    return structuredClone(vendorAccounts)
  }

  return saved.map((account) => normalizeVendorAccount(account))
}

/** @deprecated Use loadVendorAccounts instead */
export function hydrateVendorAccounts(saved) {
  return loadVendorAccounts(saved)
}
