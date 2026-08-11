<template>
  <div class="w-full">
    <!-- as this component is an option api, so $t is not needed to import.  vue i18n will automatically provide the $t function in the template context. -->
    <DashboardSectionHeading
      :title="$t('dashboard.candidate_process_pipeline')"
      icon="fa fa-random"
      icon-class="bg-linear-to-br from-tertiary to-tertiary/70"
    />
    <!-- Main Card -->
    <div
      class="w-full bg-white backdrop-blur-lg rounded-2xl shadow-xl p-4 border border-gray-200 hover:shadow-2xl transition-shadow duration-300"
    >
      <!-- Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7 gap-3 w-full">
        <div
          v-for="(card, index) in pipelineCards"
          :key="card.title"
          class="group relative overflow-hidden p-3.5 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 cursor-pointer bg-linear-to-br from-white to-gray-50 border-2"
          :style="{ animationDelay: `${index * 30}ms`, borderColor: `${card.color}55` }"
          @click="goToApplicationReport(card)"
        >
          <!-- Progress bar -->
          <div class="absolute top-0 left-0 right-0 h-1 bg-gray-200 overflow-hidden">
            <div
              class="h-full transition-all duration-500"
              :style="{
                width: `${(card.value / maxValue) * 100}%`,
                background: card.color,
              }"
            ></div>
          </div>

          <div class="relative grid grid-cols-[auto_1fr] gap-x-2.5 gap-y-0.5 items-center mt-1">
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

          <!-- Hover border effect -->
          <div
            class="absolute inset-0 border-2 rounded-xl transition-all duration-300 pointer-events-none opacity-0 group-hover:opacity-100"
            :style="{ borderColor: card.color }"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script >
import DashboardSectionHeading from '../../components/DashboardSectionHeading.vue'
import { usePrimaryColor } from '@/shared/composables/usePrimaryColor'

export default {
  name: 'CandidateProcessPipeline',
  components: {
    DashboardSectionHeading,
  },
  setup() {
    const primaryColor = usePrimaryColor()
    return { primaryColor }
  },
  props: {
    pipeline: {
      type: Array,

    },
  },
  computed: {
    maxValue() {
      // Determine the max total for progress bar scaling
      return Math.max(...(this.pipeline?.map((item) => item.total) || [1]), 1)
    },
    pipelineCards() {
      // Map pipeline data to display cards
      const colors = [this.primaryColor, '#0D71B9', '#E2232A', '#F59E0B'] // rotate colors
      const icons = {
        offer_extended: 'fa fa-file-text',
        visa_authorization: 'fa fa-id-card',
        medical_test: 'fa fa-heartbeat',
        police_clearance: 'fa fa-shield',
        trade_test: 'fa fa-wrench',
        biometric_enrollment: 'fa fa-hand-paper-o',
        embassy_submission: 'fa fa-building',
        bmet_training: 'fa fa-graduation-cap',
        bmet_biometric_enrollment: 'fa fa-hand-paper-o',
        immigration_clearance: 'fa fa-plane',
        pta_request: 'fa fa-envelope',
        tra_process: 'fa fa-suitcase',
        on_boarding: 'fa fa-users',
      }

      return (
        this.pipeline?.map((item, index) => ({
          title: this.$te(`dashboard.pipeline.${item.process}`)
                  ? this.$t(`dashboard.pipeline.${item.process}`)
                  : this.formatTitle(item.process),
          process: item.process,
          value: item.total,
          icon: icons[item.process] || 'fa fa-circle',
          color: colors[index % colors.length],
        })) || []
      )
    },
  },
  methods: {
    formatTitle(process) {
      return process.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase())
    },
    goToApplicationReport(card) {
      this.$router.push({
        name: 'Applicant Report',
        params: { type: 'application' },
        query: {
          process: card.process,
          filter_tab: 'ats_process',
        },
      })
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
