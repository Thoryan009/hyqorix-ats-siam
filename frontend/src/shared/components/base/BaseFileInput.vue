<template>
  <div>
    <label
      :for="id"
      class="w-full flex items-center justify-between cursor-pointer rounded-md border border-gray-300 px-3 py-2
             bg-white hover:bg-gray-50 transition
             focus-within:ring-2 focus-within:ring-primary"
    >
      <div class="flex items-center gap-2">
        <i class="fa fa-upload text-gray-600"></i>
        <span class="text-gray-700">
          {{ fileName || (multiple ? 'Choose files...' : 'Choose file...') }}
        </span>
      </div>

      <input
        :id="id"
        type="file"
        :accept="accept"
        :multiple="multiple"
        class="hidden"
        @change="onFileChange"
      />
    </label>
  </div>
</template>

<script setup>
defineProps({
  id: {
    type: String,
    default: () => `file-${Math.random().toString(36).slice(2)}`,
  },
  accept: {
    type: String,
    default: '.pdf,.jpg,.jpeg,.png',
  },
  fileName: {
    type: String,
    default: '',
  },
  multiple: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['change'])

const onFileChange = (event) => {
  emit('change', event)
}
</script>
