<template>
  <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3">
    <div class="flex flex-wrap items-center gap-x-6 gap-y-1 text-sm">
      <span class="font-semibold text-green-800">Job Information</span>
      <span class="text-gray-500">
        Job Title:
        <span class="font-semibold text-gray-800">{{ job.job_title }}</span>
      </span>
      <span class="text-gray-500">
        Job Code:
        <span class="font-semibold text-gray-800">{{ job.job_code }}</span>
      </span>
      <span class="text-gray-500">
        Client:
        <span class="font-semibold text-gray-800">{{ job.client }}</span>
      </span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  job: { type: Object, required: true },
})
</script>
