<template>
  <div class="w-full">
    <DashboardSectionHeading :title="t('dashboard.summary')" icon="fa fa-dashboard" />

    <!-- Main Card with gradient border -->
    <div
      class="bg-white backdrop-blur-lg rounded-2xl shadow-xl p-4 border border-gray-200 hover:shadow-2xl transition-shadow duration-300"
    >
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
        <div
          v-for="(card, index) in filteredCards"
          :key="card.title"
          class="group relative overflow-hidden p-3.5 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 cursor-pointer bg-linear-to-br from-white to-gray-50 border-2"
          :style="{ animationDelay: `${index * 50}ms`, borderColor: `${card.color}55` }"
          @click="card.path && $router.push(card.path)"
        >
          <div
            class="absolute -right-4 -top-4 w-16 h-16 rounded-full opacity-10 group-hover:scale-150 transition-transform duration-500"
            :style="{ backgroundColor: card.color }"
          ></div>

          <div class="relative grid grid-cols-[auto_1fr] gap-x-2.5 gap-y-0.5 items-center">
            <div
              class="row-span-2 w-9 h-9 rounded-lg flex items-center justify-center shadow-md group-hover:scale-110 transition-all duration-300 self-center"
              :style="{ background: `linear-gradient(135deg, ${card.color}, ${card.color}dd)` }"
            >
              <i :class="card.icon + ' text-sm text-white'"></i>
            </div>

            <p class="text-xs font-semibold uppercase tracking-wide truncate text-black">
              {{ card.title }}
            </p>

            <p class="text-xl font-bold leading-tight" :style="{ color: card.color }">
              {{ card.value }}
            </p>
          </div>

          <div
            class="absolute inset-0 border-2 rounded-xl transition-all duration-300 pointer-events-none opacity-0 group-hover:opacity-100"
            :style="{ borderColor: card.color }"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import DashboardSectionHeading from '../../components/DashboardSectionHeading.vue'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
// props
const props = defineProps({
  summary: {
    type: Object,
    default: () => ({}),
  },
})

// role (reactive)

// cards
const cards = computed(() => {
  const s = props.summary || {}

  return [
  {
      title: t('dashboard.total_jobs'),
      value: s.total_jobs || 0,
      icon: 'fa fa-briefcase',
      roles: ['super_admin', 'admin', 'recruiter', 'accountant', 'agent', 'client'],
      path: '/jobs',
    },
    {
      title: t('dashboard.total_applicants'),
      value: s.total_applications || 0,
      icon: 'fa fa-file-text',
      roles: ['super_admin', 'admin', 'recruiter', 'accountant', 'agent', 'client'],
      path: '/applications',
    },
    {
      title: t('dashboard.total_clients'),
      value: s.total_clients || 0,
      icon: 'fa fa-handshake-o',
      roles: ['super_admin', 'admin', 'recruiter', 'accountant'],
      path: '/clients',
    },
    {
      title: t('dashboard.total_agents'),
      value: s.total_agents || 0,
      icon: 'fa fa-user-secret',
      roles: ['super_admin', 'admin', 'recruiter', 'accountant'],
      path: '/agents',
    },
    {
      title: t('dashboard.total_employees'),
      value: s.total_employees || 0,
      icon: 'fa fa-id-badge',
      roles: ['super_admin', 'admin', 'recruiter', 'accountant'],
      path: '/employees',
    },
    {
      title: t('dashboard.total_demand_letters'),
      value: s.total_work_orders || 0,
      icon: 'fa fa-clipboard',
      roles: ['super_admin', 'admin', 'recruiter', 'accountant'],
      path: '/work-orders',
    },


  ]
})

// filtered cards (clean)
const summaryColors = ['#10b981', '#0D71B9', '#E2232A', '#F59E0B', '#9333EA', '#6366F1']

const filteredCards = computed(() => {
  return cards.value.map((card, index) => ({
    ...card,
    color: summaryColors[index % summaryColors.length],
  }))
})
</script>

<style scoped>
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.group {
  animation: slideIn 0.5s ease-out forwards;
}
</style>
