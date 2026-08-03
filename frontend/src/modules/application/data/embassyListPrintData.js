export const EMBASSY_LIST_PRINT_META = {
  rlNumber: '1160',
  officeName: 'MERCHANT OVERSEAS',
  submitDate: '01 Jun, 2026',
}

export const EMBASSY_LIST_PRINT_SECTIONS = [
  { key: 'restamping', labelEn: 'Re-stamping', labelAr: 'التجديد' },
  { key: 'new_stamping', labelEn: 'New', labelAr: 'جديد' },
  { key: 'cancellation', labelEn: 'Cancellation', labelAr: 'الغاء' },
]

export const EMBASSY_LIST_PRINT_APPLICANTS = [
  {
    listType: 'new_stamping',
    sl: 1,
    passportNo: 'A14977329',
    visaNo: '1306052542',
    name: 'SADDEK CHOWDHURY',
    sponsorName: 'شركة التغليف المتكاملة للصناعة شركة شخص واحد',
    year: '1447',
    profession: 'سائق سيارة',
    agentName: 'N/A',
  },
  {
    listType: 'new_stamping',
    sl: 2,
    passportNo: 'A15296488',
    visaNo: '1305891877',
    name: 'MD ANTOR MIA',
    sponsorName: 'شركة ادارة مرافق للأستثمار',
    year: '1447',
    profession: 'عامل نظافة حدائق',
    agentName: 'N/A',
  },
  {
    listType: 'new_stamping',
    sl: 3,
    passportNo: 'A03078973',
    visaNo: '1305553135',
    name: 'MD SAIFUL ISLAM',
    sponsorName: 'شركة متاجر السيف للتنمية والاستثمار',
    year: '1447',
    profession: 'عامل تحميل وتنزيل',
    agentName: 'N/A',
  },
  {
    listType: 'new_stamping',
    sl: 4,
    passportNo: 'EL0595634',
    visaNo: '1304647243',
    name: 'MOHAMMAD ROBEL',
    sponsorName: 'مصنع شركة المخابز الغربية',
    year: '1446',
    profession: 'عامل تنظيف مكاتب ومنشآت',
    agentName: 'N/A',
  },
  {
    listType: 'new_stamping',
    sl: 5,
    passportNo: 'A11076588',
    visaNo: '1305402913',
    name: 'ABDUL AZIZ',
    sponsorName: 'شركة مصنع شركة المخابز الغربية',
    year: '1447',
    profession: 'عامل تعبئة وتغليف',
    agentName: 'N/A',
  },
  {
    listType: 'new_stamping',
    sl: 6,
    passportNo: 'A12124993',
    visaNo: '1305402913',
    name: 'MD MONOWAR HOSSAIN',
    sponsorName: 'شركة مصنع شركة المخابز الغربية',
    year: '1447',
    profession: 'عامل تعبئة وتغليف',
    agentName: 'N/A',
  },
]

export function getApplicantsBySection(listType) {
  return EMBASSY_LIST_PRINT_APPLICANTS.filter((item) => item.listType === listType)
}

export function buildOfficialTableRows() {
  const rows = []

  EMBASSY_LIST_PRINT_SECTIONS.forEach((section) => {
    const applicants = getApplicantsBySection(section.key)

    rows.push({
      key: `official-section-${section.key}`,
      type: 'section',
      labelEn: section.labelEn,
      labelAr: section.labelAr,
    })

    applicants.forEach((item) => {
      rows.push({
        key: `official-item-${section.key}-${item.sl}`,
        type: 'item',
        item,
      })
    })

    rows.push({
      key: `official-group-${section.key}`,
      type: 'group',
      count: applicants.length,
    })
  })

  return rows
}

export function buildEmbassyTableRows() {
  const rows = []

  EMBASSY_LIST_PRINT_SECTIONS.forEach((section) => {
    const applicants = getApplicantsBySection(section.key)

    rows.push({
      key: `embassy-section-${section.key}`,
      type: 'section',
      labelEn: section.labelEn,
      labelAr: section.labelAr,
    })

    applicants.forEach((item) => {
      rows.push({
        key: `embassy-item-${section.key}-${item.sl}`,
        type: 'item',
        item,
      })
    })

    rows.push({
      key: `embassy-group-${section.key}`,
      type: 'group',
      count: applicants.length,
    })
  })

  return rows
}
