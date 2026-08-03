<template>
  <div class="w-full">
    <DashboardSectionHeading title="Financial Overview" icon="fa fa-money" />

    <!-- Tabs -->
    <div class="mb-3 flex space-x-3">
      <button
        class="px-4 py-2 rounded-lg font-semibold"
        :class="activeTab === 'candidate' ? 'bg-primary text-white' : 'bg-gray-200 text-black'"
        @click="activeTab = 'candidate'"
      >
        Candidate Payments
      </button>
      <button
        class="px-4 py-2 rounded-lg font-semibold"
        :class="activeTab === 'client' ? 'bg-primary text-white' : 'bg-gray-200 text-black'"
        @click="activeTab = 'client'"
      >
        Client Payments
      </button>
    </div>

    <!-- Main Card -->
    <div
      class="w-full bg-white backdrop-blur-lg rounded-2xl shadow-xl p-4 border border-gray-200 hover:shadow-2xl transition-shadow duration-300"
    >
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 w-full">
        <router-link
          v-for="(card, index) in currentFinanceCards"
          :key="card.title"
          :to="getRoute(card.title)"
          class="block"
        >
          <div
            class="group relative overflow-hidden p-5 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 cursor-pointer"
            :class="
              card.title === 'Total Due'
                ? 'bg-linear-to-br from-red-50 to-red-100 border-2 border-red-200'
                : card.title === 'Total Transactions'
                  ? 'bg-linear-to-br from-primary/10 to-primary/10 border-2 border-primary/30'
                  : 'bg-linear-to-br from-primary/10 to-primary/10 border-2 border-primary/30'
            "
            :style="{ animationDelay: `${index * 50}ms` }"
          >
            <!-- Decorative circle -->
            <div
              class="absolute -right-4 -top-4 w-20 h-20 rounded-full opacity-10 group-hover:scale-150 transition-transform duration-500"
              :style="{ backgroundColor: card.color }"
            ></div>

            <!-- Icon -->
            <div class="relative flex items-center mb-3">
              <div
                class="w-12 h-12 rounded-lg flex items-center justify-center shadow-md group-hover:scale-110 group-hover:rotate-6 transition-all duration-300"
                :style="{ background: `linear-gradient(135deg, ${card.color}, ${card.color}DD)` }"
              >
                <i :class="card.icon + ' text-xl text-white'"></i>
              </div>
            </div>

            <!-- Text -->
            <div class="relative">
              <p class="text-sm font-semibold mb-1 uppercase tracking-wide text-black">
                {{ card.title }}
              </p>
              <p class="text-2xl font-bold text-black">
                {{ card.value }}
              </p>
            </div>
          </div>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import DashboardSectionHeading from '../../components/DashboardSectionHeading.vue'

// v-for="(card, index) in currentFinanceCards" :key="card.title"

const getRoute = (title) => {
  const base = activeTab.value // candidate বা client

  let type = ''

  switch (title) {
    case 'Total Transactions':
      type = `${base}`
      break
    case 'Total Billed':
      type = `${base}`
      break
    case 'Total Paid':
      type = `${base}`
      break
    case 'Total Due':
      type = `${base}`
      break
  }

  return {
    name: 'Transaction Report',
    params: { type },
  }
}

// Props
const props = defineProps({
  finance: {
    type: Object,
    required: true,
  },
})

// Reactive state
const activeTab = ref('candidate')

// Computed cards based on activeTab
const currentFinanceCards = computed(() => {
  const financeData =
    activeTab.value === 'candidate' ? props.finance?.candidate : props.finance?.client

  const cards = [
    {
      title: 'Total Transactions',
      value: financeData?.total_transactions,
      icon: 'fa fa-exchange',
      color: '#0D71B9',
    },
    {
      title: 'Total Billed',
      value: financeData?.total_billed,
      icon: 'fa fa-file-text',
      color: '#10b981',
    },
    {
      title: 'Total Paid',
      value: financeData?.total_paid,
      icon: 'fa fa-check-circle',
      color: '#10b981',
    },
    {
      title: 'Total Due',
      value: financeData?.total_due,
      icon: 'fa fa-clock-o',
      color: '#E2232A',
    },
  ]

  return cards
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
