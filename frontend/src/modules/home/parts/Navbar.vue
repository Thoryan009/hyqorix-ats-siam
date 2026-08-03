<template>
  <nav class="bg-white/90 backdrop-blur-md shadow-sm sticky top-0 z-50 transition-all duration-300">
    <div class="mx-auto max-w-7xl">
      <div class="flex h-14 sm:h-16 px-2 items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center space-x-2 shrink-0">

          <img
            :src="settingsData?.company_logo_url"
            alt="Merchant Overseas Logo"
            class="h-8 sm:h-10 w-auto"
          />
          <span
            class="text-lg sm:text-xl font-bold bg-primary bg-clip-text text-transparent whitespace-nowrap"
          >{{ settingsData?.company_name || appName }}</span>
        </div>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-1 lg:space-x-6">
          <a
            v-for="link in navLinks"
            :key="link.href"
            :href="link.href"
            @click="smoothScroll"
            class="relative px-3 py-2 text-sm lg:text-base text-gray-700 hover:text-primary-hover transition-colors font-medium group"
          >
            {{ link.label }}
            <span
              class="absolute bottom-0 left-0 w-0 h-0.5 bg-linear-to-r from-primary to-primary-dark group-hover:w-full transition-all duration-300"
            ></span>
          </a>

          <!-- Login Button -->
          <button
            @click="handleLogin"
            class="ml-2 lg:ml-4 rounded-lg bg-primary px-4 lg:px-6 py-2 lg:py-2.5 text-sm font-semibold text-white shadow-md hover:shadow-lg hover:scale-105 transition-all duration-200 whitespace-nowrap hover:bg-primary-hover cursor-pointer"
          >{{ t('home.login') }}</button>

          <!-- Language Switch -->
          <button
            @click="handleSwitchLanguage"
            class="rounded-lg bg-gray-200 px-4 lg:px-6 py-2 lg:py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-300 transition-all duration-200 cursor-pointer"
          >{{ currentLangLabel }}</button>
        </div>

        <!-- Mobile Menu Button -->
        <button
          @click="toggleMobileMenu"
          class="md:hidden rounded-lg p-2 text-gray-700 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
          aria-label="Toggle menu"
        >
          <svg
            class="h-6 w-6 transition-transform duration-200"
            :class="{ 'rotate-90': isMobileMenuOpen }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              v-if="!isMobileMenuOpen"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"
            />
            <path
              v-else
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      <!-- Mobile Navigation -->
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform -translate-y-2 opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform -translate-y-2 opacity-0"
      >
        <div v-if="isMobileMenuOpen" class="md:hidden py-3 space-y-1 border-t border-gray-100">
          <a
            v-for="link in navLinks"
            :key="link.href"
            :href="link.href"
            @click="handleMobileNavClick"
            class="block px-4 py-3 text-base text-gray-700 hover:bg-linear-to-r hover:from-primary-light hover:to-primary-gradient hover:text-primary rounded-lg transition-all duration-200 font-medium"
          >{{ link.label }}</a>

          <button
            @click="handleLogin"
            class="w-full mt-2 rounded-lg bg-linear-to-r from-primary/80 to-primary px-6 py-3 text-base font-semibold text-white shadow-md hover:shadow-lg transition-all duration-200 hover:bg-primary-hover hover:to-primary-dark cursor-pointer"
          >{{ t('home.login') }}</button>

          <!-- Mobile Language Switch -->
          <button
            @click="handleSwitchLanguage"
            class="w-full mt-2 rounded-lg bg-gray-200 px-6 py-3 text-base font-medium text-gray-700 hover:bg-gray-300 transition-all duration-200 cursor-pointer"
          >{{ currentLangLabel }}</button>
        </div>
      </Transition>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

// expose Vite env var to the template
const appName = import.meta.env.VITE_APP_NAME
const appLogoUrl = import.meta.env.VITE_APP_LOGO_URL

const router = useRouter()
const { t, locale } = useI18n()

const isMobileMenuOpen = ref(false)

defineProps({
  settingsData: {
    type: Object,
    default: () => ({}),
  },
})

// ✅ Load saved language on mount
onMounted(() => {
  const savedLang = localStorage.getItem(import.meta.env.VITE_LANG)
  if (savedLang) {
    locale.value = savedLang
  }
})

// ✅ Computed navLinks (reactive)
const navLinks = computed(() => [
  { label: t('home.nav.features'), href: '#features' },
  { label: t('home.nav.benefits'), href: '#benefits' },
])

// ✅ Language label
const currentLangLabel = computed(() => (locale.value === 'en' ? 'বাংলা' : 'English'))

// Toggle mobile menu
const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

// Mobile nav click
const handleMobileNavClick = (event) => {
  smoothScroll(event)
  setTimeout(() => {
    isMobileMenuOpen.value = false
  }, 300)
}

// Smooth scroll
const smoothScroll = (event) => {
  event.preventDefault()
  const targetId = event.currentTarget.getAttribute('href')
  const targetElement = document.querySelector(targetId)

  if (targetElement) {
    const navHeight = 64
    const targetPosition = targetElement.offsetTop - navHeight
    window.scrollTo({ top: targetPosition, behavior: 'smooth' })
  }
}

// Language switch
const handleSwitchLanguage = () => {
  locale.value = locale.value === 'en' ? 'bn' : 'en'
  localStorage.setItem(import.meta.env.VITE_LANG, locale.value)
}

// Login
const handleLogin = () => {
  router.push('/login')
}
</script>

<style scoped>
html {
  scroll-behavior: smooth;
}

nav {
  transition: box-shadow 0.3s ease;
}

@media (max-width: 768px) {
  a,
  button {
    min-height: 44px;
    display: flex;
    align-items: center;
  }
}
</style>
