<template>
  <div class="mt-3 space-y-2.5 border-t border-slate-100 pt-3">
    <div class="flex items-center justify-between gap-2">
      <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
        {{ t('journals.narration_hints') }}
      </p>
      <button
        type="button"
        class="inline-flex items-center gap-1.5 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700 transition hover:bg-indigo-100 disabled:cursor-not-allowed disabled:opacity-50"
        :disabled="!canSuggest || isPending"
        @click="loadHints({ force: true })"
      >
        <i class="fa" :class="isPending ? 'fa-spinner fa-spin' : 'fa-refresh'"></i>
        <span>{{ isPending ? t('journals.narration_hints_loading') : t('journals.narration_hints_refresh') }}</span>
      </button>
    </div>

    <p v-if="errorMessage" class="text-xs text-red-600">{{ errorMessage }}</p>

    <p
      v-else-if="isPending && !hints.length"
      class="rounded-lg border border-dashed border-indigo-200 bg-indigo-50/40 px-3 py-2.5 text-xs leading-relaxed text-indigo-700"
    >
      <i class="fa fa-spinner fa-spin mr-1.5"></i>
      {{ t('journals.narration_hints_waiting') }}
    </p>

    <p
      v-else-if="!hints.length"
      class="rounded-lg border border-dashed border-slate-200 bg-slate-50/80 px-3 py-2.5 text-xs leading-relaxed text-slate-500"
    >
      {{ canSuggest ? t('journals.narration_hints_pending') : t('journals.narration_hints_empty') }}
    </p>

    <div v-else class="flex flex-wrap gap-2">
      <button
        v-for="(hint, index) in hints"
        :key="`${index}-${hint}`"
        type="button"
        class="group max-w-full rounded-lg border px-2.5 py-1.5 text-left text-xs leading-relaxed transition"
        :class="
          selectedHint === hint
            ? 'border-indigo-300 bg-indigo-50 text-indigo-900 ring-1 ring-indigo-200'
            : 'border-slate-200 bg-white text-slate-700 hover:border-indigo-200 hover:bg-indigo-50/60 hover:text-indigo-900'
        "
        :title="t('journals.narration_hints_apply')"
        @click="applyHint(hint)"
      >
        <span class="line-clamp-2">{{ hint }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import { useJournalNarrationHints } from '../../queries/useJournalNarrationHints'

const AUTO_FETCH_DELAY_MS = 900

const props = defineProps({
  context: {
    type: Object,
    required: true,
  },
  modelValue: {
    type: String,
    default: '',
  },
  canSuggest: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue'])

const { t } = useTranslate()
const { mutateAsync, isPending } = useJournalNarrationHints()

const hints = ref([])
const errorMessage = ref('')
const selectedHint = ref('')
const lastFetchedContextKey = ref('')

const contextKey = computed(() => JSON.stringify(props.context ?? {}))

let debounceTimer = null

watch(
  () => props.modelValue,
  (value) => {
    if (value !== selectedHint.value) {
      selectedHint.value = ''
    }
  },
)

watch(
  () => [props.canSuggest, contextKey.value],
  ([canSuggest]) => {
    hints.value = []
    errorMessage.value = ''
    lastFetchedContextKey.value = ''

    if (!canSuggest) {
      clearTimeout(debounceTimer)
      return
    }

    scheduleAutoFetch()
  },
  { immediate: true },
)

onBeforeUnmount(() => {
  clearTimeout(debounceTimer)
})

function scheduleAutoFetch() {
  clearTimeout(debounceTimer)

  if (!props.canSuggest) return

  debounceTimer = setTimeout(() => {
    loadHints()
  }, AUTO_FETCH_DELAY_MS)
}

async function loadHints({ force = false } = {}) {
  if (!props.canSuggest || isPending.value) return

  const key = contextKey.value
  if (!force && key === lastFetchedContextKey.value) return

  errorMessage.value = ''

  try {
    const result = await mutateAsync(props.context)
    hints.value = Array.isArray(result) ? result.filter(Boolean) : []
    lastFetchedContextKey.value = key

    if (!hints.value.length) {
      errorMessage.value = t('journals.narration_hints_none')
    }
  } catch (error) {
    hints.value = []
    lastFetchedContextKey.value = ''
    errorMessage.value =
      error?.message || error?.errors?.lines?.[0] || t('journals.narration_hints_failed')
  }
}

function applyHint(hint) {
  selectedHint.value = hint
  emit('update:modelValue', hint)
}
</script>
