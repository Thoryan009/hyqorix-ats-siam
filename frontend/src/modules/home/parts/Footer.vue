<template>
  <footer class="relative bg-gray-900 px-4 py-16 sm:px-6 lg:px-8 overflow-hidden">
    <!-- Background decoration -->
    <div
      class="absolute inset-0 bg-gradient-to-br from-emerald-900/20 via-gray-900 to-emerald-800/20 pointer-events-none"
    ></div>

    <div class="relative mx-auto max-w-7xl">
      <!-- Top Section -->
      <div class="grid gap-12 lg:grid-cols-12 mb-12">
        <!-- Company Info - Larger Column -->
        <div class="lg:col-span-5">
          <div class="flex items-center space-x-3 mb-6">
            <img
              :src="settingsData?.company_logo_url"
              alt="Merchant Overseas Logo"
              class="h-12 w-auto"
              onerror="this.src = '/logo.jpg'"
            />
            <div>
              <span class="text-2xl font-bold text-white">{{
                settingsData?.company_name
              }}</span>
              <p class="text-xs text-primary">by Norqel Technologies</p>
            </div>
          </div>
          <p class="text-sm text-gray-300 mb-6 leading-relaxed max-w-md">
            {{ t('home.footer.description') }}
          </p>

          <!-- Contact Info Cards -->
          <div class="space-y-3">
            <div class="flex items-center space-x-3 text-gray-300">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-800">
                <svg
                  class="h-4 w-4 text-primary"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                  />
                </svg>
              </div>
              <span class="text-sm">{{ settingsData?.company_email }}</span>
            </div>
            <div class="flex items-center space-x-3 text-gray-300">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-800">
                <svg
                  class="h-4 w-4 text-primary"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                  />
                </svg>
              </div>
              <span class="text-sm">{{ settingsData?.company_phone }}</span>
            </div>
            <div class="flex items-center space-x-3 text-gray-300">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-800">
                <svg
                  class="h-4 w-4 text-primary"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                </svg>
              </div>
              <span class="text-sm">{{ settingsData?.company_address }}</span>
            </div>
          </div>
        </div>

        <!-- Links Grid -->
        <div class="lg:col-span-7 grid grid-cols-2 sm:grid-cols-3 gap-8">
          <!-- Product -->
          <div>
            <!-- <h4 class="text-sm font-bold text-white mb-5 uppercase tracking-wider">
              {{ t('home.footer.product') }}
            </h4> -->
            <ul class="space-y-3 text-sm">
              <li>
                <a
                  href="#features"
                  class="text-gray-400 hover:text-primary-hover transition-colors flex items-center group"
                >
                  <span
                    class="inline-block w-0 group-hover:w-2 h-px bg-primary transition-all mr-0 group-hover:mr-2"
                  ></span>
                  {{ t('home.footer.features') }}
                </a>
              </li>
              <li>
                <a
                  href="#pricing"
                  class="text-gray-400 hover:text-primary-hover transition-colors flex items-center group"
                >
                  <span
                    class="inline-block w-0 group-hover:w-2 h-px bg-primary transition-all mr-0 group-hover:mr-2"
                  ></span>
                  {{ t('home.footer.pricing') }}
                </a>
              </li>
              <li>
                <a
                  href="#benefits"
                  class="text-gray-400 hover:text-primary-hover transition-colors flex items-center group"
                >
                  <span
                    class="inline-block w-0 group-hover:w-2 h-px bg-primary-hover transition-all mr-0 group-hover:mr-2"
                  ></span>
                  {{ t('home.footer.benefits') }}
                </a>
              </li>
              <li>
                <a
                  href="#"
                  class="text-gray-400 hover:text-primary-hover transition-colors flex items-center group"
                >
                  <span
                    class="inline-block w-0 group-hover:w-2 h-px bg-primary-hover transition-all mr-0 group-hover:mr-2"
                  ></span>
                  {{ t('home.footer.documentation') }}
                </a>
              </li>
            </ul>
          </div>

          <!-- Support -->
          <div>
            <!-- <h4 class="text-sm font-bold text-white mb-5 uppercase tracking-wider">
              {{ t('home.footer.support') }}
            </h4> -->
            <ul class="space-y-3 text-sm">
              <li>
                <a
                  href="#contact"
                  class="text-gray-400 hover:text-primary-hover transition-colors flex items-center group"
                >
                  <span
                    class="inline-block w-0 group-hover:w-2 h-px bg-primary transition-all mr-0 group-hover:mr-2"
                  ></span>
                  {{ t('home.footer.contact') }}
                </a>
              </li>
              <li>
                <a
                  href="#"
                  class="text-gray-400 hover:text-primary-hover transition-colors flex items-center group"
                >
                  <span
                    class="inline-block w-0 group-hover:w-2 h-px bg-primary transition-all mr-0 group-hover:mr-2"
                  ></span>
                  {{ t('home.footer.helpCenter') }}
                </a>
              </li>
              <li>
                <a
                  href="#"
                  class="text-gray-400 hover:text-primary-hover transition-colors flex items-center group"
                >
                  <span
                    class="inline-block w-0 group-hover:w-2 h-px bg-primary transition-all mr-0 group-hover:mr-2"
                  ></span>
                  {{ t('home.footer.training') }}
                </a>
              </li>
              <li>
                <a
                  href="#"
                  class="text-gray-400 hover:text-primary-hover transition-colors flex items-center group"
                >
                  <span
                    class="inline-block w-0 group-hover:w-2 h-px bg-primary transition-all mr-0 group-hover:mr-2"
                  ></span>
                  {{ t('home.footer.dailySupport') }}
                </a>
              </li>
            </ul>
          </div>

          <!-- Legal -->
          <div>
            <!-- <h4 class="text-sm font-bold text-white mb-5 uppercase tracking-wider">
              {{ t('home.footer.legal') }}
            </h4> -->
            <ul class="space-y-3 text-sm">
              <li>
                <a
                  href="#"
                  class="text-gray-400 hover:text-primary-hover transition-colors flex items-center group"
                >
                  <span
                    class="inline-block w-0 group-hover:w-2 h-px bg-primary-hover transition-all mr-0 group-hover:mr-2"
                  ></span>
                  {{ t('home.footer.privacyPolicy') }}
                </a>
              </li>
              <li>
                <a
                  href="#"
                  class="text-gray-400 hover:text-primary-hover transition-colors flex items-center group"
                >
                  <span
                    class="inline-block w-0 group-hover:w-2 h-px bg-primary transition-all mr-0 group-hover:mr-2"
                  ></span>
                  {{ t('home.footer.termsOfService') }}
                </a>
              </li>
              <li>
                <a
                  href="#"
                  class="text-gray-400 hover:text-primary-hover transition-colors flex items-center group"
                >
                  <span
                    class="inline-block w-0 group-hover:w-2 h-px bg-primary transition-all mr-0 group-hover:mr-2"
                  ></span>
                  {{ t('home.footer.security') }}
                </a>
              </li>
              <li>
                <a
                  href="#"
                  class="text-gray-400 hover:text-primary-hover transition-colors flex items-center group"
                >
                  <span
                    class="inline-block w-0 group-hover:w-2 h-px bg-primary transition-all mr-0 group-hover:mr-2"
                  ></span>
                  {{ t('home.footer.compliance') }}
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Divider with Gradient -->
      <div class="relative mb-10">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-gray-800"></div>
        </div>
        <div class="relative flex justify-center">
          <span class="bg-gray-900 px-4">
            <div class="h-1 w-20 bg-gradient-to-r from-primary to-primary-hover rounded-full"></div>
          </span>
        </div>
      </div>

      <!-- Bottom Section -->
      <div class="flex flex-col md:flex-row justify-center items-center space-y-3 md:space-y-0">
        <!-- Copyright -->
        <div class="w-full py-4">
          <div
            class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-2"
          >
            <!-- Left Side -->
            <p class="text-sm text-gray-400 text-center md:text-left">
              &copy; {{ new Date().getFullYear() }}
              <span class="font-semibold text-gray-300">{{ settingsData.company_name  }}</span
              >. {{ t('home.footer.copyRight') }}
            </p>

            <!-- Right Side -->
            <p class="text-sm text-gray-400">
              {{ t('home.footer.developBy') }}
              <span
                class="font-semibold text-transparent bg-gradient-to-r from-primary to-primary bg-clip-text"
              >
                <a href="https://www.norqel.com/" target="_blank" class="hover:underline"
                  >Norqel Technologies</a
                >
              </span>
            </p>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>

import { useI18n } from 'vue-i18n'
const { t } = useI18n()

defineProps({
  settingsData: {
    type: Object,
    default: () => ({}),
  },
})


</script>
