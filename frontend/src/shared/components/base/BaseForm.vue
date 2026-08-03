<template>
  <form
    @submit.prevent="handleSubmit"
    :class="['space-y-4', className]"
  >
    <slot />
  </form>
</template>

<script setup>
const props = defineProps({
  onSubmit: {
    type: Function,
    required: true,
  },
  className: {
    type: String,
    default: '',
  },
})

const handleSubmit = (event) => {
  props.onSubmit(event)
}
</script>
