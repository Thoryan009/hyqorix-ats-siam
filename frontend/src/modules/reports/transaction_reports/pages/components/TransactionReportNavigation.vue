<template>
  <div class="flex flex-col md:flex-row gap-4 my-4 w-full md:w-auto">
    <router-link
      v-for="route in routes"
      :key="route.name"
      :to="{ name: 'Transaction Report', params: { type: route.type } }"
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
      <p class="text-xs mt-1 text-gray-500">
        {{ route.description }}
      </p>
    </router-link>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

// computed instead of manual assignment
const currentRoute = computed(() => route.params.type)

const routes = [
  {
    name: 'Candidate Transaction Report',
    label: 'Candidate Transaction Report',
    description: 'About to Candidate Transaction ',
    type: 'candidate',
  },
  {
    name: 'Client Transaction Report',
    label: 'Client Transaction Report',
    description: 'About to Client Transaction',
    type: 'client',
  },
]
</script>
