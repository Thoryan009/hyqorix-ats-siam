<template>
  <Teleport to="body">
    <Transition name="gp-preview">
      <div
        v-if="isVisible"
        class="fixed inset-0 z-[90] flex items-stretch justify-center bg-slate-950/75"
        role="dialog"
        aria-modal="true"
        aria-labelledby="gross-profit-breakdown-preview-title"
      >
        <div
          class="gp-preview-panel flex h-full w-full max-w-none flex-col overflow-hidden bg-white shadow-2xl"
        >
          <header
            class="flex shrink-0 items-center justify-between gap-3 border-b border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-4 py-3 text-white sm:px-6"
          >
            <div class="min-w-0">
              <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-300">
                {{ t('accounts.gross_profit') }}
              </p>
              <h2 id="gross-profit-breakdown-preview-title" class="truncate text-lg font-semibold sm:text-xl">
                {{ t('accounts.gross_profit_breakdown') }}
              </h2>
              <p class="mt-0.5 truncate text-xs text-slate-300">
                {{ filename || t('accounts.gross_profit_breakdown_preview') }}
              </p>
            </div>

            <div class="flex shrink-0 items-center gap-2">
              <button
                type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-white/10 px-3 py-2 text-sm font-medium text-white ring-1 ring-white/15 transition hover:bg-white/20 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="!pdfUrl || loading"
                @click="$emit('download')"
              >
                <i class="fa fa-download"></i>
                <span class="hidden sm:inline">{{ t('accounts.download') }}</span>
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-500 px-3 py-2 text-sm font-medium text-white transition hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="!pdfUrl || loading"
                @click="$emit('print')"
              >
                <i class="fa fa-print"></i>
                <span class="hidden sm:inline">{{ t('accounts.print') }}</span>
              </button>
              <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-white/10 text-xl text-white ring-1 ring-white/15 transition hover:bg-red-500/80"
                :aria-label="t('accounts.close')"
                @click="close"
              >
                &times;
              </button>
            </div>
          </header>

          <div class="relative min-h-0 flex-1 bg-slate-200">
            <div
              v-if="loading"
              class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-slate-50 px-6"
            >
              <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
                <div class="mb-5 flex items-center gap-4">
                  <div
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-600 text-white shadow-md"
                  >
                    <i class="fa fa-file-pdf-o text-2xl"></i>
                  </div>
                  <div class="min-w-0">
                    <p class="text-lg font-semibold text-slate-900">{{ t('accounts.generating_preview') }}</p>
                    <p class="mt-0.5 text-sm text-slate-500">{{ statusText }}</p>
                  </div>
                </div>

                <div class="mb-2 flex items-center justify-between text-sm font-medium text-slate-700">
                  <span>{{ t('accounts.progress') }}</span>
                  <span class="tabular-nums text-emerald-600">{{ displayProgress }}%</span>
                </div>
                <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                  <div
                    class="h-full rounded-full bg-emerald-500 transition-all duration-300 ease-out"
                    :style="{ width: `${displayProgress}%` }"
                  ></div>
                </div>
                <p class="mt-3 text-center text-xs text-slate-400">
                  {{ t('accounts.please_wait_preview') }}
                </p>
              </div>
            </div>

            <div
              v-else-if="error"
              class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-3 px-6 text-center"
            >
              <div
                class="flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-red-600"
              >
                <i class="fa fa-exclamation-triangle text-xl"></i>
              </div>
              <p class="text-base font-semibold text-slate-900">{{ t('accounts.preview_load_failed') }}</p>
              <p class="max-w-md text-sm text-slate-500">{{ error }}</p>
              <button
                type="button"
                class="mt-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                @click="$emit('retry')"
              >
                {{ t('accounts.try_again') }}
              </button>
            </div>

            <iframe
              v-if="pdfUrl"
              :src="iframeSrc"
              class="h-full w-full border-0 bg-slate-200"
              :title="t('accounts.gross_profit_breakdown')"
              @load="$emit('ready')"
            ></iframe>
          </div>

          <footer
            class="flex shrink-0 items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-2 text-xs text-slate-500 sm:px-6"
          >
            <span>{{ t('accounts.gross_profit_breakdown_footer') }}</span>
            <span class="hidden sm:inline">{{ t('accounts.press_esc_to_close') }}</span>
          </footer>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, onMounted, onUnmounted, watch } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'

const props = defineProps({
  isVisible: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  error: { type: String, default: '' },
  pdfUrl: { type: String, default: '' },
  filename: { type: String, default: '' },
  progress: { type: Number, default: 0 },
  statusText: { type: String, default: '' },
})

const emit = defineEmits(['close', 'download', 'print', 'retry', 'ready'])

const { t } = useTranslate()

const displayProgress = computed(() => Math.min(100, Math.max(0, Number(props.progress) || 0)))

const iframeSrc = computed(() =>
  props.pdfUrl ? `${props.pdfUrl}#toolbar=1&navpanes=0` : '',
)

function close() {
  emit('close')
}

function onKeydown(event) {
  if (event.key === 'Escape' && props.isVisible) {
    close()
  }
}

watch(
  () => props.isVisible,
  (visible) => {
    document.body.style.overflow = visible ? 'hidden' : ''
  },
)

onMounted(() => {
  window.addEventListener('keydown', onKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', onKeydown)
  document.body.style.overflow = ''
})
</script>

<style scoped>
.gp-preview-enter-active,
.gp-preview-leave-active {
  transition: opacity 0.2s ease;
}

.gp-preview-enter-active .gp-preview-panel,
.gp-preview-leave-active .gp-preview-panel {
  transition: transform 0.22s ease;
}

.gp-preview-enter-from,
.gp-preview-leave-to {
  opacity: 0;
}

.gp-preview-enter-from .gp-preview-panel,
.gp-preview-leave-to .gp-preview-panel {
  transform: translateY(10px);
}
</style>
