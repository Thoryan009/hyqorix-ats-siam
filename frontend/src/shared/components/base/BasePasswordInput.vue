<template>
  <div>
    <BaseLabel :for="id">{{ label }}</BaseLabel>

    <div class="relative">
      <input
        :id="id"
        :type="showPassword ? 'text' : 'password'"
        :placeholder="placeholder"
        :required="required"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        class="w-full px-4 py-3 border border-gray-300 rounded-lg pr-12 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#588F36] focus:border-transparent transition-all"
      />

      <button
        type="button"
        @click="togglePassword"
        class="absolute inset-y-0 right-4 flex items-center text-gray-500 hover:text-[#588F36] transition"
      >
        <i v-if="!showPassword" class="fa fa-eye cursor-pointer"></i>
        <i v-else class="fa fa-eye-slash cursor-pointer"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  label: {
    type: String,
    default: 'Password',
  },
  placeholder: {
    type: String,
    default: '••••••••',
  },
  required: {
    type: Boolean,
    default: true,
  },
  id: {
    type: String,
    default: 'password',
  },
})

defineEmits(['update:modelValue'])

const showPassword = ref(false)

const togglePassword = () => {
  showPassword.value = !showPassword.value
}
</script>
