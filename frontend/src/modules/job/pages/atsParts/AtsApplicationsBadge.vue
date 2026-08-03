<template>
  <div
    class="inline-flex items-center gap-2 rounded-xl border-2 border-primary bg-primary-light px-3 py-1.5 shadow-sm ring-2 ring-primary/20"
  >
    <span class="text-xs font-bold uppercase tracking-wide text-primary">{{t('ats.ats_applications')}}</span>
    <span
      class="inline-flex min-h-7 min-w-7 items-center justify-center rounded-full bg-primary px-2.5 text-sm font-bold text-white shadow-sm"
    >
      {{ count }}
    </span>
  </div>
</template>

<script setup>
import { useTranslate } from '@/shared/composables/useTranslate.js'

const { t } = useTranslate()
defineProps({
  count: {
    type: [String, Number],
    default: 0,
  },
})
</script>
