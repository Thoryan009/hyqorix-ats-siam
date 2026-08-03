export const vendorMasterList = [
  {
    id: 1,
    vendor_code: 'VND001',
    vendor_name: 'Skyline Travel Services',
    phone: '+880 1711002200',
  },
  {
    id: 2,
    vendor_code: 'VND002',
    vendor_name: 'Gulf Medical Supplies',
    phone: '+880 1822003300',
  },
  {
    id: 3,
    vendor_code: 'VND003',
    vendor_name: 'Orient Visa Support',
    phone: '+880 1933004400',
  },
  {
    id: 4,
    vendor_code: 'VND004',
    vendor_name: 'Pacific Air Logistics',
    phone: '+880 1711005500',
  },
  {
    id: 5,
    vendor_code: 'VND005',
    vendor_name: 'Global Ticket House',
    phone: '+880 1822006600',
  },
]

export function loadVendorMasterList(saved) {
  if (!Array.isArray(saved) || saved.length === 0) {
    return structuredClone(vendorMasterList)
  }

  return vendorMasterList.map((base) => {
    const existing = saved.find((item) => item.id === base.id)
    if (!existing) return structuredClone(base)

    return {
      ...base,
      ...existing,
      vendor_code: base.vendor_code,
      vendor_name: base.vendor_name,
      phone: base.phone,
    }
  })
}
