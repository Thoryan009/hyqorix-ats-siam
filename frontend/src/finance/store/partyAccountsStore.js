import { defineStore } from 'pinia'
import { ref } from 'vue'
import { getPartyConfig } from '../config/partyAccountConfigs'
import { usePartyMasterStore } from './partyMasterStore'
import { useFinanceAccountStore } from './financeAccountStore'

export const usePartyAccountsStore = defineStore('partyAccounts', () => {
  const financeAccountStore = useFinanceAccountStore()

  const activePartyType = ref(null)
  const isCreateModalOpen = ref(false)
  const isEditModalOpen = ref(false)
  const editingAccount = ref(null)

  function getAccountCategory(partyType) {
    return getPartyConfig(partyType)?.accountCategory ?? null
  }

  function getAccounts(partyType) {
    const category = getAccountCategory(partyType)
    return category ? financeAccountStore.getAccountsByCategory(category) : []
  }

  function getAccount(partyType, accountId) {
    const category = getAccountCategory(partyType)
    return category
      ? financeAccountStore.getAccountByCategory(category, accountId)
      : null
  }

  function getAccountByEntityId(partyType, entityId) {
    if (!entityId) return null
    return (
      getAccounts(partyType).find(
        (account) => Number(account.entity_id) === Number(entityId)
      ) ?? null
    )
  }

  function hasAccountForParty(partyType, partyId) {
    const config = getPartyConfig(partyType)
    const masterParty = usePartyMasterStore().getParty(partyType, partyId)
    if (!config || !masterParty) return false

    return getAccounts(partyType).some(
      (account) =>
        Number(account.entity_id) === Number(partyId) ||
        account[config.codeKey] === masterParty[config.codeKey]
    )
  }

  function getAvailableParties(partyType) {
    const masterStore = usePartyMasterStore()
    return masterStore.getParties(partyType).filter((party) => !hasAccountForParty(partyType, party.id))
  }

  async function fetchAccounts(partyType, force = false) {
    const category = getAccountCategory(partyType)
    if (!category) return
    await financeAccountStore.fetchAccounts(category, force)
  }

  /**
   * Create Active finance party accounts for master IDs that do not have one yet.
   * Used so Client Income / Sale Entry can list payment-responsibility clients
   * without requiring a manual "Create Client Account" step first.
   */
  async function ensureAccountsForMasterIds(partyType, masterIds = []) {
    const ids = [
      ...new Set(
        (Array.isArray(masterIds) ? masterIds : [])
          .map((id) => Number(id))
          .filter((id) => id > 0)
      ),
    ]
    if (!ids.length) return { ok: true, created: 0 }

    const masterStore = usePartyMasterStore()
    await Promise.all([fetchAccounts(partyType), masterStore.fetchParties(partyType)])

    let created = 0
    for (const masterId of ids) {
      if (hasAccountForParty(partyType, masterId)) continue
      if (!masterStore.getParty(partyType, masterId)) continue

      const result = await createAccount(partyType, masterId)
      if (result.ok) created += 1
    }

    if (created > 0) {
      await fetchAccounts(partyType, true)
    }

    return { ok: true, created }
  }

  function openCreateModal(partyType) {
    activePartyType.value = partyType
    isCreateModalOpen.value = true
  }

  function closeCreateModal() {
    isCreateModalOpen.value = false
    activePartyType.value = null
  }

  function openEditModal(partyType, account) {
    activePartyType.value = partyType
    editingAccount.value = { ...account }
    isEditModalOpen.value = true
  }

  function closeEditModal() {
    isEditModalOpen.value = false
    editingAccount.value = null
    activePartyType.value = null
  }

  async function createAccount(partyType, partyId) {
    const config = getPartyConfig(partyType)
    const masterParty = usePartyMasterStore().getParty(partyType, partyId)

    if (!config || !masterParty) {
      return { ok: false, message: `Selected ${config?.partyLabel?.toLowerCase() ?? 'party'} was not found.` }
    }

    if (hasAccountForParty(partyType, partyId)) {
      return { ok: false, message: `An account already exists for this ${config.partyLabel.toLowerCase()}.` }
    }

    return financeAccountStore.createAccount({
      category: config.accountCategory,
      account_name: masterParty[config.nameKey],
      code: masterParty[config.codeKey],
      phone: masterParty.phone ?? '',
      entity_id: masterParty.id,
      balance: 0,
      opening_balance: 0,
      status: 'Active',
    })
  }

  async function updateAccountStatus(partyType, accountId, status) {
    const config = getPartyConfig(partyType)
    const account = getAccount(partyType, accountId)

    if (!account) {
      return { ok: false, message: `${config?.partyLabel ?? 'Party'} account was not found.` }
    }

    if (!['Active', 'Inactive'].includes(status)) {
      return { ok: false, message: 'Please select a valid status.' }
    }

    return financeAccountStore.updateAccount({
      id: account.id,
      category: config.accountCategory,
      account_name: account[config.nameKey] ?? account.account_name,
      code: account[config.codeKey] ?? account.code,
      phone: account.phone,
      entity_id: account.entity_id ?? account[config.idKey],
      balance: account.balance,
      opening_balance: account.opening_balance,
      status,
    })
  }

  async function deleteAccount(partyType, accountId) {
    const category = getAccountCategory(partyType)
    if (!category) {
      return { ok: false, message: 'Invalid party account category.' }
    }

    return financeAccountStore.deleteAccount(accountId, category)
  }

  return {
    activePartyType,
    isCreateModalOpen,
    isEditModalOpen,
    editingAccount,
    getAccounts,
    getAccount,
    getAccountByEntityId,
    getAvailableParties,
    hasAccountForParty,
    fetchAccounts,
    ensureAccountsForMasterIds,
    openCreateModal,
    closeCreateModal,
    openEditModal,
    closeEditModal,
    createAccount,
    updateAccountStatus,
    deleteAccount,
  }
})
