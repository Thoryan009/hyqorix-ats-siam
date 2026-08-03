<template>
  <BaseModal
    :isVisible="store.isDeleteModal"
    :title="`Are you sure you want to delete this ${store.moduleName}?`"
    @close="store.handleToggleModal"
  >
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div class="space-y-2">
        <p class="text-lg"><strong>ID:</strong> {{ store.item.id }}</p>
        <p class="text-lg"><strong>Organization:</strong> {{ store.item.organization_name }}</p>
        <p class="text-lg"><strong>Email:</strong> {{ store.item.email }}</p>
        <p class="text-lg"><strong>Vendor ID:</strong> {{ store.item.vendor_id }}</p>
      </div>

      <div class="flex justify-end gap-2 pt-4">
        <BaseButton class="bg-yellow-600 hover:bg-yellow-700" type="button" @click="store.handleToggleModal">
          Cancel
        </BaseButton>
        <BaseButton type="submit" class="bg-red-600 hover:bg-red-700" :disabled="removeLoading">
          <span v-if="removeLoading">Deleting...</span>
          <span v-else>Delete</span>
        </BaseButton>
      </div>
    </form>
  </BaseModal>
</template>

<script setup>
import { useVendorMutations } from '@/modules/vendor/queries/useVendorMutations'
import { useVendorStore } from '@/modules/vendor/store/vendorStore'

const store = useVendorStore()

const { remove, removeLoading } = useVendorMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
  },
})

const handleSubmit = async () => {
  await remove.mutateAsync(store.item.id)
}
</script>
