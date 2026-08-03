<template>
  <div class="flex flex-col md:flex-row gap-4 my-4 w-full md:w-auto">
    <router-link
      v-for="route in routesTemp"
      :key="route.name"
      :to="{ name: 'ATS Report', params: { type: route.type } }"
      class="w-full md:w-48 px-4 py-2 rounded-xl border cursor-pointer transition-all bg-white text-gray-700 shadow-sm hover:shadow-md hover:-translate-y-0.5"
      :class="
        currentRoute === route.type
          ? 'border-primary bg-primary-light! ring-1 ring-primary'
          : 'border-gray-200'
      "
    >
      <p class="text-sm font-semibold">
        {{ route.label }}
      </p>
      <p class="text-xs mt-1 text-gray-500">
        {{ route.description }}
      </p>
    </router-link>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const route = useRoute()

// computed instead of manual assignment
const currentRoute = computed(() => route.params.type)

const routesTemp = computed (() => [
  {
    name: 'All Reports',
    label: t('report.all_reports'),
    description: t('report.includes_all_records'),
    type: 'all',
  },
  // {
  //   name: 'Active Report',
  //   label: t('report.active_report'),
  //   description: t('report.currently_valid_running'),
  //   type: 'active',
  // },
  // {
  //   name: 'Expiring Soon Report',
  //   label: t('report.expiring_soon_report'),
  //   description: t('report.about_to_expire_shortly'),
  //   type: 'expiring',
  // },
  // {
  //   name: 'Expired Report',
  //   label: t('report.expired_report'),
  //   description: t('report.already_expired_records'),
  //   type: 'expired',
  // },
])

const routes = [
  {
    name: 'All Reports',
    label: 'All Reports',
    description: 'Includes all records',
    type: 'all',
  },
  // {
  //   name: 'Active Report',
  //   label: 'Active Report',
  //   description: 'Currently valid & running',
  //   type: 'active',
  // },
  // {
  //   name: 'Expiring Soon Report',
  //   label: 'Expiring Soon Report',
  //   description: 'About to expire shortly',
  //   type: 'expiring',
  // },
  // {
  //   name: 'Expired Report',
  //   label: 'Expired Report',
  //   description: 'Already expired records',
  //   type: 'expired',
  // },
]
</script>
