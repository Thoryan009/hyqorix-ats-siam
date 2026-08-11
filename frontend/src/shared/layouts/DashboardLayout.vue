<template>
  <div class="flex h-screen bg-gray-100 custom-scrollbar">
    <Sidebar
      :isSidebarOpen="isSidebarOpen"
      :isSidebarCollapsed="isSidebarCollapsed"
      :activeNav="activeNav"
      :navItems="navItems"
      @toggle-sidebar="toggleSidebar"
      @toggle-collapse="toggleCollapse"
      @profile-action="handleProfileAction"
    />

    <div class="flex flex-1 flex-col overflow-hidden transition-all duration-300">
      <TopHeader
        :activeNav="activeNav"
        :user="user"
        :isProfileDropdownOpen="isProfileDropdownOpen"
        @toggle-sidebar="toggleSidebar"
        @toggle-profile-dropdown="toggleProfileDropdown"
        @profile-action="handleProfileAction"
      />

      <main class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-5">
        <router-view />
        <!-- <pre>{{ data }}</pre> -->
      </main>

      <FooterComponent />
    </div>

    <div
      v-if="isSidebarOpen"
      @click="toggleSidebar"
      class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"
    ></div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import Sidebar from './components/SideBarComponent.vue'
import TopHeader from './components/TopHeader.vue'
import { navItems } from './navItems.js' // ✅ import nav items
import { useRouter } from 'vue-router'

import { useAuthQuery } from '@/modules/auth/queries/useAuthQuery'
import { useAuthMutations } from '@/modules/auth/queries/useAuthMutations'
import { useAuthStore } from '@/modules/auth/store/authStore'

const isSidebarOpen = ref(false)
const isSidebarCollapsed = ref(false)
const isProfileDropdownOpen = ref(false)
const activeNav = ref('Dashboard')
const router = useRouter()
import FooterComponent from './components/FooterComponent.vue'

// Methods
const toggleSidebar = () => (isSidebarOpen.value = !isSidebarOpen.value)
const toggleCollapse = () => (isSidebarCollapsed.value = !isSidebarCollapsed.value)
const toggleProfileDropdown = () => (isProfileDropdownOpen.value = !isProfileDropdownOpen.value)

const { data, isLoading } = useAuthQuery()

const user = computed(() => data.value?.data ?? {})

const authStore = useAuthStore()
const { logout } = useAuthMutations('Auth', {
  onSuccess(data) {
    if (data?.success) {
      authStore.clearSession()
      router.push({ name: 'Login' })
    }
  },
})

const handleProfileAction = async (action) => {
  isProfileDropdownOpen.value = false
  if (action === 'logout') await logout.mutateAsync()
  else if (action === 'settings') console.log('Settings clicked')
}

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
  if (isProfileDropdownOpen.value && !e.target.closest('.relative')) {
    isProfileDropdownOpen.value = false
  }
})
</script>
