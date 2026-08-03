<template>
  <div
    v-if="src"
    class="relative mt-5 inline-block"
    :style="{
      width,
      height,
    }"
  >
    <!-- Close button -->
    <button
      v-if="showCancel"
      class="absolute -top-2 -right-2 w-6 h-6 bg-slate-900 rounded-full p-1 hover:bg-slate-700 cursor-pointer flex justify-center items-center transition text-slate-50"
      @click="cancelImage"
      type="button"
    >
      <i class="fa fa-times"></i>
    </button>

    <!-- Image -->
    <img
      :src="src"
      :alt="alt"
      class="object-contain w-full h-full border border-gray-300"
      :class="className"
    />
  </div>
</template>

<script setup>
defineProps({
  src: {
    type: String,
    default: null,
  },
  width: {
    type: String,
    default: '200px',
  },
  height: {
    type: String,
    default: '150px',
  },
  alt: {
    type: String,
    default: 'Preview',
  },
  showCancel: {
    type: Boolean,
    default: true,
  },
  className: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['cancelImage'])

const cancelImage = () => {
  emit('cancelImage')
}
</script>
