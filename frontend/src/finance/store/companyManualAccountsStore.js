import { defineStore } from 'pinia'
import { ref } from 'vue'
import { getCompanyManualAccountConfig } from '../config/companyManualAccountConfigs'
import { useFinanceAccountStore } from './financeAccountStore'

export const useCompanyManualAccountsStore = defineStore('companyManualAccounts', () => {
  const financeAccountStore = useFinanceAccountStore()

  const activeManualType = ref(null)
  const isCreateModalOpen = ref(false)
  const isEditModalOpen = ref(false)
  const editingAccount = ref(null)

  function getAccountCategory(manualType) {
    return getCompanyManualAccountConfig(manualType)?.accountCategory ?? null
  }

  function getAccounts(manualType) {
    const category = getAccountCategory(manualType)
    return category ? financeAccountStore.getAccountsByCategory(category) : []
  }

  function getAccount(manualType, accountId) {
    const category = getAccountCategory(manualType)
    return category ? financeAccountStore.getAccountByCategory(category, accountId) : null
  }

  async function fetchAccounts(manualType, force = false) {
    const category = getAccountCategory(manualType)
    if (!category) return
    await financeAccountStore.fetchAccounts(category, force)
  }

  function openCreateModal(manualType) {
    activeManualType.value = manualType
    isCreateModalOpen.value = true
  }

  function closeCreateModal() {
    isCreateModalOpen.value = false
    activeManualType.value = null
  }

  function openEditModal(manualType, account) {
    activeManualType.value = manualType
    editingAccount.value = { ...account }
    isEditModalOpen.value = true
  }

  function closeEditModal() {
    isEditModalOpen.value = false
    editingAccount.value = null
    activeManualType.value = null
  }

  async function createAccount(manualType, payload = {}) {
    const config = getCompanyManualAccountConfig(manualType)
    if (!config) {
      return { ok: false, message: 'Invalid account category.' }
    }

    const accountName = String(payload.account_name || '').trim()
    if (!accountName) {
      return { ok: false, message: 'Please enter an account name.' }
    }

    return financeAccountStore.createAccount({
      category: config.accountCategory,
      account_name: accountName,
      balance: 0,
      opening_balance: 0,
      status: payload.status || 'Active',
      ...(manualType === 'asset'
        ? {
            link_to_purchase: Boolean(payload.link_to_purchase),
            is_non_current_asset: Boolean(payload.is_non_current_asset),
          }
        : {}),
    })
  }

  async function updateAccount(manualType, accountId, payload = {}) {
    const config = getCompanyManualAccountConfig(manualType)
    const account = getAccount(manualType, accountId)

    if (!config || !account) {
      return { ok: false, message: `${config?.typeLabel ?? 'Account'} was not found.` }
    }

    const accountName = String(payload.account_name || account.account_name || '').trim()
    if (!accountName) {
      return { ok: false, message: 'Please enter an account name.' }
    }

    if (!['Active', 'Inactive'].includes(payload.status)) {
      return { ok: false, message: 'Please select a valid status.' }
    }

    return financeAccountStore.updateAccount({
      id: account.id,
      category: config.accountCategory,
      account_name: accountName,
      balance: account.balance ?? account.current_balance ?? 0,
      opening_balance: account.opening_balance ?? 0,
      status: payload.status,
      ...(manualType === 'asset'
        ? {
            link_to_purchase: Boolean(payload.link_to_purchase),
            is_non_current_asset: Boolean(payload.is_non_current_asset),
          }
        : {}),
    })
  }

  async function deleteAccount(manualType, accountId) {
    const category = getAccountCategory(manualType)
    if (!category) {
      return { ok: false, message: 'Invalid account category.' }
    }

    return financeAccountStore.deleteAccount(accountId, category)
  }

  return {
    activeManualType,
    isCreateModalOpen,
    isEditModalOpen,
    editingAccount,
    getAccounts,
    getAccount,
    fetchAccounts,
    openCreateModal,
    closeCreateModal,
    openEditModal,
    closeEditModal,
    createAccount,
    updateAccount,
    deleteAccount,
  }
})
