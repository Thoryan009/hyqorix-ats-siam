<template>
  <div class="w-full">
    <DashboardSectionHeading
      title="Job Status Overview"
      icon="fa fa-briefcase"
      icon-class="bg-linear-to-br from-tertiary to-tertiary/70"
    />

    <!-- Main Card -->
    <div
      class="w-full bg-white backdrop-blur-lg rounded-2xl shadow-xl p-4 border border-gray-200 hover:shadow-2xl transition-shadow duration-300"
    >
      <!-- Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
        <div
          v-for="(card, index) in cards"
          :key="card.title"
          class="group relative overflow-hidden p-5 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 cursor-pointer w-full"
          :class="
            card.isAlert
              ? 'bg-gradient-to-br from-secondary/5 to-secondary/10 border-2 border-secondary/30'
              : 'bg-gradient-to-br from-primary/10 to-primary/10 border-2 border-primary/30'
          "
          :style="{ animationDelay: `${index * 50}ms` }"
          @click="card.path && $router.push(card.path)"
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
              :style="{ background: `linear-gradient(135deg, ${card.color}, ${card.color}dd)` }"
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
  name: 'JobStatusOverview',
  components: {
    DashboardSectionHeading,
  },
  props: {
    jobs: {
      type: Object,
    },
  },
  computed: {
    cards() {
      return [
        { title: 'Open', value: this.jobs?.open, icon: 'fa fa-folder-open', color: '#10b981', path: '/jobs' },
        {
          title: 'Closed',
          value: this.jobs?.closed,
          icon: 'fa fa-check-circle',
          color: '#e2232a',
          isAlert: this.jobs?.closed > 0,
          path: '/jobs',
        },
        { title: 'Hold', value: this.jobs?.hold, icon: 'fa fa-pause-circle', color: '#0D71B9', path: '/jobs' },
        {
          title: 'Total Vacancies',
          value: this.jobs?.total_vacancies,
          icon: 'fa fa-users',
          color: '#0d71b9',
          path: '/jobs',
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
