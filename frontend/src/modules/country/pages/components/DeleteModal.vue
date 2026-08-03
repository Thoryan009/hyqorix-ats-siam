<template>
  <BaseModal
    :isVisible="store.isDeleteModal"

    :title="t('shared.actions.delete_confirmation') + ' ' + t('country.module')"
    :maxWidth="`${store.modalWidth}`"
    @close="store.handleToggleModal"
  >
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div class="space-y-2">
        <p class="text-lg">
          <strong>ID:</strong>
          {{ store.item.id }}
        </p>
        <p class="text-lg">
          <strong>Name:</strong>
          {{ store.item.name }}
        </p>
      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-2 pt-4">
        <BaseButton
          class="bg-yellow-600 hover:bg-yellow-700"
          type="button"
          @click="store.handleToggleModal"
        >{{t('shared.actions.cancel')}}</BaseButton>
        <BaseButton type="submit" class="bg-red-600 hover:bg-red-700" :disabled="removeLoading">
          <span v-if="removeLoading">{{t('shared.messages.deleting')}}</span>
          <span v-else>{{t('shared.actions.delete')}}</span>
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>

<script setup>
import { useCountryMutations } from '@/modules/country/queries/useCountryMutations'
import { useCountryStore } from '@/modules/country/store/countryStore'
import { useTranslate } from '@/shared/composables/useTranslate'

const { t } = useTranslate('shared')
const store = useCountryStore()


const { remove, removeLoading } = useCountryMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()     // ✅ close modal
  },
   onError: (error) => {
    console.log('Custom error handling', error)
  },
})
const handleSubmit = async () => {
  await remove.mutateAsync(store.item.id)
}
</script>
