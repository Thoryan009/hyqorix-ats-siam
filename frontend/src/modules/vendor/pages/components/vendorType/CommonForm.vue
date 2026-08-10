<template>
  <BaseForm :onSubmit="onSubmit">
    <div class="space-y-2">
      <BaseLabel for="name">{{ t('vendor.vendor_type') }} {{ t('shared.labels.name') }}</BaseLabel>
      <BaseInput
        id="name"
        v-model="localForm.name"
        :placeholder="t('vendor.placeholder_vendor_type_name')"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="status">{{ t('shared.labels.status') }}</BaseLabel>
      <BaseSelect
        id="status"
        v-model="localForm.status"
        :options="statusOptions"
        placeholder="Select"
        :required="true"
      />
    </div>

    <div class="flex justify-end gap-2 pt-4">
      <BaseButton class="bg-yellow-600 hover:bg-yellow-700" type="button" @click="onCancel">
        {{ t('shared.actions.cancel') }}
      </BaseButton>
      <BaseButton type="submit" :disabled="loading">
        <span v-if="loading">{{ t('shared.actions.saving') }}</span>
        <span v-else>{{ t('shared.actions.save') }}</span>
      </BaseButton>
    </div>
  </BaseForm>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('vendor')

const statusOptions = [
  { id: 'active', name: 'Active' },
  { id: 'inactive', name: 'Inactive' },
]

const props = defineProps({
  formData: { type: Object, required: true },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, required: true },
})

const emit = defineEmits(['update:formData'])
const localForm = ref({ ...props.formData })

watch(
  localForm,
  (val) => {
    emit('update:formData', val)
  },
  { deep: true }
)
</script>
