<template>
  <BaseForm :onSubmit="onSubmit">
    <!-- Name -->
    <div class="space-y-2">
      <BaseLabel for="name">{{ t('shared.labels.name') }}</BaseLabel>
      <BaseInput
        id="name"
        v-model="localForm.name"
        :required="true"
        :readonly="true"
        placeholder="Eg: Rabit Ltd"
      />
    </div>

    <!-- Duration -->
    <div class="space-y-2">
      <BaseLabel for="duration">{{ t('process.duration') }}</BaseLabel>
      <BaseInput
        id="duration"
        type="number"
        v-model="localForm.duration"
        :required="true"
        :readonly="false"
        placeholder="Eg: 1000"
      />
    </div>
    <!-- Validity -->
    <div class="space-y-2">
      <BaseLabel for="validity">{{ t('process.validity') }}</BaseLabel>
      <BaseInput
        id="validity"
        type="number"
        v-model="localForm.validity"
        :required="true"
        :readonly="false"
        placeholder="Eg: 1000"
      />
    </div>
    <!-- Notify Before -->
    <div class="space-y-2">
      <BaseLabel for="notify_before">{{ t('process.notify_before') }}</BaseLabel>
      <BaseInput
        id="notify_before"
        type="number"
        v-model="localForm.notify_before"
        :required="true"
        :readonly="false"
        placeholder="Eg: 1000"
      />
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-2 pt-4">
      <BaseButton class="bg-yellow-600 hover:bg-yellow-700" type="button" @click="onCancel">{{ t('shared.actions.cancel') }}</BaseButton>
      <BaseButton type="submit" :loading="props.loading">
        <span v-if="props.loading">{{ t('shared.messages.saving') }}</span>
        <span v-else>{{ t('shared.actions.save') }}</span>
      </BaseButton>
    </div>
  </BaseForm>
</template>

<script setup>
import { ref, watch } from 'vue'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
// Props
const props = defineProps({
  formData: { type: Object, required: true },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, default: false },
})

// Emit updates
const emit = defineEmits(['update:formData'])

// Local reactive copy
const localForm = ref({ ...props.formData })

// Sync back to parent
watch(
  localForm,
  (val) => {
    emit('update:formData', val)
  },
  { deep: true }
)
</script>
