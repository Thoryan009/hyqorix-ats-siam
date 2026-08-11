<template>
  <section class="mx-auto max-w-5xl">
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
      <div
        class="relative overflow-hidden bg-linear-to-br from-primary to-primary-dark px-6 py-10 sm:px-10"
      >
        <div
          class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10"
        ></div>
        <div
          class="pointer-events-none absolute -bottom-20 left-1/3 h-48 w-48 rounded-full bg-white/5"
        ></div>

        <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center">
          <div
            class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-lg"
          >
            <img :src="logoUrl" alt="Software logo" class="h-14 w-14 object-contain" />
          </div>

          <div class="min-w-0 text-white">
            <p
              class="mb-1 text-xs font-semibold uppercase tracking-[0.2em] text-white/70"
            >Product information</p>
            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">{{ softwareName }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-white/80">
              Applicant Tracking System for overseas recruitment — from demand letters to
              deployment, billing, and reporting.
            </p>
          </div>

          <div
            class="sm:ml-auto inline-flex items-center gap-2 self-start rounded-full bg-white/15 px-4 py-2 text-sm font-semibold text-white ring-1 ring-white/20"
          >
            <span class="h-2 w-2 rounded-full bg-white"></span>
            Version {{ version }}
          </div>
        </div>
      </div>

      <div class="grid gap-0 lg:grid-cols-[1.4fr_1fr]">
        <div class="border-b border-gray-100 p-6 sm:p-8 lg:border-b-0 lg:border-r">
          <h2
            class="mb-5 text-sm font-semibold uppercase tracking-wide text-gray-500"
          >Software details</h2>

          <dl class="divide-y divide-gray-100">
            <div
              v-for="item in details"
              :key="item.label"
              class="flex items-start gap-4 py-4 first:pt-0 last:pb-0"
            >
              <span
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-light text-primary"
              >
                <i :class="['fa', item.icon, 'text-sm']"></i>
              </span>
              <div class="min-w-0">
                <dt
                  class="text-xs font-medium uppercase tracking-wide text-gray-400"
                >{{ item.label }}</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-800">
                  <a
                    v-if="item.href"
                    :href="item.href"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-primary hover:text-primary-hover hover:underline"
                  >{{ item.value }}</a>
                  <span v-else>{{ item.value }}</span>
                </dd>
              </div>
            </div>
          </dl>
        </div>

        <aside class="bg-gray-50/80 p-6 sm:p-8">
          <h2 class="mb-5 text-sm font-semibold uppercase tracking-wide text-gray-500">Developer</h2>

          <div class="rounded-xl border border-gray-200 bg-white p-5">
            <p class="text-base font-semibold text-gray-900">Norqel Technologies</p>
            <p class="mt-2 text-sm leading-relaxed text-gray-500">
              Custom software for recruitment agencies, with implementation support and
              ongoing product updates.
            </p>

            <a
              href="https://www.norqel.com/"
              target="_blank"
              rel="noopener noreferrer"
              class="mt-5 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-hover"
            >
              Visit website
              <i class="fa fa-external-link text-xs"></i>
            </a>
          </div>

          <p class="mt-6 text-xs leading-relaxed text-gray-400">
            © {{ currentYear }} Norqel Technologies. All rights reserved. Unauthorized
            reproduction or distribution of this software is prohibited.
          </p>
        </aside>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { useSettingsQuery } from '@/modules/home/queries/useSettingsQuery'

const version = '26.1'
const currentYear = new Date().getFullYear()

const { data } = useSettingsQuery()
const settings = computed(() => data.value?.data || {})

const softwareName = computed(() => settings.value.software_name || 'Hyqorix ATS')
const logoUrl = computed(
  () => settings.value.fav_icon_url || settings.value.company_logo_url || '/hyqorix-logo.png'
)

const details = computed(() => [
  {
    icon: 'fa-desktop',
    label: 'Software',
    value: softwareName.value,
  },
  {
    icon: 'fa-code-fork',
    label: 'Version',
    value: version,
  },
  {
    icon: 'fa-building',
    label: 'Developed by',
    value: 'Norqel Technologies',
  },
  {
    icon: 'fa-globe',
    label: 'Website',
    value: 'www.norqel.com',
    href: 'https://www.norqel.com/',
  },
  {
    icon: 'fa-copyright',
    label: 'Copyright',
    value: `© ${currentYear} Norqel Technologies`,
  },
])
</script>
