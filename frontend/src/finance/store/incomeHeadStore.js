import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'
import {
  fetchAll,
  submitData,
  updateData,
} from '../services/incomeHeadService'

export const useIncomeHeadStore = defineStore('incomeHead', () => {
  const { item, isModal, isViewModal, isEditModal, handleToggleModal, handleReset } =
    useModalHelpers()

  const moduleName = 'Income Head'
  const heads = ref([])
  const isLoading = ref(false)
  const isLoaded = ref(false)

  const totalHeads = computed(() => heads.value.length)

  function getApiErrorMessage(error, fallback = 'Request failed.') {
    return (
      Object.values(error?.errors || {})?.flat()?.[0] ||
      error?.message ||
      fallback
    )
  }

  function mapPayload(payload, linkedAccounts = []) {
    return {
      category_id: Number(payload.category_id),
      income_category_id: Number(payload.category_id),
      name: payload.name?.trim(),
      base_price: Number(payload.base_price ?? 0),
      status: payload.status ?? 'Active',
      linked_accounts: linkedAccounts,
      is_bills_payable_link:
        payload.is_bills_payable_link === true ||
        payload.is_bills_payable_link === 1 ||
        payload.is_bills_payable_link === '1',
    }
  }

  async function fetchHeads(force = false) {
    if (isLoading.value) return
    if (isLoaded.value && !force) return

    isLoading.value = true

    try {
      const { data, error } = await fetchAll(1, 300, {})
      if (error) throw error
      heads.value = (data?.data ?? []).map((head) => ({
        ...head,
        category_id: Number(head.category_id ?? head.income_category_id),
      }))
      isLoaded.value = true
    } finally {
      isLoading.value = false
    }
  }

  function findHeadIndex(headId) {
    return heads.value.findIndex((head) => Number(head.id) === Number(headId))
  }

  function getHeadsByCategory(categoryId) {
    return heads.value.filter((head) => Number(head.category_id) === Number(categoryId))
  }

  function getHead(headId) {
    return heads.value.find((head) => Number(head.id) === Number(headId)) ?? null
  }

  function getBillsPayableLinkedHead() {
    return heads.value.find((head) => head.is_bills_payable_link) ?? null
  }

  function validateLinkedAccounts(linkedAccounts = []) {
    const rows = Array.isArray(linkedAccounts) ? linkedAccounts : []
    const startedRows = rows.filter((link) => link.account_category)

    const normalized = startedRows.map((link) => ({
      account_category: link.account_category ?? '',
    }))

    const keys = new Set()

    for (const link of normalized) {
      if (!link.account_category) {
        return { ok: false, message: 'Please select an account type for each linked row.' }
      }

      if (keys.has(link.account_category)) {
        return { ok: false, message: 'Duplicate account types are not allowed.' }
      }

      keys.add(link.account_category)
    }

    return { ok: true, data: normalized }
  }

  async function addHead(payload) {
    const categoryId = Number(payload.category_id)

    if (!categoryId) {
      return { ok: false, message: 'Expense category is required.' }
    }

    if (!payload.name?.trim()) {
      return { ok: false, message: 'Expense head name is required.' }
    }

    const basePrice = Number(payload.base_price)
    if (!Number.isFinite(basePrice) || basePrice < 0) {
      return { ok: false, message: 'Please enter a valid base price.' }
    }

    const linkResult = validateLinkedAccounts(payload.linked_accounts)
    if (!linkResult.ok) {
      return linkResult
    }

    try {
      const response = await submitData(mapPayload(payload, linkResult.data))
      const created = response?.data
      if (created) {
        heads.value.push({
          ...created,
          category_id: Number(created.category_id ?? created.income_category_id),
        })
      } else {
        await fetchHeads(true)
      }
      return { ok: true, head: created }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to create income head.') }
    }
  }

  async function updateHead(payload) {
    const categoryId = Number(payload.category_id)

    if (!categoryId) {
      return { ok: false, message: 'Expense category is required.' }
    }

    if (!payload.name?.trim()) {
      return { ok: false, message: 'Expense head name is required.' }
    }

    const basePrice = Number(payload.base_price)
    if (!Number.isFinite(basePrice) || basePrice < 0) {
      return { ok: false, message: 'Please enter a valid base price.' }
    }

    const linkResult = validateLinkedAccounts(payload.linked_accounts)
    if (!linkResult.ok) {
      return linkResult
    }

    try {
      await updateData({ id: payload.id, ...mapPayload(payload, linkResult.data) })
      await fetchHeads(true)
      return { ok: true }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to update income head.') }
    }
  }

  return {
    item,
    isModal,
    isViewModal,
    isEditModal,
    moduleName,
    heads,
    isLoading,
    isLoaded,
    totalHeads,
    handleToggleModal,
    handleReset,
    fetchHeads,
    getHeadsByCategory,
    getHead,
    getBillsPayableLinkedHead,
    addHead,
    updateHead,
  }
})
