<template>
  <BaseForm :onSubmit="onSubmit">
    <!-- Total Amount -->
    <div class="space-y-2">
      <BaseLabel for="total_amount">Total Amount</BaseLabel>
      <BaseInput
        id="total_amount"
        v-model="localForm.total_amount"
        placeholder="Eg: 10000"
        type="number"
        min="0"
      />
    </div>
    <!-- Total Amount USD -->
    <div class="space-y-2">
      <BaseLabel for="total_amount_usd">Total Amount (USD)</BaseLabel>
      <BaseInput
        id="total_amount_usd"
        v-model="localForm.total_amount_usd"
        placeholder="Eg: 100"
        type="number"
        min="0"
      />
    </div>
    <!-- Paid Amount -->
    <div class="space-y-2">
      <BaseLabel for="paid_amount">Paid Amount</BaseLabel>
      <BaseInput
        id="paid_amount"
        v-model="localForm.paid_amount"
        placeholder="Eg: 1234567890"
        :required="true"
      />
    </div>
    <!-- Paid Amount USD -->
    <div class="space-y-2">
      <BaseLabel for="paid_amount_usd">Paid Amount (USD)</BaseLabel>
      <BaseInput
        id="paid_amount_usd"
        v-model="localForm.paid_amount_usd"
        placeholder="Eg: 50"
        type="number"
        min="0"
      />
    </div>
    <div class="space-y-2">
      <BaseLabel for="payment_method">Payment Method</BaseLabel>
      <BaseInput
        id="payment_method"
        v-model="localForm.payment_method"
        placeholder="Eg: Cash, Card, etc."
        :required="true"
      />
    </div>
    <div class="space-y-2">
      <BaseLabel for="payment_date">Payment Date</BaseLabel>
      <BaseInput
        id="payment_date"
        v-model="localForm.payment_date"
        placeholder="Eg: 2023-12-31"
        :required="true"
      />
    </div>
    <div class="space-y-2">
      <BaseLabel for="payment_time">Payment Time</BaseLabel>
      <BaseInput
        id="payment_time"
        v-model="localForm.payment_time"
        placeholder="Eg: 12:00 PM"
        :required="true"
      />
    </div>
    <div class="space-y-2">
      <BaseLabel for="remarks">Remarks</BaseLabel>
      <BaseInput
        id="remarks"
        v-model="localForm.remarks"
        placeholder="Eg: Payment received"
        :required="true"
      />
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-2 pt-4">
      <BaseButton class="bg-yellow-600 hover:bg-yellow-700" type="button" @click="onCancel">Cancel</BaseButton>
      <BaseButton type="submit" :loading="props.loading">
        <span v-if="props.loading">Saving...</span>
        <span v-else>Save</span>
      </BaseButton>
    </div>
  </BaseForm>
</template>

<script setup>
import { ref, watch } from 'vue'

// Props
const props = defineProps({
  formData: { type: Object, required: true },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, default: false },
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
  { deep: true }
)
</script>
