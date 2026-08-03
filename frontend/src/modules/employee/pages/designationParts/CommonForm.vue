<template>
  <form @submit.prevent="onSubmit" class="space-y-5 p-1">
    <!-- ── Group 1: Designation Info ── -->
    <div class="rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
      <div
        class="px-4 py-2.5 flex items-center gap-2"
        style="background: linear-gradient(90deg, #3b82f6, #6366f1)"
      >
        <i class="fa fa-id-badge text-white text-sm"></i>
        <span class="text-white text-sm font-semibold tracking-wide uppercase"
          >Designation Info</span
        >
      </div>
      <div class="p-4 bg-white space-y-4">
        <!-- Name -->
        <div class="space-y-1.5">
          <label
            for="name"
            class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
          >
            <i class="fa fa-tag text-blue-400"></i> Name <span class="text-red-400">*</span>
          </label>
          <BaseInput
            id="name"
            v-model="localForm.name"
            placeholder="Eg: Manager"
            :required="true"
          />
        </div>

        <!-- ── Editor: Description ── -->
        <div class="space-y-1.5">
          <label
            for="description"
            class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide"
          >
            <i class="fa fa-align-left text-blue-400"></i> Description
          </label>

          <BaseQuillEditor
            id="description"
            v-model="formData.description"
            :placeholder="'Enter designation description'"
          />
        </div>
        <!-- Description -->
        <!-- <div class="space-y-1.5">
          <label for="description" class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">
            <i class="fa fa-align-left text-blue-400"></i> Description
          </label>
          <BaseTextArea id="description" v-model="localForm.description" placeholder="Optional description..." rows="3" />
        </div> -->
      </div>
    </div>

    <!-- ── Actions ── -->
    <div class="flex justify-end gap-3 pt-2 pb-4">
      <BaseButton
        class="bg-yellow-700 hover:bg-yellow-800 text-white border border-gray-200 gap-2 cursor-pointer"
        @click="onCancel"
      >
        <i class="fa fa-times"></i> Cancel
      </BaseButton>
      <BaseButton type="submit" class="gap-2"> <i class="fa fa-check"></i> Save </BaseButton>
    </div>
  </form>
</template>

<script setup>
import { ref, watch } from 'vue'

// Props
const props = defineProps({
  formData: { type: Object, required: true },
  onSubmit: { type: Function, required: true },
  onCancel: { type: Function, required: true },
})

// Emit
const emit = defineEmits(['update:formData'])

// Local reactive copy
const localForm = ref({
  name: '',
  description: '',
  ...props.formData,
})

// Sync to parent
watch(
  localForm,
  (newVal) => {
    emit('update:formData', newVal)
  },
  { deep: true },
)
</script>
