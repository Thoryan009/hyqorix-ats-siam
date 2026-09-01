export const CREATE_PARTY_ACCOUNT_CHECKED = 1
export const CREATE_PARTY_ACCOUNT_UNCHECKED = 0

export function buildEmptyFormData(defaultFormData, options = {}) {
  const {
    excludeKeys = ['status'],
    createPartyAccountDefault = CREATE_PARTY_ACCOUNT_CHECKED,
  } = options

  return Object.fromEntries(
    Object.keys(defaultFormData)
      .filter((key) => !excludeKeys.includes(key))
      .map((key) => {
        if (key === 'create_party_account') {
          return [key, createPartyAccountDefault]
        }

        return [key, ' ']
      }),
  )
}

export function resetCreatePartyAccount(formData, checked = true) {
  if (!formData || !('create_party_account' in formData)) {
    return
  }

  formData.create_party_account = checked
    ? CREATE_PARTY_ACCOUNT_CHECKED
    : CREATE_PARTY_ACCOUNT_UNCHECKED
}
