
<template>
  <BaseModal
    :isVisible="store.isModal || store.isEditModal"
    :title="modalTitle"
    @close="store.handleToggleModal"
    :className="'w-full xl:max-w-[50vw]'"
  >
    <ScrollableLayout height="680px">

     <form @submit.prevent="handleSubmit" class="space-y-4">

        <div class="space-y-2">
          <BaseLabel for="name">{{ t('departments.name') }}</BaseLabel>
          <BaseInput
            id="name"
            v-model="formData.name"
            :placeholder="t('departments.placeholder_name')"
            :required="true"
          />
        </div>



        <!-- Actions -->
        <div class="flex justify-end gap-2 py-5">
          <BaseButton
            class="bg-yellow-600 hover:bg-yellow-700"
            type="button"
            @click="store.handleToggleModal"
          >{{ t('shared.actions.cancel') }}</BaseButton>
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
import { computed } from 'vue'
import { useCrudForm } from '@/shared/composables/useCrudForm'
import { useCrudSubmit } from '@/shared/composables/useCrudSubmit'
import { useCrudMutations } from '@/shared/composables/useCrudMutations'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate()

const props = defineProps({
  extraData: {
    type: Object,
    default: () => ({}),
  },
  store: {
    type: Object,
    required: true,
  },
})

const modalTitle = computed(() =>
  props.store.isEditModal ? t('departments.edit') : t('departments.add')
)

const defaultFormData = {
  name: 'Test Name',
}

// API
const api = useCrudMutations(props.store.moduleName, {
  onSuccess() {
    props.store.handleToggleModal()
    resetForm()
  },
})


const { formData, resetForm } = useCrudForm(props.store, defaultFormData)

// SUBMIT
const { handleSubmit, submitLoading, updateLoading, submitText, submitSavingText } = useCrudSubmit(
  props.store,
  api,
  formData,
  resetForm
)
</script>
