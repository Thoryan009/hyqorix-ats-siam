<template>
  
  <section
    id="benefits"
    class="bg-gradient-to-br from-primary/10 to-primary/10 px-4 py-20 sm:px-6 lg:px-8"
  >
    <div class="mx-auto max-w-7xl">
      <div class="grid gap-12 lg:grid-cols-2 items-center">
        <div>
          <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl mb-6">
            {{ t('home.benefits.title') }}
          </h2>
          <div class="space-y-6">
            <div
              v-for="benefit in benefits"
              :key="benefit.title"
              class="flex items-start space-x-4"
            >
              <div class="flex-shrink-0 mt-1">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/20">
                  <svg
                    class="h-5 w-5 text-primary-dark"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                </div>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ benefit.title }}</h3>
                <p class="mt-1 text-gray-600">{{ benefit.description }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="relative">
          <div class="rounded-2xl bg-white p-8 shadow-2xl">
            <div class="mb-6 text-center">
              <div
                class="text-5xl font-bold text-transparent bg-gradient-to-r from-primary to-primary-dark bg-clip-text"
              >
                {{t('home.benefits.processAutomationValue')}}
              </div>
              <div class="mt-2 text-gray-600">{{ t('home.benefits.processAutomation') }}</div>
            </div>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">{{ t('home.benefits.processAutomation') }}</span>
                <span class="text-sm font-semibold text-primary">↑ {{ t('home.benefits.processAutomationValue') }}</span>
              </div>
              <div class="h-2 rounded-full bg-gray-200">
                <div
                  class="h-2 rounded-full bg-gradient-to-r from-primary/50 to-primary-dark w-4/5"
                ></div>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">{{ t('home.benefits.clientSatisfaction') }}</span>
                <span class="text-sm font-semibold text-primary">↑ {{ t('home.benefits.clientSatisfactionValue') }}</span>
              </div>
              <div class="h-2 rounded-full bg-gray-200">
                <div
                  class="h-2 rounded-full bg-gradient-to-r from-primary/50 to-primary-dark w-[95%]"
                ></div>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">{{ t('home.benefits.manualWork') }}</span>
                <span class="text-sm font-semibold text-primary">↓ {{ t('home.benefits.manualWorkValue') }}</span>
              </div>
              <div class="h-2 rounded-full bg-gray-200">
                <div
                  class="h-2 rounded-full bg-gradient-to-r from-primary/50 to-primary-dark w-3/4"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n';

const { t, tm } = useI18n();

const benefits = computed(() => tm('home.benefits.items') || [])


</script>
