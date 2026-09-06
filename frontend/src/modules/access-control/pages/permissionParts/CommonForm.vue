<template>
  <ScrollableLayout height="680px">
    <form @submit.prevent="onSubmit" class="space-y-5 p-1">
      <div class="rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div
          class="px-4 py-2.5 flex items-center gap-2"
          style="background: linear-gradient(90deg, #8b5cf6, #a855f7)"
        >
          <i class="fa fa-key text-white text-sm"></i>
          <span class="text-white text-sm font-semibold tracking-wide uppercase"
            >{{ t('permission.info') }}</span
          >
        </div>
        <div class="p-4 bg-white space-y-4">
          <div class="space-y-1.5">
            <label
              for="permission_name"
              class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
            >
              <i class="fa fa-tag text-violet-400"></i> {{ t('permission.name') }}
              <span class="text-red-400">*</span>
            </label>
            <BaseInput
              id="permission_name"
              v-model="localForm.name"
              :placeholder="t('permission.placeholder_name')"
              :required="true"
            />
          </div>

          <div class="space-y-1.5">
            <label
              for="permission_slug"
              class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
            >
              <i class="fa fa-link text-violet-400"></i> {{ t('permission.slug') }}
              <span class="text-red-400">*</span>
            </label>
            <BaseInput
              id="permission_slug"
              v-model="localForm.slug"
              :placeholder="t('permission.placeholder_slug')"
              :required="true"
            />
            <p class="text-xs text-gray-400">{{ t('permission.slug_hint') }}</p>
          </div>
        </div>
      </div>

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
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()

const props = defineProps({
  formData: { type: Object, required: true },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['update:formData'])

const localForm = ref({ ...props.formData })

watch(
  localForm,
  (newVal) => {
    emit('update:formData', newVal)
  },
  { deep: true }
)

watch(
  () => props.formData,
  (newVal) => {
    if (!newVal) return
    Object.assign(localForm.value, newVal)
  },
  { deep: true }
)
</script>
