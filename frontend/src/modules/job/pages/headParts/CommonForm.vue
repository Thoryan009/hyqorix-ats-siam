<template>
  <BaseForm :onSubmit="onSubmit">
    <!-- Name -->
    <div class="space-y-2">
      <BaseLabel for="name">{{t('shared.labels.name')}}</BaseLabel>
      <BaseInput id="name" v-model="localForm.name" :required="true" placeholder="Eg: Rabit Ltd" />
    </div>

    <!-- Amount -->
    <div class="space-y-2">
      <BaseLabel for="amount">{{t('job_price.amount')}}</BaseLabel>
      <BaseInput
        id="amount"
        type="number"
        v-model="localForm.amount"
        :required="true"
        placeholder="Eg: 1000"
      />
    </div>

    <!-- Amount USD-->
    <div class="space-y-2">
      <BaseLabel for="amount_usd">{{t('job_price.amount_usd')}}</BaseLabel>
      <BaseInput
        id="amount_usd"
        type="number"
        v-model="localForm.amount_usd"
        :required="true"
        placeholder="Eg: 1000"
      />
    </div>

    <!-- Country -->
    <div class="space-y-2">
      <BaseLabel for="category_name">{{t('job_price.head_category')}}</BaseLabel>
      <BaseSelect
        id="category_name"
        v-model="localForm.job_list_details_category_id"
        :options="categories"
        placeholder="Select"
        :required="true"
      />
    </div>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row justify-end gap-2 pt-4">
      <BaseButton
        class="bg-yellow-600 hover:bg-yellow-700 w-full sm:w-auto"
        type="button"
        @click="onCancel"
      >{{t('shared.actions.cancel')}}</BaseButton>
      <BaseButton class="w-full sm:w-auto" type="submit" :disabled="loading">
        <span v-if="loading">{{t('shared.messages.saving')}}</span>
        <span v-else>{{t('shared.actions.save')}}</span>
      </BaseButton>
    </div>
  </BaseForm>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'
const { t } = useTranslate()
// Props
const props = defineProps({
  formData: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
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
