<template>
  <div v-if="store.showEmailForm || filters.transaction_status == 'invoice-generated'" class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
    <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-300 pb-2 mb-4">Compose Email</h3>

    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">To Mail</label>
        <input
          v-model="store.formData.to_mail"
          type="email"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
        />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Subject</label>
        <input
          v-model="store.formData.subject"
          type="text"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
        />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Body</label>
        <textarea
          v-model="store.formData.body"
          rows="10"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
        ></textarea>
      </div>

      <!-- IMAGE PREVIEW -->
      <div class="mt-2">
        <!-- New uploaded file preview -->
        <div v-if="store.formData?.invoice_preview">
          <BaseImagePreview
            v-if="store.fileType?.invoice_preview === 'image'"
            :src="store.formData?.invoice_preview"
            width="320px"
            height="320px"
            @cancelImage="store.cancelImage('invoice')"
          />
          <div
            v-else
            class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50 relative"
          >
            <iframe :src="store.formData?.invoice_preview" class="w-full h-full"></iframe>

            <button
              class="absolute top-2 right-2 w-6 h-6 bg-slate-900 rounded-full p-1 hover:bg-slate-700 cursor-pointer flex justify-center items-center transition text-slate-50"
              @click="store.cancelImage('invoice')"
              type="button"
            >
              <i class="fa fa-times"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="space-y-1">
        <div class="flex items-center gap-2 text-sm font-medium">
          <i class="fa fa-paperclip"></i> Attachment
        </div>
        <BaseFileInput
          accept=".pdf, .jpg, .jpeg, .png"
          @change="store.handleFileChange($event, 'invoice_path', 'invoice_preview')"
          :fileName="store.fileName.invoice_path"
        />
      </div>

      <div class="flex justify-end pt-2 gap-4">
        <BaseButton
        v-if="filters.transaction_status !== 'invoice-generated'"
          className="bg-gray-600 text-white hover:bg-gray-700 cursor-pointer"
          @click="store.handleToggleEmailForm"
        >Cancel Mail</BaseButton>
        <BaseButton
          :disabled="!store.fileName.invoice_path || isMailing"
          className="bg-green-600 text-white hover:bg-green-700 cursor-pointer"
          @click="handleSubmit"
        >{{ isMailing ? 'Sending...' : 'Send Mail' }}</BaseButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useClientBillMutations } from '../../queries/useClientBillMutation'
import { useClientBillStore } from '../../store/clientBillStore'

defineProps({
  filters: { type: Object, required: true },
})

const emit = defineEmits(['reset'])

const handleReset = () => {
  emit('reset')
}


const store = useClientBillStore()


const { sendMail, isMailing } = useClientBillMutations(store.moduleName, {
  onSuccess() {
    store.handleToggleModal()
    store.handleToggleEmailForm()
    handleReset()
  },
   onError(error) {
    console.log('Error:', error)
  },
})

const handleSubmit = async () => {
  const payload = new FormData()

  for (const key in store.formData) {
    const value = store.formData[key]
    if (value === null || value === undefined || value === '') {
      continue
    }
    if (value.invoice_preview) {
      continue
    }

    payload.append(key, value)
  }

  await sendMail.mutateAsync(payload)
}
</script>
