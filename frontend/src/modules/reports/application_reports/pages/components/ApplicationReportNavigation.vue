<template>
  <div class="flex flex-col md:flex-row gap-4 my-4 mt-2 w-full md:w-auto">
    <router-link
      v-for="route in routesTemp"
      :key="route.name"
      :to="{ name: 'Applicant Report', params: { type: route.type } }"
      class="px-4 py-2 rounded-xl border cursor-pointer transition-all bg-white text-gray-700 shadow-sm hover:shadow-md hover:-translate-y-0.5"
      :class="
        currentRoute === route.type
          ? 'border-primary bg-primary-light! ring-1 ring-primary'
          : 'border-gray-200'
      "
    >
      <p class="text-sm font-semibold">
        {{ route.label }}
      </p>

    </router-link>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/modules/auth/store/authStore';
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const route = useRoute()

// computed instead of manual assignment
const currentRoute = computed(() => route.params.type)
const authStore = useAuthStore();

let routesTemp = computed (() => [
  {
    name: 'Applicant Report',
    label: t('report.applicant_reports'),
    description: 'Applicant report description',
    type: 'application',
  },
  {
    name: 'Client Report',
    label: t('report.client_report'),
    description: 'Client report description',
    type: 'client',
  },
  {
    name: 'Agent Report',
    label: t('report.agent_report'),
    description: 'Agent report description',
    type: 'agent',
  },
  {
    name: 'Principal Report',
    label: t('report.principal_report'),
    description: 'Principal report description',
    type: 'principal',
  }
])

if(authStore.userType === 'agent') {
  routesTemp.value = routesTemp.value.filter(route => route.type !== 'application' && route.type !== 'principal' && route.type !== 'client')
}
else if(authStore.userType === 'client') {
  routesTemp.value = routesTemp.value.filter(route => route.type !== 'application' && route.type !== 'agent' && route.type !== 'principal')
}
else if(authStore.userType === 'principal') {
  routesTemp.value = routesTemp.value.filter(route => route.type !== 'application' && route.type !== 'agent' && route.type !== 'client')
}
</script>
