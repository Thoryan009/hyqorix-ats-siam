<template>
  <section class="relative overflow-hidden px-4 py-20 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
      <div class="grid gap-12 lg:grid-cols-2 lg:gap-16 items-center">
        <!-- Left Content -->
        <div class="space-y-8">
          <div
            class="inline-flex items-center rounded-full bg-primary-light px-4 py-2 text-sm font-medium text-primary"
          >
            <span class="mr-2">🚀</span>
            {{ t('home.hero.subTitle') }}
          </div>

          <!-- {{ t('home.hero.title') }} -->

          <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl">
            <span class="bg-linear-to-r from-primary to-primary-dark bg-clip-text text-transparent"
              >{{ t('home.hero.titleFirst') }}</span
            >
            {{ t('home.hero.titleSecond') }}
          </h1>

          <p class="text-lg text-gray-600 leading-relaxed">
            {{ t('home.hero.description') }}
          </p>

          <div class="flex flex-col sm:flex-row gap-4">
            <button
              @click="emit('onLogin')"
              class="flex items-center justify-center rounded-xl bg-gradient-to-r from-primary to-primary-dark px-8 py-4 text-base font-semibold text-white shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-200 hover:from-primary hover:to-primary-dark cursor-pointer"
            >
              {{ t('home.hero.btnRequestDemo') }}
              <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13 7l5 5m0 0l-5 5m5-5H6"
                />
              </svg>
            </button>
            <button
              class="flex items-center justify-center rounded-xl border-2 border-gray-300 bg-white px-8 py-4 text-base font-semibold text-gray-700 hover:border-primary/60 hover:text-primary/80 transition-all duration-200 cursor-pointer"
            >
              <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
              {{ t('home.hero.btnSystemDemo') }}
            </button>
          </div>

          <!-- Stats -->
          <div class="grid grid-cols-3 gap-6 pt-8">
            <div>
              <div class="text-3xl font-bold text-gray-900">{{ t('home.hero.processStagesValue') }}</div>
              <div class="text-sm text-gray-600">{{ t('home.hero.processStages') }}</div>
            </div>
            <div>
              <div class="text-3xl font-bold text-gray-900">{{ t('home.hero.supportValue') }}</div>
              <div class="text-sm text-gray-600">{{ t('home.hero.support') }}</div>
            </div>
            <div>
              <div class="text-3xl font-bold text-gray-900">{{ t('home.hero.customizableValue') }}</div>
              <div class="text-sm text-gray-600">{{ t('home.hero.customizable') }}</div>
            </div>
          </div>
        </div>

        <!-- Right Image/Illustration -->
        <div class="relative">
          <div
            class="absolute -inset-4 rounded-3xl bg-gradient-to-r from-primary-gradient to-primary-ring opacity-30 blur-3xl"
          ></div>
          <div class="relative rounded-2xl bg-white p-8 shadow-2xl">
            <div class="space-y-4">
              <!-- Mock Dashboard Preview -->
              <div
                class="flex items-center justify-between rounded-lg bg-gradient-to-r from-primary-light to-primary-gradient p-4"
              >
                <div class="flex items-center space-x-3">
                  <div class="h-10 w-10 rounded-full bg-primary/60"></div>
                  <div>
                    <div class="h-3 w-24 rounded bg-gray-300"></div>
                    <div class="mt-1 h-2 w-16 rounded bg-gray-200"></div>
                  </div>
                </div>
                <div class="h-8 w-8 rounded-lg bg-primary/20"></div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="rounded-lg bg-primary/10 p-4">
                  <div class="text-2xl font-bold text-primary">{{ t('home.hero.workOrderValue') }}</div>
                  <div class="text-xs text-gray-600">{{ t('home.hero.workOrder') }}</div>
                </div>
                <div class="rounded-lg bg-primary/10 p-4">
                  <div class="text-2xl font-bold text-primary">{{ t('home.hero.ApplicationsValue') }}</div>
                  <div class="text-xs text-gray-600">{{ t('home.hero.Applications') }}</div>
                </div>
              </div>

              <div class="space-y-3">
                <div
                  class="flex items-center justify-between rounded-lg border-2 border-gray-100 p-3"
                >
                  <div class="flex items-center space-x-3">
                    <div class="h-3 w-3 rounded-full bg-primary/60"></div>
                    <div class="h-3 w-32 rounded bg-gray-200"></div>
                  </div>
                  <div class="h-6 w-16 rounded bg-primary/30"></div>
                </div>
                <div
                  class="flex items-center justify-between rounded-lg border-2 border-gray-100 p-3"
                >
                  <div class="flex items-center space-x-3">
                    <div class="h-3 w-3 rounded-full bg-primary/60"></div>
                    <div class="h-3 w-28 rounded bg-gray-200"></div>
                  </div>
                  <div class="h-6 w-16 rounded bg-primary/30"></div>
                </div>
                <div
                  class="flex items-center justify-between rounded-lg border-2 border-gray-100 p-3"
                >
                  <div class="flex items-center space-x-3">
                    <div class="h-3 w-3 rounded-full bg-primary/60"></div>
                    <div class="h-3 w-36 rounded bg-gray-200"></div>
                  </div>
                  <div class="h-6 w-16 rounded bg-primary/30"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n';

const emit = defineEmits(['onLogin'])
const { t } = useI18n()
</script>
