<template>
  <div>
    <!-- Header -->
    <div
      class="col-span-1 md:col-span-3 font-semibold text-lg border-b border-gray-300 pb-1 flex items-center gap-3"
    >NID Details</div>

    <!-- IMAGE PREVIEW -->
    <div class="mt-2">
      <!-- New uploaded file preview -->
      <div class="overflow-hidden" v-if="store.formData?.nid_preview">
        <BaseImagePreview
          v-if="store.fileType?.nid_preview === 'image'"
          :src="store.formData?.nid_preview"
          width="250px"
          height="200px"
          @cancelImage="store.cancelImage('nid')"
          cancelKey="nid"
        />
        <div v-else class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50 relative">
          <iframe :src="store.formData?.nid_preview" class="w-full h-full"></iframe>
          <button
            class="absolute top-2 right-2 w-6 h-6 bg-slate-900 rounded-full p-1 hover:bg-slate-700 cursor-pointer flex justify-center items-center transition text-slate-50"
            @click="store.cancelImage('nid')"
            type="button"
          >
            <i class="fa fa-times"></i>
          </button>
        </div>
      </div>

      <!-- Existing file from database -->
      <template v-else-if="store.item?.nid_url">
        <div
          v-if="store.item.nid_url.split('?')[0].toLowerCase().endsWith('.pdf')"
          class="w-80 h-64 rounded-lg border shadow overflow-hidden relative bg-gray-50"
        >
          <iframe :src="store.item.nid_url" class="w-full h-full"></iframe>
          <button
            class="absolute top-2 right-2 w-6 h-6 bg-slate-900 rounded-full p-1 hover:bg-slate-700 cursor-pointer flex justify-center items-center transition text-slate-50"
            @click="store.deleteImagePdfFiles(store.item.id, 'nid_path')"
            type="button"
          >
            <i class="fa fa-times"></i>
          </button>
        </div>
        <BaseImagePreview
          v-else
          :src="store.item.nid_url"
          width="320px"
          height="200px"
          cancelKey="nid"
          @cancelImage="store.cancelImage('nid')"
        />
      </template>
    </div>

    <!-- NID FORM -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-4">
      <div>
        <BaseLabel for="nid_path">Upload NID (Optional)</BaseLabel>
        <BaseFileInput
          accept=".pdf, .jpg, .jpeg, .png"
          @change="store.handleFileChange($event, 'nid_path', 'nid_preview')"
          :fileName="store.fileName.nid_path"
        />
        <div
          v-if="store.fileError.nid_path"
          class="text-red-600 text-sm mt-1"
        >{{ store.fileError.nid_path }}</div>
      </div>
      <!-- <div>
        <BaseLabel for="nid_no">NID Number (Optional)</BaseLabel>
        <BaseInput id="nid_no" v-model="store.formData.nid_no" placeholder="eg 1998123456789" />
      </div>-->
    </div>
  </div>
</template>

<script setup>
import { useApplicationStore } from '@/modules/application/store/applicationStore'

const store = useApplicationStore()
</script>
