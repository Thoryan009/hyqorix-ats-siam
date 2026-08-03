import { defineStore } from 'pinia'
import { ref } from 'vue'
import { getExpenseCostConfig, getCostTypeByCategory } from '../config/expenseCostAccountConfigs'
import { getCostTypeFromAccountCategory } from '../data/expenseHeadAccountLinkData'
import { useExpenseCategoryStore } from './expenseCategoryStore'
import { useExpenseHeadStore } from './expenseHeadStore'
import { useFinanceAccountStore } from './financeAccountStore'

export const useExpenseCostAccountsStore = defineStore('expenseCostAccounts', () => {
  const financeAccountStore = useFinanceAccountStore()

  const activeCostType = ref(null)
  const isCreateModalOpen = ref(false)
  const isEditModalOpen = ref(false)
  const editingAccount = ref(null)

  function normalizeCostType(costTypeOrCategory) {
    return getCostTypeFromAccountCategory(costTypeOrCategory) ?? costTypeOrCategory
  }

  function getAccountCategory(costType) {
    return getExpenseCostConfig(normalizeCostType(costType))?.accountCategory ?? null
  }

  function getAccounts(costType) {
    const category = getAccountCategory(costType)
    return category ? financeAccountStore.getAccountsByCategory(category) : []
  }

  function getAccount(costType, accountId) {
    const category = getAccountCategory(costType)
    return category
      ? financeAccountStore.getAccountByCategory(category, accountId)
      : null
  }

  function hasAccountForHead(costType, headId) {
    return getAccounts(costType).some(
      (account) => Number(account.head_id) === Number(headId)
    )
  }

  function getAvailableHeads(costType) {
    const config = getExpenseCostConfig(costType)
    if (!config) return []

    const headStore = useExpenseHeadStore()
    const categoryStore = useExpenseCategoryStore()
    const category = categoryStore.findCategoryByCode(config.categoryCode)

    if (!category) return []

    return headStore.heads.filter(
      (head) =>
        Number(head.category_id) === Number(category.id) &&
        String(head.status || '').toLowerCase() === 'active' &&
        !hasAccountForHead(costType, head.id)
    )
  }

  function getAccountByHeadId(categoryId, headId) {
    const categoryStore = useExpenseCategoryStore()
    const headStore = useExpenseHeadStore()
    const category = categoryStore.categories.find((item) => Number(item.id) === Number(categoryId))
    const costType = getCostTypeByCategory(category)
    if (!costType) return null

    const head = headStore.heads.find((item) => Number(item.id) === Number(headId))
    const normalizedHeadName = head?.name?.trim().toLowerCase() ?? ''

    return (
      getAccounts(costType).find((account) => {
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

  function getCostTypeByCategoryId(categoryId) {
    const categoryStore = useExpenseCategoryStore()
    const category = categoryStore.categories.find(
      (item) => Number(item.id) === Number(categoryId)
    )
    return getCostTypeByCategory(category)
  }

  function resolveAccountMeta(categoryId, headId) {
    const categoryStore = useExpenseCategoryStore()
    const category = categoryStore.categories.find((item) => Number(item.id) === Number(categoryId))
    const costType = getCostTypeByCategory(category)
    const account = getAccountByHeadId(categoryId, headId)

    if (!costType || !account) return null

    return {
      linked_account_category: costType,
      linked_account_id: account.id,
      linked_account_name: account.head_name,
      linked_account_type: '',
      expense_cost_type: costType,
      expense_cost_account_id: account.id,
      expense_cost_account_name: account.head_name,
      expense_cost_category_name: account.category_name,
    }
  }

  async function ensureAccountsForCategoryId(categoryId) {
    const costType = getCostTypeByCategoryId(categoryId)
    if (!costType) return
    await fetchAccounts(costType)
  }

  async function resolveAccountMetaAsync(categoryId, headId) {
    await ensureAccountsForCategoryId(categoryId)
    return resolveAccountMeta(categoryId, headId)
  }

  async function fetchAccounts(costType, force = false) {
    const category = getAccountCategory(costType)
    if (!category) return
    await financeAccountStore.fetchAccounts(category, force)
  }

  function openCreateModal(costType) {
    activeCostType.value = costType
    isCreateModalOpen.value = true
  }

  function closeCreateModal() {
    isCreateModalOpen.value = false
    activeCostType.value = null
  }

  function openEditModal(costType, account) {
    activeCostType.value = costType
    editingAccount.value = { ...account }
    isEditModalOpen.value = true
  }

  function closeEditModal() {
    isEditModalOpen.value = false
    editingAccount.value = null
    activeCostType.value = null
  }

  async function createAccount(costType, headId) {
    const config = getExpenseCostConfig(costType)
    const headStore = useExpenseHeadStore()
    const categoryStore = useExpenseCategoryStore()
    const head = headStore.heads.find((item) => item.id === Number(headId))
    const category = categoryStore.findCategoryByCode(config?.categoryCode)

    if (!config || !head) {
      return { ok: false, message: 'Selected expense head was not found.' }
    }

    if (Number(head.category_id) !== Number(category?.id)) {
      return {
        ok: false,
        message: 'Selected expense head does not belong to this expense category.',
      }
    }

    if (String(head.status || '').toLowerCase() !== 'active') {
      return { ok: false, message: 'Selected expense head is not active.' }
    }

    if (hasAccountForHead(costType, headId)) {
      return { ok: false, message: 'An account already exists for this expense head.' }
    }

    const result = await financeAccountStore.createAccount({
      category: config.accountCategory,
      account_name: head.name,
      expense_head_id: head.id,
      expense_category_id: category?.id ?? head.category_id,
      base_price: Number(head.base_price) || 0,
      balance: 0,
      opening_balance: 0,
      status: 'Active',
    })

    if (result.ok) {
      await fetchAccounts(costType, true)
    }

    return result
  }

  async function updateAccountStatus(costType, accountId, status) {
    const config = getExpenseCostConfig(costType)
    const account = getAccount(costType, accountId)

    if (!account) {
      return { ok: false, message: `${config?.costTypeLabel ?? 'Expense'} account was not found.` }
    }

    if (!['Active', 'Inactive'].includes(status)) {
      return { ok: false, message: 'Please select a valid status.' }
    }

    return financeAccountStore.updateAccount({
      id: account.id,
      category: config.accountCategory,
      account_name: account.account_name ?? account.head_name,
      expense_head_id: account.head_id,
      expense_category_id: account.category_id,
      base_price: account.base_price,
      balance: account.balance,
      opening_balance: account.opening_balance,
      status,
    })
  }

  async function deleteAccount(costType, accountId) {
    const category = getAccountCategory(costType)
    if (!category) {
      return { ok: false, message: 'Invalid expense account category.' }
    }

    return financeAccountStore.deleteAccount(accountId, category)
  }

  return {
    activeCostType,
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
    getCostTypeByCategoryId,
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
