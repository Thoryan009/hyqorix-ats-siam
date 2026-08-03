<template>
  <div
    :class="[
      'shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-100 flex items-center justify-center',
      sizeClass,
    ]"
  >
    <img
      v-if="imageUrl && !imageFailed"
      :src="imageUrl"
      :alt="alt"
      class="h-full w-full object-cover"
      @error="imageFailed = true"
    />
    <div v-else class="flex flex-col items-center justify-center text-gray-400">
      <i class="fa fa-user" :class="iconClass"></i>
      <span v-if="showPlaceholderText" class="mt-1 text-[10px] font-medium">No Photo</span>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  imageUrl: {
    type: String,
    default: '',
  },
  alt: {
    type: String,
    default: 'Candidate photo',
  },
  size: {
    type: String,
    default: 'md',
  },
  showPlaceholderText: {
    type: Boolean,
    default: false,
  },
})

const imageFailed = ref(false)

const sizeClass = {
  sm: 'h-12 w-12',
  md: 'h-16 w-16',
  lg: 'h-24 w-24',
}[props.size] || 'h-16 w-16'

const iconClass = {
  sm: 'text-lg',
  md: 'text-2xl',
  lg: 'text-3xl',
}[props.size] || 'text-2xl'

watch(
  () => props.imageUrl,
  () => {
    imageFailed.value = false
  },
)
</script>
