import { computed, reactive, ref } from 'vue'

let nextEntryId = 1

function createSingleEntry(overrides = {}) {
  return {
    id: nextEntryId++,
    application: null,
    expected_return_date: '',
    taken_reason: '',
    return_date: '',
    ...overrides,
  }
}

function createSharedFields() {
  return {
    type: 'single',
    is_permanent: false,
    taker_name: '',
    taker_phone: '',
    taken_at: new Date().toISOString().slice(0, 10),
    expected_return_date: '',
  }
}

function createGroupFields() {
  return {
    taken_reason: '',
    return_date: '',
    applications: [],
  }
}

export function usePassportHandoverForm() {
  const sharedFields = reactive(createSharedFields())
  const groupFields = reactive(createGroupFields())
  const singleEntries = ref([createSingleEntry()])

  const isSingleType = computed(() => sharedFields.type === 'single')
  const isGroupType = computed(() => sharedFields.type === 'group')
  const isPermanent = computed(() => sharedFields.is_permanent)

  const excludedApplicationIds = computed(() => {
    if (isGroupType.value) {
      return groupFields.applications.map((item) => item.id)
    }

    return singleEntries.value
      .map((entry) => entry.application?.id)
      .filter(Boolean)
  })

  function setType(type) {
    sharedFields.type = type

    if (type === 'single') {
      groupFields.taken_reason = ''
      groupFields.return_date = ''
      groupFields.applications = []
      if (!singleEntries.value.length) {
        singleEntries.value = [createSingleEntry()]
      }
      return
    }

    singleEntries.value = [createSingleEntry()]
  }

  function setPermanent(isPermanent) {
    sharedFields.is_permanent = isPermanent

    if (!isPermanent) {
      return
    }

    sharedFields.expected_return_date = ''
    groupFields.return_date = ''

    singleEntries.value.forEach((entry) => {
      entry.expected_return_date = ''
      entry.return_date = ''
    })
  }

  function addSingleEntry() {
    const firstEntry = singleEntries.value[0]
    singleEntries.value.push(
      createSingleEntry({
        expected_return_date: sharedFields.is_permanent
          ? ''
          : firstEntry?.expected_return_date || '',
      })
    )
  }

  function removeSingleEntry(entryId) {
    if (singleEntries.value.length === 1) return
    singleEntries.value = singleEntries.value.filter((entry) => entry.id !== entryId)
  }

  function setSingleEntryApplication(entryId, application) {
    const entry = singleEntries.value.find((item) => item.id === entryId)
    if (!entry) return
    entry.application = application
  }

  function removeSingleEntryApplication(entryId) {
    const entry = singleEntries.value.find((item) => item.id === entryId)
    if (!entry) return
    entry.application = null
  }

  function addGroupApplication(application) {
    if (groupFields.applications.some((item) => item.id === application.id)) return
    groupFields.applications.push(application)
  }

  function removeGroupApplication(applicationId) {
    groupFields.applications = groupFields.applications.filter((item) => item.id !== applicationId)
  }

  function resetForm() {
    Object.assign(sharedFields, createSharedFields())
    Object.assign(groupFields, createGroupFields())
    singleEntries.value = [createSingleEntry()]
  }

  function buildPayload() {
    const base = {
      type: sharedFields.type,
      is_permanent: sharedFields.is_permanent,
      taker_name: sharedFields.taker_name.trim(),
      taker_phone: sharedFields.taker_phone.trim(),
      taken_at: sharedFields.taken_at,
    }

    if (isSingleType.value) {
      return {
        ...base,
        entries: singleEntries.value.map((entry) => ({
          application_id: entry.application?.id ?? null,
          passport_no: entry.application?.passportNo ?? '',
          expected_return_date: sharedFields.is_permanent ? null : entry.expected_return_date,
          taken_reason: entry.taken_reason.trim(),
          return_date: sharedFields.is_permanent ? null : entry.return_date || null,
        })),
      }
    }

    return {
      ...base,
      expected_return_date: sharedFields.is_permanent ? null : sharedFields.expected_return_date,
      taken_reason: groupFields.taken_reason.trim(),
      return_date: sharedFields.is_permanent ? null : groupFields.return_date || null,
      applications: groupFields.applications.map((item) => ({
        application_id: item.id,
        passport_no: item.passportNo,
      })),
    }
  }

  function validateForm() {
    if (!sharedFields.taker_name.trim()) {
      return { ok: false, message: 'Taker name is required.' }
    }

    if (!sharedFields.taker_phone.trim()) {
      return { ok: false, message: 'Taker phone is required.' }
    }

    if (!sharedFields.taken_at) {
      return { ok: false, message: 'Taken at date is required.' }
    }

    if (isSingleType.value) {
      for (const [index, entry] of singleEntries.value.entries()) {
        if (!entry.application) {
          return { ok: false, message: `Please select an application for entry ${index + 1}.` }
        }

        if (!sharedFields.is_permanent && !entry.expected_return_date) {
          return {
            ok: false,
            message: `Expected return date is required for entry ${index + 1}.`,
          }
        }

        if (!entry.taken_reason.trim()) {
          return { ok: false, message: `Taken reason is required for entry ${index + 1}.` }
        }
      }

      return { ok: true }
    }

    if (!sharedFields.is_permanent && !sharedFields.expected_return_date) {
      return { ok: false, message: 'Expected return date is required.' }
    }

    if (!groupFields.taken_reason.trim()) {
      return { ok: false, message: 'Taken reason is required.' }
    }

    if (!groupFields.applications.length) {
      return { ok: false, message: 'Please select at least one application.' }
    }

    return { ok: true }
  }

  return {
    sharedFields,
    groupFields,
    singleEntries,
    isSingleType,
    isGroupType,
    isPermanent,
    excludedApplicationIds,
    setType,
    setPermanent,
    addSingleEntry,
    removeSingleEntry,
    setSingleEntryApplication,
    removeSingleEntryApplication,
    addGroupApplication,
    removeGroupApplication,
    resetForm,
    buildPayload,
    validateForm,
  }
}
