import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'
import { fetchAll, updateData } from '../services/expenseCategoryService'
import { findCategoryByCode as findCategoryByCodeUtil } from '../data/expenseCategoryCodes'

export const useExpenseCategoryStore = defineStore('expenseCategory', () => {
  const { item, isModal, isViewModal, isEditModal, handleToggleModal, handleReset } =
    useModalHelpers()

  const moduleName = 'Expense Category'
  const categories = ref([])
  const isLoading = ref(false)
  const isLoaded = ref(false)

  const totalCategories = computed(() => categories.value.length)

  function mapDescriptionPayload(payload) {
    return {
      description: payload.description?.trim() ?? '',
    }
  }

  function getApiErrorMessage(error, fallback = 'Request failed.') {
    return (
      Object.values(error?.errors || {})?.flat()?.[0] ||
      error?.message ||
      fallback
    )
  }

  async function fetchCategories(force = false) {
    if (isLoading.value) return
    if (isLoaded.value && !force) return

    isLoading.value = true

    try {
      const { data, error } = await fetchAll(1, 100, {})
      if (error) throw error
      categories.value = data?.data ?? []
      isLoaded.value = true
    } finally {
      isLoading.value = false
    }
  }

  function findCategoryByCode(code) {
    return findCategoryByCodeUtil(categories.value, code)
  }

  async function updateCategory(payload) {
    try {
      await updateData({ id: payload.id, ...mapDescriptionPayload(payload) })
      await fetchCategories(true)
      return { ok: true }
    } catch (error) {
      return { ok: false, message: getApiErrorMessage(error, 'Failed to update expense category.') }
    }
  }

  function getPaginatedCategories(page, perPage) {
    const start = (page - 1) * perPage
    return categories.value.slice(start, start + perPage)
  }

  function getPaginationMeta(page, perPage) {
    const total = categories.value.length
    const lastPage = Math.max(1, Math.ceil(total / perPage))
    const to = Math.min(page * perPage, total)

    const links = Array.from({ length: lastPage }, (_, index) => ({
      label: String(index + 1),
      active: page === index + 1,
      url: null,
    }))

    return { total, to, links, lastPage }
  }

  return {
    item,
    isModal,
    isViewModal,
    isEditModal,
    moduleName,
    categories,
    isLoading,
    isLoaded,
    totalCategories,
    handleToggleModal,
    handleReset,
    fetchCategories,
    findCategoryByCode,
    updateCategory,
    getPaginatedCategories,
    getPaginationMeta,
  }
})
