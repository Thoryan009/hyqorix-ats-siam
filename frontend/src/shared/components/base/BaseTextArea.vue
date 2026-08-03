<template>
  <div>
    <textarea :id="id" :placeholder="placeholder" :value="modelValue" :required="required" :rows="rows"
      @input="$emit('update:modelValue', $event.target.value)"
      :disabled="disabled"
      :class="`${disabled ? 'opacity-50 cursor-not-allowed' : ''} ${className}`"></textarea>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  placeholder: {
    type: String,
    default: '',
  },
  id: {
    type: String,
    default: '',
  },
  required: {
    type: Boolean,
    default: false,
  },
  rows: {
    type: Number,
    default: 2,
  },
   disabled: {
    type: Boolean,
    default: false,
  },
  className: {
    type: String,
    default: 'w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary',
  },
})

defineEmits(['update:modelValue'])
</script>
