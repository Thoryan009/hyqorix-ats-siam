<template>
  <BaseForm :onSubmit="onSubmit">
    <div class="space-y-2">
      <BaseLabel for="bank_name">Bank Name</BaseLabel>
      <BaseInput
        id="bank_name"
        v-model="localForm.bank_name"
        placeholder="Eg: Dutch-Bangla Bank PLC"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="swift_code">SWIFT Code</BaseLabel>
      <BaseInput
        id="swift_code"
        v-model="localForm.swift_code"
        placeholder="Eg: DBBLBDDH"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="address">Address</BaseLabel>
      <BaseInput
        id="address"
        v-model="localForm.address"
        placeholder="Eg: Motijheel C/A, Dhaka-1000"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="branch_name">Branch Name</BaseLabel>
      <BaseInput
        id="branch_name"
        v-model="localForm.branch_name"
        placeholder="Eg: Motijheel Branch"
        :required="true"
      />
    </div>

    <div class="space-y-2">
      <BaseLabel for="status">Status</BaseLabel>
      <BaseSelect
        id="status"
        v-model="localForm.status"
        :options="statusOptions"
        placeholder="Select status"
        :required="true"
      />
    </div>

    <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>

    <div class="flex justify-end gap-2 pt-4">
      <BaseButton class="bg-yellow-600 hover:bg-yellow-700" type="button" @click="onCancel">
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
import BaseForm from '@/shared/components/base/BaseForm.vue'
import { ref, watch } from 'vue'

const props = defineProps({
  formData: { type: Object, required: true },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, default: false },
  errorMessage: { type: String, default: '' },
})

const emit = defineEmits(['update:formData'])

const localForm = ref({ ...props.formData })

const statusOptions = [
  { id: 'Active', name: 'Active' },
  { id: 'Inactive', name: 'Inactive' },
]

watch(
  localForm,
  (newVal) => {
    emit('update:formData', newVal)
  },
  { deep: true },
)
</script>
