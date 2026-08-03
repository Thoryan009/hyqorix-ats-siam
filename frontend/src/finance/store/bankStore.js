import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'
import {
  bulkDelete,
  deleteItem,
  fetchAll,
  submitData,
  updateData,
} from '../services/bankService'

export const useBankStore = defineStore('bank', () => {
  const { item, isModal, isViewModal, isEditModal, handleToggleModal, handleReset } =
    useModalHelpers()

  const moduleName = 'Bank'
  const banks = ref([])
  const isLoading = ref(false)
  const isLoaded = ref(false)

  function getApiErrorMessage(error, fallback = 'Request failed.') {
    return (
      Object.values(error?.errors || {})?.flat()?.[0] ||
      error?.message ||
      fallback
    )
  }

  async function fetchBanks(force = false) {
    if (isLoading.value) return
    if (isLoaded.value && !force) return

    isLoading.value = true

    try {
      const { data, error } = await fetchAll(1, 300, {})
      if (error) throw error
      banks.value = data?.data ?? []
      isLoaded.value = true
    } finally {
      isLoading.value = false
    }
  }

  function findBankIndex(bankId) {
    return banks.value.findIndex((bank) => Number(bank.id) === Number(bankId))
  }

  async function addBank(payload) {
    try {
      await submitData(payload)
      await fetchBanks(true)
      return { ok: true }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to create bank.') }
    }
  }

  async function updateBank(payload) {
    try {
      await updateData(payload)
      await fetchBanks(true)
      return { ok: true }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to update bank.') }
    }
  }

  async function deleteBank(id) {
    try {
      await deleteItem(id)
      banks.value = banks.value.filter((bank) => Number(bank.id) !== Number(id))
      return { ok: true }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to delete bank.') }
    }
  }

  async function deleteBanks(ids) {
    try {
      await bulkDelete(ids)
      const idSet = new Set(ids.map((id) => Number(id)))
      banks.value = banks.value.filter((bank) => !idSet.has(Number(bank.id)))
      return { ok: true }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to delete banks.') }
    }
  }

  function getPaginatedBanks(page, perPage) {
    const start = (page - 1) * perPage
    return banks.value.slice(start, start + perPage)
  }

  function getPaginationMeta(page, perPage) {
    const total = banks.value.length
    const lastPage = Math.max(1, Math.ceil(total / perPage))
    const to = Math.min(page * perPage, total)

    const links = Array.from({ length: lastPage }, (_, index) => ({
      label: String(index + 1),
      active: page === index + 1,
      url: page === index + 1 ? null : '#',
    }))

    return { total, from: total === 0 ? 0 : (page - 1) * perPage + 1, to, links, lastPage }
  }

  return {
    item,
    isModal,
    isViewModal,
    isEditModal,
    moduleName,
    banks,
    isLoading,
    isLoaded,
    handleToggleModal,
    handleReset,
    fetchBanks,
    addBank,
    updateBank,
    deleteBank,
    deleteBanks,
    getPaginatedBanks,
    getPaginationMeta,
  }
})
