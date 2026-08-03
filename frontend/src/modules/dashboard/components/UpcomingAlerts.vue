<template>
  <div
    class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200 hover:shadow-2xl transition-all duration-300"
  >
    <!-- Header with animated gradient -->
    <div
      class="bg-gradient-to-r from-orange-500 to-red-500 px-4 py-2.5 flex items-center justify-between relative overflow-hidden cursor-pointer"
      @click="$router.push('/jobs')"
    >
      <div
        class="absolute inset-0 bg-gradient-to-r from-secondary/20 to-orange-400/20 animate-pulse"
      ></div>

      <div class="flex items-center gap-2 relative z-10">
        <div
          class="w-7 h-7 rounded-md bg-white/20 backdrop-blur-sm flex items-center justify-center text-white"
        >
          <i class="fa fa-bell text-sm"></i>
        </div>
        <h2 class="text-sm font-semibold text-white">Upcoming Deadlines</h2>
      </div>
      <!-- Urgent Badge -->
      <span
        class="bg-white text-black text-xs font-bold px-3 py-1 rounded-full shadow-lg relative z-10 animate-pulse"
        >⚠ Urgent</span
      >
    </div>

    <!-- Content -->
    <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <!-- Job Deadlines -->
      <div
        v-for="(deadline, index) in jobDeadlines"
        :key="'job-deadline-' + index"
        class="group p-5 rounded-xl bg-gradient-to-br from-secondary/5 to-secondary/10 border-2 border-secondary/30 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer"
        @click="$router.push('/jobs')"
      >
        <div class="flex items-center justify-between mb-2">
          <div
            class="w-10 h-10 rounded-lg bg-gradient-to-br from-secondary to-secondary/70 flex items-center justify-center shadow-md group-hover:scale-110 transition-transform"
          >
            <i class="fa fa-briefcase text-white"></i>
          </div>
          <span class="text-xs font-semibold text-black bg-white px-2 py-1 rounded-full">
            {{ daysUntil(deadline.deadline) }} days
          </span>
        </div>
        <p class="text-sm font-semibold text-black mb-1">Job Deadlines</p>
        <p class="text-2xl font-bold text-black">{{ formatDate(deadline.deadline) }}</p>
      </div>

      <!-- Interview Dates -->
      <div
        v-for="(interview, index) in interviews"
        :key="'interview-' + index"
        class="group p-5 rounded-xl bg-gradient-to-br from-tertiary/5 to-tertiary/10 border-2 border-tertiary/30 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer"
        @click="$router.push('/jobs')"
      >
        <div class="flex items-center justify-between mb-2">
          <div
            class="w-10 h-10 rounded-lg bg-gradient-to-br from-tertiary to-tertiary/70 flex items-center justify-center shadow-md group-hover:scale-110 transition-transform"
          >
            <i class="fa fa-users text-white"></i>
          </div>
          <span class="text-xs font-semibold text-black bg-white px-2 py-1 rounded-full">
            {{ daysUntil(interview.interview_date) }} days
          </span>
        </div>
        <p class="text-sm font-semibold text-black mb-1">Interview Dates</p>
        <p class="text-2xl font-bold text-black">{{ formatDate(interview.interview_date) }}</p>
      </div>

      <!-- Work Order Expiry -->
      <div
        v-for="(expiry, index) in workOrderExpiry"
        :key="'expiry-' + index"
        class="group p-5 rounded-xl bg-gradient-to-br from-secondary/5 to-secondary/10 border-2 border-secondary/30 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer"
        @click="$router.push('/jobs')"
      >
        <div class="flex items-center justify-between mb-2">
          <div
            class="w-10 h-10 rounded-lg bg-gradient-to-br from-secondary to-secondary/70 flex items-center justify-center shadow-md group-hover:scale-110 transition-transform"
          >
            <i class="fa fa-exclamation-triangle text-white"></i>
          </div>
          <span class="text-xs font-semibold text-black bg-white px-2 py-1 rounded-full">
            {{ daysUntil(expiry.end_date) }} days
          </span>
        </div>
        <p class="text-sm font-semibold text-black mb-1">Work Order Expiry</p>
        <p class="text-2xl font-bold text-black">{{ formatDate(expiry.end_date) }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const { alerts } = defineProps({
  alerts: {
    type: Object,
  },
})

const jobDeadlines = computed(() => alerts?.job_deadlines || [])
const interviews = computed(() => alerts?.interviews || [])
const workOrderExpiry = computed(() => alerts?.work_order_expiry || [])

// Helper to calculate days remaining
const daysUntil = (date) => {
  const today = new Date()
  const target = new Date(date)
  const diffTime = target - today
  return Math.max(Math.ceil(diffTime / (1000 * 60 * 60 * 24)), 0)
}

// Helper to format date as DD-MM-YYYY
const formatDate = (date) => {
  const d = new Date(date)
  return d.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}
</script>
