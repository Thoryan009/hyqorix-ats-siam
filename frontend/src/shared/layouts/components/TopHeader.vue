<template>
  <header
    class="relative z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 shadow sm:px-6 lg:px-5"
  >
    <!-- Left -->
    <div class="flex min-w-0 flex-1 items-center gap-4">
      <button
        @click="$emit('toggle-sidebar')"
        class="mr-4 text-gray-600 hover:text-gray-800 lg:hidden"
      >
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16"
          />
        </svg>
      </button>

      <h2 class="shrink-0 text-xl font-semibold text-gray-800">{{ t('navigation.dashboard') }}</h2>

      <div class="hidden min-w-0 flex-1 items-center gap-3 md:flex">
        <div v-if="can('ats.view')" class="min-w-0 flex-1">
          <QuickApplicationSearch />
        </div>
        <!-- Language Switch -->
          <div>
            <button
              @click="handleSwitchLanguage"
              class="rounded-lg bg-gray-200 px-4 lg:px-6 py-2 lg:py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-300 transition-all duration-200 cursor-pointer"
            >{{ currentLangLabel }}</button>
          </div>
        <TopAgentBadge v-if="can('agent.view')" />
        <TopEmployeeBadge v-if="can('employee.view')" />
      </div>
    </div>

    <!-- Right -->
    <div class="relative flex items-center space-x-4 ms-3">
      <div>
        <BaseButton @click="handleRefresh">
          <i class="fa fa-refresh me-2"></i>
          <span v-if="refreshLoading">{{ t('shared.placeholders.refreshing') }}</span>
          <span v-else>{{ t('shared.placeholders.refresh') }}</span>
        </BaseButton>
      </div>

      <!-- Profile Button -->
      <div
        @click="$emit('toggle-profile-dropdown')"
        class="flex items-center space-x-2 rounded-full p-1.5 hover:bg-gray-100 focus:ring-2 focus:ring-primary border border-gray-200 cursor-pointer transition"
      >
        <div class="flex gap-2">
          <UserAvatar :name="user?.name" :type="user?.type" :image="user?.employee_image" />
          <div class="flex flex-col items-start">
            <p class="text-sm font-medium text-gray-900">{{ user?.name }}</p>
            <p class="text-xs text-gray-500">{{ user?.email }}</p>
          </div>
        </div>

        <svg
          class="hidden h-5 w-5 text-gray-600 sm:block"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 9l-7 7-7-7"
          />
        </svg>
      </div>

      <!-- Dropdown -->
      <div
        v-if="isProfileDropdownOpen"
        class="absolute top-14 right-0 z-10 mt-2 w-52 rounded-lg bg-white py-2 shadow-xl ring-1 ring-black/20 ring-opacity-5"
      >
        <!-- User Info -->
        <div class="border-b border-gray-200 px-4 pb-3">
          <p class="text-sm font-medium text-gray-900">{{ user?.name }}</p>
          <p class="text-xs text-gray-500">{{ user?.email }}</p>

          <span
            class="mt-1 inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
            :class="userTypeBadgeClass"
            >{{ formattedUserType }}</span
          >
        </div>

        <!-- Actions -->
        <router-link
          to="/settings"
          @click.prevent="$emit('profile-action', 'settings')"
          class="flex items-center px-4 py-2 text-sm hover:bg-gray-100 border-b border-gray-200"
          >{{t('shared.labels.settings')}}</router-link
        >

        <button
          @click="$emit('profile-action', 'logout')"
          class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
        >
          {{t('navigation.logout')}}
          </button>

        </div>
    </div>
  </header>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import UserAvatar from './UserAvatar.vue'
import QuickApplicationSearch from './QuickApplicationSearch.vue'
import TopEmployeeBadge from './TopEmployeeBadge.vue'
import TopAgentBadge from './TopAgentBadge.vue'
import { getFormattedUserType, getUserTypeClasses } from '@/shared/helpers/userHelpers'
import { useAuthMutations } from '../../../modules/auth/queries/useAuthMutations'
import { usePermission } from '@/shared/composables/usePermission'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
const props = defineProps({
  activeNav: String,
  isProfileDropdownOpen: Boolean,
  user: Object,
})
const router = useRouter()
const { t, locale } = useI18n()
const currentLangLabel = computed(() => (locale.value === 'en' ? 'বাংলা' : 'English'))


// Language switch
const handleSwitchLanguage = () => {
  locale.value = locale.value === 'en' ? 'bn' : 'en'
  localStorage.setItem(import.meta.env.VITE_LANG, locale.value)
}
const { can } = usePermission()

const formattedUserType = computed(() => getFormattedUserType(props.user?.type))

const userTypeBadgeClass = computed(() => getUserTypeClasses(props.user?.type).badge)

const { refresh, refreshLoading } = useAuthMutations('Auth')

const handleRefresh = async () => {
  await refresh.mutateAsync()
}
</script>
