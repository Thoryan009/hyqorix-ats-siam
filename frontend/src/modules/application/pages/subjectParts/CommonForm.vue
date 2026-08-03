<template>
  <BaseForm :onSubmit="onSubmit">
    <!-- Subject Name -->
    <div class="space-y-2">
      <BaseLabel for="name">{{t('shared.labels.name')}}</BaseLabel>
      <BaseInput
        id="name"
        v-model="localForm.name"
        placeholder="Eg: Mathematics"
        :required="true"
      />
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-2 pt-4">
      <BaseButton class="bg-yellow-600 hover:bg-yellow-700" type="button" @click="onCancel">
        {{ t('shared.actions.cancel') }}
      </BaseButton>
      <BaseButton type="submit" :disabled="loading">
        <span v-if="loading">{{ t('shared.messages.saving') }}...</span>
        <span v-else>{{ t('shared.actions.save') }}</span>
      </BaseButton>
    </div>
  </BaseForm>
</template>

<script setup>
import BaseForm from '@/shared/components/base/BaseForm.vue'
import { ref, watch } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()
// Props
const props = defineProps({
  formData: { type: Object, required: true },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, required: true },
})

// Emit event to sync formData
const emit = defineEmits(['update:formData'])

// Local reactive copy
const localForm = ref({ ...props.formData })

// Sync to parent when local changes
watch(
  localForm,
  (newVal) => {
    emit('update:formData', newVal)
  },
  { deep: true },
)
</script>
