import { computed, reactive, ref, unref, watch } from 'vue'
import { toast } from '@/shared/config/toastConfig'
import { useEmbassyHintsQuery } from '../queries/useEmbassyQuery'

const TAB_LABELS = {
  restamping: 'Re-stamping',
  new_stamping: 'New Stamping',
  cancellation: 'Cancellation',
}

const LIST_TYPES = ['restamping', 'new_stamping', 'cancellation']

const normalizePassport = (passport) => passport.trim().toLowerCase()

export function useEmbassyListForm(options = {}) {
  const submitDate = ref(options.initialSubmitDate ?? new Date().toISOString().slice(0, 10))
  const activeTab = ref('new_stamping')
  const showHints = ref(false)
  const hintsSearchQuery = ref('')

  const excludeEmbassyListId = computed(() => unref(options.excludeEmbassyListId) ?? null)

  const { data: embassyHintsData } = useEmbassyHintsQuery(hintsSearchQuery, {
    submitDateRef: submitDate,
    excludeEmbassyListIdRef: excludeEmbassyListId,
  })

  const tabPassportInputs = reactive({
    restamping: '',
    new_stamping: '',
    cancellation: '',
  })

  const tabApplicants = reactive({
    restamping: [],
    new_stamping: [],
    cancellation: [],
  })

  watch(
    () => tabPassportInputs[activeTab.value],
    (newVal) => {
      if (newVal && newVal.length >= 3) {
        hintsSearchQuery.value = newVal
      } else {
        hintsSearchQuery.value = ''
      }
    }
  )

  const allHints = computed(() => {
    const rawHints = embassyHintsData.value?.data?.data ?? []

    return rawHints.map((hint) => ({
      applicationId: hint.id,
      name: hint.given_name || 'N/A',
      surname: hint.sur_name || 'N/A',
      passport: hint.passport_no || 'N/A',
      visaNo: hint.visa_no || 'N/A',
      sponsorId: hint.sponsor_id || 'N/A',
      professionAr: hint.visa_profession_ar || 'N/A',
    }))
  })

  const activeApplicants = computed(() => tabApplicants[activeTab.value])

  const addedPassportsAcrossAllTabs = computed(() => {
    const passports = Object.values(tabApplicants).flatMap((list) =>
      list.map((item) => normalizePassport(item.passport))
    )

    return new Set(passports)
  })

  const findTabWithPassport = (passportNo) => {
    const normalizedPassport = normalizePassport(passportNo)

    return Object.entries(tabApplicants).find(([, list]) =>
      list.some((item) => normalizePassport(item.passport) === normalizedPassport)
    )?.[0]
  }

  const activeHints = computed(() => {
    if (!showHints.value) return []

    return allHints.value
      .filter((hint) => !addedPassportsAcrossAllTabs.value.has(normalizePassport(hint.passport)))
      .map((hint, index) => ({
        ...hint,
        index: index + 1,
      }))
  })

  const hasApplicants = computed(() =>
    LIST_TYPES.some((type) => tabApplicants[type].length > 0)
  )

  const renumberActiveTabApplicants = () => {
    tabApplicants[activeTab.value].forEach((item, index) => {
      item.id = index + 1
    })
  }

  const addPassportToActiveTab = () => {
    const passportNo = tabPassportInputs[activeTab.value].trim()
    if (!passportNo) return

    if (!submitDate.value) {
      toast.error('Please select a submit date.')
      return
    }

    if (addedPassportsAcrossAllTabs.value.has(normalizePassport(passportNo))) {
      const existingTab = findTabWithPassport(passportNo)
      const tabLabel = existingTab ? TAB_LABELS[existingTab] : 'another list'
      toast.error(`This candidate is already added to ${tabLabel}.`)
      return
    }

    const normalizedPassport = normalizePassport(passportNo)
    const selectedHint =
      allHints.value.find((item) => normalizePassport(item.passport) === normalizedPassport) || null
    const nextId = tabApplicants[activeTab.value].length + 1

    tabApplicants[activeTab.value].push({
      id: nextId,
      applicationId: selectedHint?.applicationId ?? null,
      name: selectedHint ? `${selectedHint.name} ${selectedHint.surname}` : `NEW APPLICANT ${nextId}`,
      code: selectedHint?.visaNo || `TEMP${String(nextId).padStart(5, '0')}`,
      passport: selectedHint?.passport || passportNo,
      nid: selectedHint?.sponsorId || `NID${String(nextId).padStart(6, '0')}`,
      professionAr: selectedHint?.professionAr || 'عامل عام',
    })

    tabPassportInputs[activeTab.value] = ''
    showHints.value = false
  }

  const removeApplicantFromActiveTab = (passportNo) => {
    const normalizedPassport = normalizePassport(passportNo)
    const list = tabApplicants[activeTab.value]
    const index = list.findIndex((item) => normalizePassport(item.passport) === normalizedPassport)

    if (index === -1) return

    list.splice(index, 1)
    renumberActiveTabApplicants()
  }

  const selectHint = (item) => {
    tabPassportInputs[activeTab.value] = item.passport
    showHints.value = true
  }

  const handlePassportBlur = () => {
    window.setTimeout(() => {
      showHints.value = false
    }, 150)
  }

  const resetApplicants = () => {
    LIST_TYPES.forEach((type) => {
      tabApplicants[type] = []
      tabPassportInputs[type] = ''
    })
  }

  const loadFromRecord = (record) => {
    if (!record) return

    submitDate.value = record.submit_date_raw || submitDate.value
    resetApplicants()

    const items = record.items?.data ?? record.items ?? []

    items.forEach((item) => {
      if (!LIST_TYPES.includes(item.list_type)) return

      const nextId = tabApplicants[item.list_type].length + 1

      tabApplicants[item.list_type].push({
        id: nextId,
        applicationId: item.application_id ?? null,
        name: `${item.given_name || 'N/A'} ${item.sur_name || 'N/A'}`.trim(),
        code: item.visa_no || 'N/A',
        passport: item.passport_no,
        nid: item.sponsor_id || 'N/A',
        professionAr: item.visa_profession_ar || 'N/A',
      })
    })
  }

  const buildPayload = () => {
    const items = []

    LIST_TYPES.forEach((listType) => {
      tabApplicants[listType].forEach((item, index) => {
        items.push({
          list_type: listType,
          application_id: item.applicationId,
          passport_no: item.passport,
          sort_order: index + 1,
        })
      })
    })

    return {
      submit_date: submitDate.value,
      items,
    }
  }

  return {
    submitDate,
    activeTab,
    showHints,
    tabPassportInputs,
    activeApplicants,
    activeHints,
    hasApplicants,
    addPassportToActiveTab,
    removeApplicantFromActiveTab,
    selectHint,
    handlePassportBlur,
    buildPayload,
    loadFromRecord,
  }
}
