import { defineStore } from 'pinia'
import { ref } from 'vue'
import {
  getIncomeAccountConfig,
  getIncomeTypeByCategory,
} from '../config/incomeAccountConfigs'
import { getIncomeTypeFromAccountCategory } from '../data/incomeHeadAccountLinkData'
import { useIncomeCategoryStore } from './incomeCategoryStore'
import { useIncomeHeadStore } from './incomeHeadStore'
import { useFinanceAccountStore } from './financeAccountStore'

export const useIncomeAccountsStore = defineStore('incomeAccounts', () => {
  const financeAccountStore = useFinanceAccountStore()

  const activeIncomeType = ref(null)
  const isCreateModalOpen = ref(false)
  const isEditModalOpen = ref(false)
  const editingAccount = ref(null)

  function normalizeIncomeType(incomeTypeOrCategory) {
    return getIncomeTypeFromAccountCategory(incomeTypeOrCategory) ?? incomeTypeOrCategory
  }

  function getAccountCategory(incomeType) {
    return getIncomeAccountConfig(normalizeIncomeType(incomeType))?.accountCategory ?? null
  }

  function getAccounts(incomeType) {
    const category = getAccountCategory(incomeType)
    return category ? financeAccountStore.getAccountsByCategory(category) : []
  }

  function getAccount(incomeType, accountId) {
    const category = getAccountCategory(incomeType)
    return category
      ? financeAccountStore.getAccountByCategory(category, accountId)
      : null
  }

  function hasAccountForHead(incomeType, headId) {
    return getAccounts(incomeType).some(
      (account) => Number(account.head_id) === Number(headId)
    )
  }

  function getAvailableHeads(incomeType) {
    const config = getIncomeAccountConfig(incomeType)
    if (!config) return []

    const headStore = useIncomeHeadStore()
    const categoryStore = useIncomeCategoryStore()
    const category = categoryStore.findCategoryByCode(config.categoryCode)

    if (!category) return []

    return headStore.heads.filter(
      (head) =>
        Number(head.category_id) === Number(category.id) &&
        String(head.status || '').toLowerCase() === 'active' &&
        !hasAccountForHead(incomeType, head.id)
    )
  }

  function getAccountByHeadId(categoryId, headId) {
    const categoryStore = useIncomeCategoryStore()
    const headStore = useIncomeHeadStore()
    const category = categoryStore.categories.find((item) => Number(item.id) === Number(categoryId))
    const incomeType = getIncomeTypeByCategory(category)
    if (!incomeType) return null

    const head = headStore.heads.find((item) => Number(item.id) === Number(headId))
    const normalizedHeadName = head?.name?.trim().toLowerCase() ?? ''

    return (
      getAccounts(incomeType).find((account) => {
        if (account.status !== 'Active') return false
        if (Number(account.head_id) === Number(headId)) return true
        if (!normalizedHeadName) return false

        const accountName = String(account.head_name || account.account_name || '')
          .trim()
          .toLowerCase()

        return accountName === normalizedHeadName
      }) ?? null
    )
  }

  function getIncomeTypeByCategoryId(categoryId) {
    const categoryStore = useIncomeCategoryStore()
    const category = categoryStore.categories.find(
      (item) => Number(item.id) === Number(categoryId)
    )
    return getIncomeTypeByCategory(category)
  }

  function resolveAccountMeta(categoryId, headId) {
    const categoryStore = useIncomeCategoryStore()
    const category = categoryStore.categories.find((item) => Number(item.id) === Number(categoryId))
    const incomeType = getIncomeTypeByCategory(category)
    const account = getAccountByHeadId(categoryId, headId)

    if (!incomeType || !account) return null

    return {
      linked_account_category: incomeType,
      linked_account_id: account.id,
      linked_account_name: account.head_name,
      linked_account_type: 'Income',
      income_type: incomeType,
      income_account_id: account.id,
      income_account_name: account.head_name,
      income_category_name: account.category_name,
    }
  }

  async function ensureAccountsForCategoryId(categoryId) {
    const incomeType = getIncomeTypeByCategoryId(categoryId)
    if (!incomeType) return
    await fetchAccounts(incomeType)
  }

  async function resolveAccountMetaAsync(categoryId, headId) {
    await ensureAccountsForCategoryId(categoryId)
    return resolveAccountMeta(categoryId, headId)
  }

  async function fetchAccounts(incomeType, force = false) {
    const category = getAccountCategory(incomeType)
    if (!category) return
    await financeAccountStore.fetchAccounts(category, force)
  }

  function openCreateModal(incomeType) {
    activeIncomeType.value = incomeType
    isCreateModalOpen.value = true
  }

  function closeCreateModal() {
    isCreateModalOpen.value = false
    activeIncomeType.value = null
  }

  function openEditModal(incomeType, account) {
    activeIncomeType.value = incomeType
    editingAccount.value = { ...account }
    isEditModalOpen.value = true
  }

  function closeEditModal() {
    isEditModalOpen.value = false
    editingAccount.value = null
    activeIncomeType.value = null
  }

  async function createAccount(incomeType, headId) {
    const config = getIncomeAccountConfig(incomeType)
    const headStore = useIncomeHeadStore()
    const categoryStore = useIncomeCategoryStore()
    const head = headStore.heads.find((item) => item.id === Number(headId))
    const category = categoryStore.findCategoryByCode(config?.categoryCode)

    if (!config || !head) {
      return { ok: false, message: 'Selected income head was not found.' }
    }

    if (Number(head.category_id) !== Number(category?.id)) {
      return {
        ok: false,
        message: 'Selected income head does not belong to this income category.',
      }
    }

    if (String(head.status || '').toLowerCase() !== 'active') {
      return { ok: false, message: 'Selected income head is not active.' }
    }

    if (hasAccountForHead(incomeType, headId)) {
      return { ok: false, message: 'An account already exists for this income head.' }
    }

    const result = await financeAccountStore.createAccount({
      category: config.accountCategory,
      account_name: head.name,
      income_head_id: head.id,
      income_category_id: category?.id ?? head.category_id,
      base_price: Number(head.base_price) || 0,
      balance: 0,
      opening_balance: 0,
      status: 'Active',
    })

    if (result.ok) {
      await fetchAccounts(incomeType, true)
    }

    return result
  }

  async function updateAccountStatus(incomeType, accountId, status) {
    const config = getIncomeAccountConfig(incomeType)
    const account = getAccount(incomeType, accountId)

    if (!account) {
      return { ok: false, message: `${config?.incomeTypeLabel ?? 'Income'} account was not found.` }
    }

    if (!['Active', 'Inactive'].includes(status)) {
      return { ok: false, message: 'Please select a valid status.' }
    }

    return financeAccountStore.updateAccount({
      id: account.id,
      category: config.accountCategory,
      account_name: account.account_name ?? account.head_name,
      income_head_id: account.head_id,
      income_category_id: account.category_id,
      base_price: account.base_price,
      balance: account.balance,
      opening_balance: account.opening_balance,
      status,
    })
  }

  async function deleteAccount(incomeType, accountId) {
    const category = getAccountCategory(incomeType)
    if (!category) {
      return { ok: false, message: 'Invalid income account category.' }
    }

    return financeAccountStore.deleteAccount(accountId, category)
  }

  return {
    activeIncomeType,
    isCreateModalOpen,
    isEditModalOpen,
    editingAccount,
    getAccounts,
    getAccount,
    getAvailableHeads,
    hasAccountForHead,
    getAccountByHeadId,
    resolveAccountMeta,
    resolveAccountMetaAsync,
    ensureAccountsForCategoryId,
    getIncomeTypeByCategoryId,
    fetchAccounts,
    openCreateModal,
    closeCreateModal,
    openEditModal,
    closeEditModal,
    createAccount,
    updateAccountStatus,
    deleteAccount,
  }
})
