<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-white">
    <div class="text-center">
      <!-- Animated Logo -->
      <div class="mb-8 flex justify-center">
        <div class="relative">
          <img :src="settingsData?.company_logo_url" alt="Logo" class="h-24 w-auto animate-pulse" />
          <!-- Spinning ring around logo -->
          <div class="absolute inset-0 -m-4">
            <div
              class="h-32 w-32 rounded-full border-4 border-t-primary border-r-primary-hover border-b-primary-gradient border-l-transparent animate-spin"
            ></div>
          </div>
        </div>
      </div>

      <!-- Loading Text -->
      <h2 class="text-2xl font-bold text-gray-800 mb-3">Welcome Back!</h2>
      <p class="text-gray-600 mb-8">Setting up your workspace...</p>

      <!-- Progress Dots -->
      <div class="flex justify-center gap-2">
        <span
          class="h-3 w-3 bg-primary rounded-full animate-bounce"
          style="animation-delay: 0s"
        ></span>
        <span
          class="h-3 w-3 bg-primary-hover rounded-full animate-bounce"
          style="animation-delay: 0.2s"
        ></span>
        <span
          class="h-3 w-3 bg-primary-gradient rounded-full animate-bounce"
          style="animation-delay: 0.4s"
        ></span>
      </div>
    </div>
  </div>
</template>

<script setup>

defineProps({
  settingsData: {
    type: Object,
    default: () => ({}),
  },
})
</script>

<style scoped>
@keyframes bounce {
  0%,
  100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.animate-bounce {
  animation: bounce 1s ease-in-out infinite;
}
</style>
