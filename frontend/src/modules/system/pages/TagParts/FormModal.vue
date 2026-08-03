<template>
  <BaseModal
    :isVisible="store.isModal || store.isEditModal"
    :title="store.title"
    @close="store.handleToggleModal"
    :className="'w-full xl:max-w-[40vw]'"
  >
    <ScrollableLayout height="320px">
      <form @submit.prevent="handleSubmit" class="space-y-4 p-1">
        <div class="space-y-1.5">
          <label
            for="name"
            class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
          >
            <i class="fa fa-tag text-indigo-400"></i> Name
            <span class="text-red-400">*</span>
          </label>
          <BaseInput id="name" v-model="formData.name" placeholder="Enter tag name" required />
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <BaseButton
            class="bg-yellow-600 hover:bg-yellow-700"
            type="button"
            @click="store.handleToggleModal"
          >Cancel</BaseButton>
          <BaseButton type="submit" :disabled="submitLoading || updateLoading">
            <span v-if="submitLoading || updateLoading">{{ submitSavingText }}</span>
            <span v-else>{{ submitText }}</span>
          </BaseButton>
        </div>
      </form>
    </ScrollableLayout>
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useCrudForm } from '@/shared/composables/useCrudForm'
import { useCrudSubmit } from '@/shared/composables/useCrudSubmit'
import { useCrudMutations } from '@/shared/composables/useCrudMutations'
import { useTagStore } from '@/modules/system/stores/TagStore'

const props = defineProps({
  store: { type: Object, required: true },
})

const store = useTagStore()

const defaultFormData = {
  name: '',
}

const api = useCrudMutations(props.store.moduleName, {
  onSuccess() {
    props.store.handleToggleModal()
    resetForm()
  },
})

const { formData, resetForm } = useCrudForm(props.store, defaultFormData)

watch(
  () => props.store.item,
  (item) => {
    if (item && props.store.isEditModal) {
      formData.value = {
        name: item.name ?? '',
      }
    }
  },
  { immediate: true }
)

const { handleSubmit, submitLoading, updateLoading, submitText, submitSavingText } = useCrudSubmit(
  props.store,
  api,
  formData,
  resetForm
)
</script>
