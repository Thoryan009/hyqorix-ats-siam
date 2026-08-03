<template>
  <transition name="fade">
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
    >
      <div class="flex flex-col items-center space-y-3">
        <!-- Spinner -->
        <div
          class="w-12 h-12 border-4 border-white border-t-transparent rounded-full animate-spin"
        ></div>

        <!-- Text -->
        <p class="text-white text-sm font-medium">
          {{ text }}
        </p>
      </div>
    </div>
  </transition>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false
  },
  text: {
    type: String,
    default: "Loading..."
  }
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
