<template>
  <div class="mt-3 space-y-3 border-t border-indigo-50 pt-3">
    <div>
      <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-600/80">
        {{ t('journals.manager_comment_presets_approve') }}
      </p>
      <div class="mt-2 flex flex-wrap gap-2">
        <button
          v-for="preset in approvalPresets"
          :key="preset.id"
          type="button"
          class="rounded-lg border px-2.5 py-1.5 text-left text-xs leading-relaxed transition"
          :class="
            selectedId === preset.id
              ? 'border-indigo-300 bg-indigo-50 text-indigo-900 ring-1 ring-indigo-200'
              : 'border-slate-200 bg-white text-slate-700 hover:border-indigo-200 hover:bg-indigo-50/60 hover:text-indigo-900'
          "
          :title="t(preset.textKey)"
          @click="applyPreset(preset)"
        >
          {{ t(preset.labelKey) }}
        </button>
      </div>
    </div>

    <div>
      <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-600/80">
        {{ t('journals.manager_comment_presets_return') }}
      </p>
      <div class="mt-2 flex flex-wrap gap-2">
        <button
          v-for="preset in returnPresets"
          :key="preset.id"
          type="button"
          class="rounded-lg border px-2.5 py-1.5 text-left text-xs leading-relaxed transition"
          :class="
            selectedId === preset.id
              ? 'border-rose-300 bg-rose-50 text-rose-900 ring-1 ring-rose-200'
              : 'border-slate-200 bg-white text-slate-700 hover:border-rose-200 hover:bg-rose-50/60 hover:text-rose-900'
          "
          :title="t(preset.textKey)"
          @click="applyPreset(preset)"
        >
          {{ t(preset.labelKey) }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import {
  managerApprovalCommentPresets,
  managerReturnCommentPresets,
} from '../../data/managerCommentPresets'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['update:modelValue'])

const { t } = useTranslate()

const approvalPresets = managerApprovalCommentPresets
const returnPresets = managerReturnCommentPresets
const selectedId = ref('')

watch(
  () => props.modelValue,
  (value) => {
    const match = [...approvalPresets, ...returnPresets].find(
      (preset) => t(preset.textKey) === String(value || '').trim(),
    )
    selectedId.value = match?.id || ''
  },
  { immediate: true },
)

function applyPreset(preset) {
  selectedId.value = preset.id
  emit('update:modelValue', t(preset.textKey))
}
</script>
