<template>
  <div
    class="flex h-9 w-9 items-center justify-center rounded-full text-white"
    :class="!props.image ? avatarClass : ''"
  >
    <span v-if="!props.image" class="text-sm font-medium">
      {{ initials }}
    </span>
    <img v-else :src="logoUrl" alt="User Avatar" class="h-9 w-9 rounded-full object-cover" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { getUserInitials, getUserTypeClasses } from '@/shared/helpers/userHelpers'

const props = defineProps({
  name: String,
  type: String,
  image: String,
})

const logoUrl = computed(() => props.image || '/norqel-logo.svg')

const initials = computed(() => getUserInitials(props.name))
const avatarClass = computed(() => getUserTypeClasses(props.type).avatar)
</script>
