<template>
  <div>
    <div class="col-span-1 md:col-span-3 font-semibold text-lg border-b border-gray-300 pb-1">
      Contact Information
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-4">
      <!-- <div>
        <BaseLabel for="mobile">Mobile (Optional)</BaseLabel>
        <BaseInput id="mobile" v-model="store.formData.mobile" placeholder="eg +8801712345678" />
      </div> -->

      <!-- <div>
        <BaseLabel for="email">Email (Optional)</BaseLabel>
        <BaseInput
          id="email"
          type="email"
          v-model="store.formData.email"
          placeholder="eg example@mail.com"
        />
      </div> -->
    </div>
  </div>
</template>

<script setup>
import { useApplicationStore } from '@/modules/application/store/applicationStore'

const store = useApplicationStore()
</script>
