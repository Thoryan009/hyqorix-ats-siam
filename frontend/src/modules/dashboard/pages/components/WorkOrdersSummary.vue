<template>
  <div class="w-full">
    <DashboardSectionHeading
      title="Demand Letters & Jobs Overview"
      icon="fa fa-briefcase"
    />

    <!-- Main Card -->
    <div
      class="bg-white backdrop-blur-lg rounded-2xl shadow-xl p-4 border border-gray-200 hover:shadow-2xl transition-shadow duration-300"
    >
      <!-- 4 Columns Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="(card, index) in cards"
          :key="card.title"
          class="group relative overflow-hidden p-5 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 cursor-pointer"
          :class="
            card.isAlert
              ? 'bg-linear-to-br from-secondary/5 to-secondary/10 border-2 border-secondary/30'
              : 'bg-linear-to-br from-primary/10 to-primary/10 border-2 border-primary/30'
          "
          :style="{ animationDelay: `${index * 50}ms` }"
          @click="card.path && $router.push(card.path)"
        >
          <!-- Decorative circle -->
          <div
            class="absolute -right-4 -top-4 w-20 h-20 rounded-full opacity-10 group-hover:scale-150 transition-transform duration-500"
            :class="card.isAlert ? 'bg-secondary' : 'bg-primary'"
          ></div>

          <!-- Icon -->
          <div class="relative flex items-center mb-3">
            <div
              class="w-12 h-12 rounded-lg flex items-center justify-center shadow-md group-hover:scale-110 group-hover:rotate-6 transition-all duration-300"
              :class="
                card.isAlert
                  ? 'bg-linear-to-br from-secondary to-secondary'
                  : 'bg-linear-to-br from-primary to-primary'

              "
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
      </div>
    </div>
  </div>
</template>

<script>
import DashboardSectionHeading from '../../components/DashboardSectionHeading.vue'

export default {
  name: 'WorkOrdersSummary',
  components: {
    DashboardSectionHeading,
  },
  props: {
    workOrders: {
      type: Object,
    },
  },
  computed: {
    cards() {
      return [
        {
          title: 'Total Demand Letters',
          value: this.workOrders?.total,
          icon: 'fa fa-clipboard',
          isAlert: false,
          path: '/work-orders',
        },
        {
          title: 'Active',
          value: this.workOrders?.active,
          icon: 'fa fa-play',
          isAlert: false,
          path: '/work-orders',
        },
        {
          title: 'Expired',
          value: this.workOrders?.expired,
          icon: 'fa fa-times',
          isAlert: this.workOrders?.expired > 0,
          path: '/work-orders',
        },
        {
          title: 'Total Candidates Requested',
          value: this.workOrders?.total_candidates,
          icon: 'fa fa-users',
          isAlert: false,
          path: '/work-orders',
        },
      ]
    },
  },
}
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
