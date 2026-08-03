<template>
  <BaseForm :onSubmit="onSubmit">
    <div class="space-y-2">
      <BaseLabel for="category_description">Description</BaseLabel>
      <BaseInput
        id="category_description"
        v-model="localForm.description"
        placeholder="Enter description (optional)"
      />
    </div>

    <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>

    <div class="flex justify-end gap-2 pt-4">
      <BaseButton class="bg-gray-500 text-white hover:bg-gray-600" type="button" @click="onCancel">
        Cancel
      </BaseButton>
      <BaseButton type="submit" :disabled="loading">
        <span v-if="loading">Saving...</span>
        <span v-else>Save</span>
      </BaseButton>
    </div>
  </BaseForm>
</template>

<script setup>
import { reactive, watch } from 'vue'
import BaseForm from '@/shared/components/base/BaseForm.vue'

const props = defineProps({
  formData: { type: Object, required: true },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, default: false },
  errorMessage: { type: String, default: '' },
})

const emit = defineEmits(['update:formData'])

const localForm = reactive({ ...props.formData })

watch(
  () => props.formData,
  (value) => {
    Object.assign(localForm, value)
  },
  { deep: true }
)

watch(
  localForm,
  (value) => {
    emit('update:formData', { ...value })
  },
  { deep: true }
)
</script>
