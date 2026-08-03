<template>
  <div>
    <BaseLabel className="text-[13px] font-medium">Payment Method</BaseLabel>

    <div class="flex gap-2" :class="disabled ? 'opacity-50 ' : ''">
      <!-- Cash -->
      <div
        @click="update('cash')"
        :class="[
          `flex flex-1 items-center justify-center gap-2 rounded-md border px-4 py-2.5 transition-all ${disabled ? 'cursor-not-allowed' : 'cursor-pointer'}`,
          modelValue === 'cash'
            ? 'border-green-500 bg-green-50'
            : 'border-gray-200 bg-white hover:border-gray-300',
        ]"
      >
        <div :class="['rounded-full p-1', modelValue === 'cash' ? 'bg-green-600' : 'bg-gray-400']">
          <svg class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
            />
          </svg>
        </div>

        <span
          :class="[
            'text-xs font-semibold',
            modelValue === 'cash' ? 'text-green-700' : 'text-gray-600',
          ]"
        >
          Cash
        </span>
      </div>

      <!-- Bank -->
      <div
        @click="update('bank')"
        :class="[
          `flex flex-1 items-center justify-center gap-2 rounded-md border px-4 py-2.5 transition-all ${disabled ? 'cursor-not-allowed' : 'cursor-pointer'}`,
          modelValue === 'bank'
            ? 'border-blue-500 bg-blue-50'
            : 'border-gray-200 bg-white hover:border-gray-300',
        ]"
      >
        <div :class="['rounded-full p-1', modelValue === 'bank' ? 'bg-blue-600' : 'bg-gray-400']">
          <svg class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
            />
          </svg>
        </div>

        <span
          :class="[
            'text-xs font-semibold',
            modelValue === 'bank' ? 'text-blue-700' : 'text-gray-600',
          ]"
        >
          Bank
        </span>
      </div>

      <!-- Bkash -->
      <div
        @click="update('bkash')"
        :class="[
          `flex flex-1 items-center justify-center gap-2 rounded-md border px-4 py-2.5 transition-all ${disabled ? 'cursor-not-allowed' : 'cursor-pointer'}`,
          modelValue === 'bkash'
            ? 'border-pink-500 bg-pink-50'
            : 'border-gray-200 bg-white hover:border-gray-300',
        ]"
      >
        <div :class="['rounded-full p-1', modelValue === 'bkash' ? 'bg-pink-600' : 'bg-gray-400']">
          <svg class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
            />
          </svg>
        </div>

        <span
          :class="[
            'text-xs font-semibold',
            modelValue === 'bkash' ? 'text-pink-700' : 'text-gray-600',
          ]"
        >
          Bkash
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: String,
    default: 'cash',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue'])

const update = (value) => {
  if (props.disabled) return
  emit('update:modelValue', value)
}
</script>
