<template>
  <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-4 p-3 sm:p-4">
    <!-- Showing records -->
    <div class="order-3 sm:order-1 text-center sm:text-left">
        <p class="text-sm sm:text-base">{{ t('shared.messages.showing_records', { showing, total }) }}</p>
      </div>

    <!-- Pagination buttons -->
    <div class="flex flex-wrap gap-1 justify-center order-1 sm:order-2">
      <button
        v-for="(link, index) in localizedLinks"
        :key="index"
        @click="handleLinkClick(link)"
        :disabled="!link?.url || link?.active"
        :class="[
          'px-2 sm:px-3 py-1 rounded border text-xs sm:text-sm transition-colors min-w-[32px] sm:min-w-[36px]',
          link?.active
            ? 'bg-primary text-white border-primary cursor-default'
            : link?.url
            ? 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 cursor-pointer'
            : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
        ]"
        v-html="link?.label"
      />
    </div>
    <!-- Per page -->
    <div class="flex items-center gap-2 order-2 sm:order-3 justify-center sm:justify-end w-full sm:w-auto">
      <label for="perPage" class="text-sm sm:text-base whitespace-nowrap">{{ t('shared.messages.per_page') }}</label>
      <select
        id="perPage"
        class="border-gray-700 rounded-md shadow-sm px-2 sm:px-3 py-1 text-sm sm:text-base w-20 sm:w-auto"
        :value="perPage"
        @change="changePerPage"
      >
        <option v-for="size in perPageOptions" :key="size" :value="size">{{ size }}</option>
      </select>
    </div>
  </div>
</template>

<script setup>
 const props = defineProps({
  total: Number,
  showing: Number,
  links: {
    type: Array,
    required: true,
  },
  perPage: Number,
  perPageOptions: {
    type: Array,
    default: () => [10, 25, 50, 100],
  },
})
import { useTranslate } from '@/shared/composables/useTranslate'
import { computed } from 'vue'

const { t } = useTranslate()

const localizedLinks = computed(() =>
  props.links.map(link => ({
    ...link,
    label: link.label
      .replace('Previous', t('shared.pagination.previous'))
      .replace('Next', t('shared.pagination.next')),
  }))
)

const emit = defineEmits(['update:page', 'update:perPage'])

function handleLinkClick(link) {
  if (link?.active) return

  const page = extractPageNumber(link?.url) ?? parseLabelPage(link?.label)
  if (page) {
    emit('update:page', page)
  }
}

function parseLabelPage(label) {
  const text = String(label ?? '').trim()
  return /^\d+$/.test(text) ? Number(text) : null
}

function extractPageNumber(url) {
  if (!url || url === '#') return null

  try {
    const parsed = new URL(url, window.location.origin)
    const page = parsed.searchParams.get('page')
    return page ? Number(page) : null
  } catch {
    return null
  }
}

function changePerPage(event) {
  emit('update:perPage', Number(event.target.value))
}
</script>
