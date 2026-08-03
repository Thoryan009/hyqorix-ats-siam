<template>
  <BaseForm :onSubmit="onSubmit">
    <!-- Fee Name -->
    <div class="space-y-2">
      <BaseLabel for="fee_name">{{t('job_price.fee_name')}}</BaseLabel>
      <BaseSelect
        id="fee_name"
        v-model="localForm.job_list_details_head_id"
        :options="fees"
        placeholder="Select"
        :required="true"
      />
    </div>
    <!-- Amount -->
    <div class="space-y-2">
      <BaseLabel for="amount">{{t('job_price.amount')}}</BaseLabel>
      <BaseInput
        id="amount"
        type="number"
        v-model="localForm.amount"
        :required="true"
        placeholder="Eg: 1500"
      />
    </div>
    <!-- Amount USD -->
    <div class="space-y-2">
      <BaseLabel for="amount_usd">{{t('job_price.amount_usd')}}</BaseLabel>
      <BaseInput
        id="amount_usd"
        type="number"
        v-model="localForm.amount_usd"
        :required="true"
        placeholder="Eg: 30"
      />
    </div>
    <!-- Actions -->
    <div class="flex flex-col sm:flex-row justify-end gap-2 pt-4">
      <BaseButton
        class="bg-yellow-600 hover:bg-yellow-700 w-full sm:w-auto"
        type="button"
        @click="onCancel"
      >{{ t('shared.actions.cancel') }}</BaseButton>
      <BaseButton
        class="w-full sm:w-auto"
        :disabled="!localForm.job_list_details_head_id || loading"
        type="submit"
      >
        <span v-if="loading">{{ t('shared.messages.saving') }}</span>
        <span v-else>{{ t('shared.actions.save') }}</span>
      </BaseButton>
    </div>
  </BaseForm>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('job')
// Props
const props = defineProps({
  formData: { type: Object, required: true },
  fees: { type: Array, default: () => [] },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, default: false },
})

// Emit updates
const emit = defineEmits(['update:formData'])

// Local reactive copy
const localForm = ref({ ...props.formData })

//  2️⃣ Child → Parent Sync
watch(
  localForm,
  (val) => {
    emit('update:formData', val)
  },
  { deep: true }
)

//  3️⃣ Auto-fill Amount on Fee Select
watch(
  () => localForm.value.job_list_details_head_id,
  (newId) => {
    if (!newId) return

    // Find selected fee
    const selectedFee = props.fees.find((fee) => fee.id == newId)

    if (selectedFee) {
      localForm.value.amount = selectedFee.amount ?? 0
      localForm.value.amount_usd = selectedFee.amount_usd ?? 0
    }
  }
)
</script>
