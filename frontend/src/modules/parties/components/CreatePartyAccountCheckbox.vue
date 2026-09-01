<template>
  <div v-if="showField" class="space-y-2">
    <label class="flex items-center gap-2 cursor-pointer">
      <input
        type="checkbox"
        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
        :checked="isChecked"
        @change="onChange"
      />
      <span class="text-sm font-medium text-gray-700">{{ t('shared.labels.create_party_account') }}</span>
    </label>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'
import { usePartyTypeMappingOptionsQuery } from '@/modules/setting/queries/usePartyTypeMappingQuery'

const props = defineProps({
  modelValue: {
    type: [Number, String, Boolean],
    default: undefined,
  },
  sourceModule: {
    type: String,
    required: true,
  },
  defaultChecked: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['update:modelValue'])

const { t } = useTranslate()
const { data: partyTypeOptions } = usePartyTypeMappingOptionsQuery()

const showField = computed(() =>
  (partyTypeOptions.value ?? []).some((item) => item.source_module === props.sourceModule),
)

const isExplicitlyUnchecked = (value) => [0, '0', false].includes(value)
const isExplicitlyChecked = (value) => [1, '1', true].includes(value)

const isChecked = computed(() => {
  if (isExplicitlyUnchecked(props.modelValue)) return false
  if (isExplicitlyChecked(props.modelValue)) return true
  return props.defaultChecked
})

watch(
  () => props.modelValue,
  (value) => {
    if (isExplicitlyChecked(value) || isExplicitlyUnchecked(value)) {
      return
    }

    emit('update:modelValue', props.defaultChecked ? 1 : 0)
  },
  { immediate: true },
)

const onChange = (event) => {
  emit('update:modelValue', event.target.checked ? 1 : 0)
}
</script>
