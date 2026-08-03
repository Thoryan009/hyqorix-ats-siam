
<template>
  <ScrollableLayout height="680px">
    <form @submit.prevent="onSubmit" class="space-y-5 p-1">

      <!-- ── Group 1: Role Info ── -->
      <div class="rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div
          class="px-4 py-2.5 flex items-center gap-2"
          style="background: linear-gradient(90deg, #3b82f6, #6366f1)"
        >
          <i class="fa fa-shield text-white text-sm"></i>
          <span class="text-white text-sm font-semibold tracking-wide uppercase">{{ t('role.info') }}</span>
        </div>
        <div class="p-4 bg-white">
          <div class="space-y-1.5">
            <label for="name" class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">
              <i class="fa fa-tag text-blue-400"></i> {{ t('shared.labels.name') }} <span class="text-red-400">*</span>
            </label>
            <BaseInput id="name" v-model="localForm.name" placeholder="Eg: Role Name" :required="true" />
          </div>
        </div>
      </div>

      <!-- ── Actions ── -->
      <div class="flex justify-end gap-3 pt-2 pb-4">
        <BaseButton
            :className="'bg-yellow-700 hover:bg-yellow-800 text-white border border-gray-200 gap-2 cursor-pointer'"
            @click="onCancel"
          >
            <i class="fa fa-times"></i> {{ t('shared.actions.cancel') }}
          </BaseButton>
        <BaseButton type="submit" :disabled="loading" class="gap-2">
          <span v-if="loading" class="flex items-center gap-2">
            <i class="fa fa-spinner fa-spin"></i> {{ t('shared.messages.saving') }}
          </span>
          <span v-else class="flex items-center gap-2">
            <i class="fa fa-check"></i> {{ t('shared.actions.save') }}
          </span>
        </BaseButton>
      </div>
    </form>
  </ScrollableLayout>
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
