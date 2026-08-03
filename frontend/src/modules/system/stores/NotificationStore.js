import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axiosInstance from '@/config/axiosConfig'

export const useNotificationStore = defineStore('notification', () => {
  const notifications = ref([])
  const unreadCount = ref(0)
  const isLoading = ref(false)
  const isOpen = ref(false)
  const currentPage = ref(1)
  const lastPage = ref(1)
  const total = ref(0)
  let pollInterval = null

  const hasUnread = computed(() => unreadCount.value > 0)

  // ─── Fetch unread count (lightweight poll) ──────────────────────────────────
  async function fetchUnreadCount() {
    try {
      const { data } = await axiosInstance.get('/notifications/unread-count')
      unreadCount.value = data?.data?.count ?? 0
    } catch {
      // silent fail
    }
  }

  // ─── Fetch full list (called when dropdown opens) ────────────────────────────
  async function fetchNotifications(page = 1) {
    isLoading.value = true
    try {
      const { data } = await axiosInstance.get('/notifications', {
        params: { page, per_page: 15 },
      })
      if (page === 1) {
        notifications.value = data?.data ?? []
      } else {
        notifications.value = [...notifications.value, ...(data?.data ?? [])]
      }
      currentPage.value = data?.meta?.current_page ?? 1
      lastPage.value = data?.meta?.last_page ?? 1
      total.value = data?.meta?.total ?? 0
      unreadCount.value = data?.meta?.unread_count ?? 0
    } catch {
      // silent fail
    } finally {
      isLoading.value = false
    }
  }

  // ─── Load more ───────────────────────────────────────────────────────────────
  async function loadMore() {
    if (currentPage.value < lastPage.value) {
      await fetchNotifications(currentPage.value + 1)
    }
  }

  // ─── Mark single as read ─────────────────────────────────────────────────────
  async function markAsRead(id) {
    try {
      const { data } = await axiosInstance.post(`/notifications/${id}/read`)
      const idx = notifications.value.findIndex((n) => n.id === id)
      if (idx !== -1) {
        notifications.value[idx] = data?.data ?? notifications.value[idx]
      }
      if (unreadCount.value > 0) unreadCount.value--
    } catch {
      // silent fail
    }
  }

  // ─── Mark all as read ────────────────────────────────────────────────────────
  async function markAllAsRead() {
    try {
      await axiosInstance.post('/notifications/read-all')
      notifications.value = notifications.value.map((n) => ({
        ...n,
        is_read: true,
        read_at: new Date().toISOString(),
      }))
      unreadCount.value = 0
    } catch {
      // silent fail
    }
  }

  // ─── Delete single notification ──────────────────────────────────────────────
  async function deleteNotification(id) {
    try {
      await axiosInstance.delete(`/notifications/${id}`)
      notifications.value = notifications.value.filter((n) => n.id !== id)
      total.value = Math.max(0, total.value - 1)
    } catch {
      // silent fail
    }
  }

  // ─── Open/close dropdown ─────────────────────────────────────────────────────
  function openDropdown() {
    isOpen.value = true
    fetchNotifications(1)
  }

  function closeDropdown() {
    isOpen.value = false
  }

  function toggleDropdown() {
    if (isOpen.value) {
      closeDropdown()
    } else {
      openDropdown()
    }
  }

  // ─── Polling ─────────────────────────────────────────────────────────────────
  function startPolling(intervalMs = 30000) {
    stopPolling()
    fetchUnreadCount()
    pollInterval = setInterval(fetchUnreadCount, intervalMs)
  }

  function stopPolling() {
    if (pollInterval) {
      clearInterval(pollInterval)
      pollInterval = null
    }
  }

  return {
    notifications,
    unreadCount,
    isLoading,
    isOpen,
    currentPage,
    lastPage,
    total,
    hasUnread,
    fetchUnreadCount,
    fetchNotifications,
    loadMore,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    openDropdown,
    closeDropdown,
    toggleDropdown,
    startPolling,
    stopPolling,
  }
})
