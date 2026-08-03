export const staffMasterList = [
  {
    id: 1,
    staff_code: 'STF001',
    staff_name: 'Md. Hasan Rahman',
    phone: '+880 1711111111',
  },
  {
    id: 2,
    staff_code: 'STF002',
    staff_name: 'Nusrat Jahan',
    phone: '+880 1811111111',
  },
  {
    id: 3,
    staff_code: 'STF003',
    staff_name: 'Tanvir Ahmed',
    phone: '+880 1911111111',
  },
  {
    id: 4,
    staff_code: 'STF004',
    staff_name: 'Shamima Sultana',
    phone: '+880 1611111111',
  },
  {
    id: 5,
    staff_code: 'STF005',
    staff_name: 'Rafiul Islam',
    phone: '+880 1511111111',
  },
]

export function loadStaffMasterList(saved) {
  if (!Array.isArray(saved) || saved.length === 0) {
    return structuredClone(staffMasterList)
  }

  return staffMasterList.map((base) => {
    const existing = saved.find((item) => item.id === base.id)
    if (!existing) return structuredClone(base)

    return {
      ...base,
      ...existing,
      staff_code: base.staff_code,
      staff_name: base.staff_name,
      phone: base.phone,
    }
  })
}
