<template>
  <BaseForm :onSubmit="onSubmit">
    <!-- Category Name -->
    <div class="space-y-2">
      <BaseLabel for="name">{{ t('job_price.category_name') }}</BaseLabel>
      <BaseInput
        id="name"
        v-model="localForm.name"
        placeholder="Eg: Recruitment Service Charge"
        :required="true"
      />
    </div>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row justify-end gap-2 pt-4">
      <BaseButton
        class="bg-yellow-600 hover:bg-yellow-700 w-full sm:w-auto"
        type="button"
        @click="$emit('cancel')"
      >{{ t('shared.actions.cancel') }}</BaseButton>

      <BaseButton class="w-full sm:w-auto" type="submit" :loading="submitLoading">
        <span v-if="submitLoading">{{ t('shared.messages.submitting') }}</span>
        <span v-else>{{ t('shared.actions.submit') }}</span>
      </BaseButton>
    </div>
  </BaseForm>
</template>

<script setup>
import { reactive, watch } from 'vue'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('job')
const props = defineProps({
  formData: { type: Object, required: true },
  onSubmit: { type: Function, required: true },
   loading: { type: Boolean, default: false },
})

const emit = defineEmits(['update:formData', 'cancel'])

const localForm = reactive({ ...props.formData })

// Sync changes back to parent
watch(
  localForm,
  (value) => {
    emit('update:formData', value)
  },
  { deep: true }
)

</script>
