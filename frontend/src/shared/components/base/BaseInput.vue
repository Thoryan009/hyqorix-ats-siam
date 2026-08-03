<template>
  <div>
    <input
      ref="inputRef"
      :id="id"
      :type="type"
      :placeholder="placeholder"
      :step="step"
      :value="type === 'radio' ? value : modelValue"
      :checked="type === 'radio' ? modelValue == value : undefined"
      :disabled="disabled"
      :readonly="readonly"
      :required="required"
      @input="$emit('update:modelValue', $event.target.value)"
      @change="handleChange"
      :class="[
        className,
        disabled && 'opacity-50 cursor-not-allowed',
        readonly && 'bg-gray-100 cursor-not-allowed',
      ]"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const inputRef = ref(null)

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  value: {
    type: [String, Number], // ✅ important for radio
    default: '',
  },
  type: {
    type: String,
    default: 'text',
  },

  placeholder: {
    type: String,
    default: '',
  },
  id: {
    type: String,
    default: '',
  },
  step: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  required: {
    type: Boolean,
    default: false,
  },
  className: {
    type: String,
    default:
      'w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary',
  },
  readonly: {
    type: Boolean,
    default: false,
  },
})
const emit = defineEmits(['update:modelValue'])

watch(
  () => props.modelValue,
  (nextValue) => {
    if (props.type === 'radio' || !inputRef.value) return

    const normalized = nextValue == null ? '' : String(nextValue)
    if (inputRef.value.value !== normalized) {
      inputRef.value.value = normalized
    }
  }
)

const handleChange = () => {
  if (props.type === 'radio') {
    emit('update:modelValue', props.value)
  }
}
</script>
