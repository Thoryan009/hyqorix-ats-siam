<template>
  <div>
    <div v-if="items.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3">
    <div
      v-for="(item, index) in items"
      :key="item.key || `${item.src}-${index}`"
      class="group relative aspect-[4/3] overflow-hidden rounded-lg border border-slate-200 bg-slate-100 shadow-sm"
    >
      <button
        type="button"
        class="absolute inset-0 z-0 flex h-full w-full cursor-zoom-in items-center justify-center focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
        :aria-label="t('journals.receipt_click_to_preview')"
        @click="openPreview(index)"
      >
        <img
          :src="item.src"
          :alt="item.alt || `${t('journals.receipt')} ${index + 1}`"
          class="h-full w-full object-cover transition duration-200 group-hover:scale-[1.03]"
        />
      </button>

      <div
        class="pointer-events-none absolute inset-0 flex items-center justify-center bg-slate-900/0 transition duration-200 group-hover:bg-slate-900/25"
      >
        <span
          class="rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-medium text-slate-700 opacity-0 shadow-sm transition duration-200 group-hover:opacity-100"
        >
          <i class="fa fa-search-plus mr-1"></i>
          {{ t('journals.receipt_click_to_preview') }}
        </span>
      </div>

      <button
        v-if="item.removable"
        type="button"
        class="absolute top-1.5 right-1.5 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-slate-900/80 text-white transition hover:bg-slate-900"
        :aria-label="t('journals.delete')"
        @click.stop="emit('remove', item.removeIndex ?? index)"
      >
        <i class="fa fa-times text-xs"></i>
      </button>

      <button
        type="button"
        class="absolute bottom-1.5 right-1.5 z-10 flex h-7 w-7 items-center justify-center rounded-full bg-white/95 text-slate-700 shadow-sm transition hover:bg-white hover:text-primary"
        :aria-label="t('journals.receipt_download')"
        @click.stop="downloadItem(item, index)"
      >
        <i class="fa fa-download text-xs"></i>
      </button>
    </div>
    </div>

    <Teleport to="body">
    <div
      v-if="activeIndex !== null"
      class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/85 p-4 backdrop-blur-sm"
      role="dialog"
      aria-modal="true"
      :aria-label="t('journals.receipt_preview')"
      @click.self="closePreview"
      @keydown.escape="closePreview"
    >
      <div
        class="relative flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
        @click.stop
      >
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 sm:px-5">
          <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-slate-900">
              {{ t('journals.receipt_preview') }}
            </p>
            <p v-if="items.length > 1" class="text-xs text-slate-500">
              {{ activeIndex + 1 }} / {{ items.length }}
            </p>
          </div>

          <div class="flex shrink-0 items-center gap-2">
            <button
              type="button"
              class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
              @click="downloadActiveItem"
            >
              <i class="fa fa-download text-xs"></i>
              {{ t('journals.receipt_download') }}
            </button>
            <button
              type="button"
              class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
              :aria-label="t('journals.close')"
              @click="closePreview"
            >
              &times;
            </button>
          </div>
        </div>

        <div class="relative flex min-h-[240px] flex-1 items-center justify-center bg-slate-950/5 p-4 sm:p-6">
          <button
            v-if="items.length > 1"
            type="button"
            class="absolute left-2 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/95 text-slate-700 shadow-md transition hover:bg-white sm:left-4"
            :aria-label="t('journals.receipt_previous')"
            @click="showPrevious"
          >
            <i class="fa fa-chevron-left"></i>
          </button>

          <img
            v-if="activeItem"
            :src="activeItem.src"
            :alt="activeItem.alt || `${t('journals.receipt')} ${activeIndex + 1}`"
            class="max-h-[70vh] max-w-full rounded-lg object-contain shadow-lg"
          />

          <button
            v-if="items.length > 1"
            type="button"
            class="absolute right-2 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/95 text-slate-700 shadow-md transition hover:bg-white sm:right-4"
            :aria-label="t('journals.receipt_next')"
            @click="showNext"
          >
            <i class="fa fa-chevron-right"></i>
          </button>
        </div>

        <div
          v-if="items.length > 1"
          class="flex gap-2 overflow-x-auto border-t border-slate-200 bg-slate-50 px-4 py-3"
        >
          <button
            v-for="(item, index) in items"
            :key="`thumb-${item.key || index}`"
            type="button"
            class="h-14 w-20 shrink-0 overflow-hidden rounded-md border-2 transition"
            :class="
              index === activeIndex
                ? 'border-primary ring-2 ring-primary/20'
                : 'border-transparent opacity-70 hover:opacity-100'
            "
            @click="activeIndex = index"
          >
            <img
              :src="item.src"
              :alt="item.alt || `${t('journals.receipt')} ${index + 1}`"
              class="h-full w-full object-cover"
            />
          </button>
        </div>
      </div>
    </div>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import { downloadFile } from '@/shared/utils/download'

const props = defineProps({
  items: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['remove'])

const { t } = useTranslate()

const activeIndex = ref(null)

const activeItem = computed(() =>
  activeIndex.value === null ? null : props.items[activeIndex.value] ?? null,
)

function resolveDownloadName(item, index) {
  if (item?.downloadName) return item.downloadName
  if (item?.file instanceof File && item.file.name) return item.file.name

  const src = String(item?.src || '')
  if (src.startsWith('blob:')) {
    return `receipt-${index + 1}.jpg`
  }

  const fromUrl = src.split('/').pop()?.split('?')[0]
  if (fromUrl) return fromUrl

  return `receipt-${index + 1}.jpg`
}

async function downloadItem(item, index) {
  const name = resolveDownloadName(item, index)

  if (item?.file instanceof File) {
    const blobUrl = URL.createObjectURL(item.file)
    const link = document.createElement('a')
    link.href = blobUrl
    link.download = name
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(blobUrl)
    return
  }

  await downloadFile(item.src, name)
}

function downloadActiveItem() {
  if (activeIndex.value === null || !activeItem.value) return
  downloadItem(activeItem.value, activeIndex.value)
}

function openPreview(index) {
  activeIndex.value = index
  document.body.style.overflow = 'hidden'
}

function closePreview() {
  activeIndex.value = null
  document.body.style.overflow = ''
}

function showPrevious() {
  if (activeIndex.value === null || props.items.length <= 1) return
  activeIndex.value =
    activeIndex.value === 0 ? props.items.length - 1 : activeIndex.value - 1
}

function showNext() {
  if (activeIndex.value === null || props.items.length <= 1) return
  activeIndex.value =
    activeIndex.value === props.items.length - 1 ? 0 : activeIndex.value + 1
}

function onKeydown(event) {
  if (activeIndex.value === null) return

  if (event.key === 'Escape') {
    closePreview()
  } else if (event.key === 'ArrowLeft') {
    showPrevious()
  } else if (event.key === 'ArrowRight') {
    showNext()
  }
}

watch(activeIndex, (value) => {
  if (value !== null) {
    window.addEventListener('keydown', onKeydown)
    return
  }
  window.removeEventListener('keydown', onKeydown)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeydown)
  document.body.style.overflow = ''
})
</script>
