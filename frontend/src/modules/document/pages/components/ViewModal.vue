<template>
  <BaseModal
    :isVisible="store.isViewModal"
    :title="`View ${store.moduleName} Details`"
    :className="'max-w-[95vw] xl:max-w-[90vw]'"
    @close="store.handleToggleModal"
  >
    <ViewModalLayout height="80vh">
      <div class="grid grid-cols-1 gap-5 lg:grid-cols-12 lg:items-start">
        <!-- Left column: summary + details -->
        <div class="space-y-5 lg:col-span-5">
          <!-- Document summary -->
          <div
            class="relative overflow-hidden rounded-2xl border border-sky-200 bg-linear-to-br from-sky-50 to-blue-100 p-5 shadow-lg"
          >
            <div class="flex items-start gap-4">
              <div
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-sky-500 to-blue-600 text-white shadow-md"
              >
                <i :class="fileIcon" class="text-xl"></i>
              </div>
              <div class="min-w-0 flex-1">
                <h3 class="text-lg font-bold text-sky-900">{{ store.item.name || '—' }}</h3>
                <p class="mt-1 text-sm text-sky-700">
                  <i class="fa fa-hashtag mr-1"></i>
                  {{ store.item.document_no || '—' }}
                </p>
                <span
                  v-if="store.item.category"
                  class="mt-2 inline-flex items-center rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-sky-800 shadow-sm"
                >
                  <i class="fa fa-folder-open-o mr-1.5"></i>
                  {{ store.item.category }}
                </span>
              </div>
            </div>

            <div v-if="store.item.path_url" class="mt-4 flex flex-wrap gap-2">
              <BaseButton
                v-can="'document.download'"
                class="bg-sky-600 text-white hover:bg-sky-700"
                @click="handleDownload"
              >
                <i class="fa fa-download mr-1.5"></i>
                Download
              </BaseButton>
              <a
                :href="store.item.path_url"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center rounded-lg border border-sky-300 bg-white px-4 py-2 text-sm font-medium text-sky-700 shadow-sm transition-colors hover:bg-sky-50"
              >
                <i class="fa fa-external-link mr-1.5"></i>
                Open in New Tab
              </a>
            </div>
          </div>

          <!-- Document details -->
          <div class="rounded-xl border border-sky-200 bg-linear-to-br from-sky-50 to-blue-50 p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-3">
              <div class="rounded-lg bg-linear-to-br from-sky-500 to-blue-600 p-2.5 text-white shadow-sm">
                <i class="fa fa-file-text-o text-lg"></i>
              </div>
              <h4 class="text-lg font-bold text-sky-900">Document Information</h4>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <div class="rounded-lg bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
                <div class="mb-1 flex items-center gap-2 text-gray-600">
                  <i class="fa fa-hashtag text-sky-500"></i>
                  <span class="text-sm font-medium">Document No</span>
                </div>
                <p class="font-semibold text-gray-800">{{ store.item.document_no || '—' }}</p>
              </div>

              <div class="rounded-lg bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
                <div class="mb-1 flex items-center gap-2 text-gray-600">
                  <i class="fa fa-tag text-sky-500"></i>
                  <span class="text-sm font-medium">Category</span>
                </div>
                <p class="font-semibold text-gray-800">{{ store.item.category || '—' }}</p>
              </div>

              <div class="rounded-lg bg-white p-4 shadow-sm transition-shadow hover:shadow-md sm:col-span-2">
                <div class="mb-1 flex items-center gap-2 text-gray-600">
                  <i class="fa fa-file-o text-sky-500"></i>
                  <span class="text-sm font-medium">File Name</span>
                </div>
                <p class="truncate font-semibold text-gray-800" :title="store.item.file_name">
                  {{ store.item.file_name || '—' }}
                </p>
              </div>

              <div class="rounded-lg bg-white p-4 shadow-sm transition-shadow hover:shadow-md sm:col-span-2">
                <div class="mb-1 flex items-center gap-2 text-gray-600">
                  <i class="fa fa-header text-sky-500"></i>
                  <span class="text-sm font-medium">Name</span>
                </div>
                <p class="font-semibold text-gray-800">{{ store.item.name || '—' }}</p>
              </div>

              <div class="rounded-lg bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
                <div class="mb-1 flex items-center gap-2 text-gray-600">
                  <i class="fa fa-user-plus text-sky-500"></i>
                  <span class="text-sm font-medium">Uploaded By</span>
                </div>
                <p class="font-semibold text-gray-800">{{ store.item.created_by || '—' }}</p>
              </div>

              <div class="rounded-lg bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
                <div class="mb-1 flex items-center gap-2 text-gray-600">
                  <i class="fa fa-calendar-plus-o text-sky-500"></i>
                  <span class="text-sm font-medium">Created At</span>
                </div>
                <p class="font-semibold text-gray-800">{{ store.item.created_at || '—' }}</p>
              </div>

              <div class="rounded-lg bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
                <div class="mb-1 flex items-center gap-2 text-gray-600">
                  <i class="fa fa-pencil text-sky-500"></i>
                  <span class="text-sm font-medium">Updated By</span>
                </div>
                <p class="font-semibold text-gray-800">{{ store.item.updated_by || '—' }}</p>
              </div>

              <div class="rounded-lg bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
                <div class="mb-1 flex items-center gap-2 text-gray-600">
                  <i class="fa fa-calendar-check-o text-sky-500"></i>
                  <span class="text-sm font-medium">Updated At</span>
                </div>
                <p class="font-semibold text-gray-800">{{ store.item.updated_at || '—' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Right column: file preview -->
        <div class="lg:col-span-7 lg:sticky lg:top-0">
          <div class="flex h-full min-h-[50vh] flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm lg:min-h-[72vh]">
            <div class="flex items-center gap-2 border-b border-gray-200 bg-gray-50 px-5 py-3">
              <i class="fa fa-eye text-sky-600"></i>
              <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-700">File Preview</h4>
            </div>

            <div class="flex flex-1 flex-col p-5">
              <div
                v-if="!store.item.path_url"
                class="flex flex-1 flex-col items-center justify-center text-gray-400"
              >
                <i class="fa fa-file-o mb-3 text-4xl opacity-40"></i>
                <p class="text-sm">No file attached to this document.</p>
              </div>

              <template v-else>
                <div
                  v-if="isPdf(store.item.path_url)"
                  class="flex-1 overflow-hidden rounded-xl border border-gray-200 bg-gray-50"
                >
                  <iframe
                    :src="store.item.path_url"
                    class="h-full min-h-[45vh] w-full lg:min-h-[65vh]"
                    title="PDF preview"
                  ></iframe>
                </div>

                <div
                  v-else-if="isImage(store.item.path_url)"
                  class="flex flex-1 items-center justify-center overflow-auto rounded-xl border border-gray-200 bg-gray-50 p-4"
                >
                  <img
                    :src="store.item.path_url"
                    :alt="store.item.name || 'Document preview'"
                    class="max-h-[65vh] max-w-full rounded-lg object-contain shadow-sm"
                  />
                </div>

                <div
                  v-else
                  class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 bg-gray-50"
                >
                  <div
                    class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-sky-100 text-sky-600"
                  >
                    <i :class="fileIcon" class="text-3xl"></i>
                  </div>
                  <p class="text-sm font-medium text-gray-700">{{ store.item.file_name || store.item.name }}</p>
                  <p class="mt-1 text-xs text-gray-500">Preview is not available for this file type.</p>
                  <div class="mt-4 flex flex-wrap justify-center gap-2">
                    <BaseButton
                      v-can="'document.download'"
                      class="bg-sky-600 text-white hover:bg-sky-700"
                      @click="handleDownload"
                    >
                      <i class="fa fa-download mr-1.5"></i>
                      Download File
                    </BaseButton>
                    <a
                      :href="store.item.path_url"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                      <i class="fa fa-external-link mr-1.5"></i>
                      Open File
                    </a>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </ViewModalLayout>
  </BaseModal>
</template>

<script setup>
import { computed } from 'vue'
import BaseModal from '@/shared/components/base/BaseModal.vue'
import BaseButton from '@/shared/components/base/BaseButton.vue'
import ViewModalLayout from '@/shared/components/ui/ViewModalLayout.vue'
import { useDocumentStore } from '../../store/documentStore'
import { downloadDocument } from '../../services/documentService'
import { toast } from '@/shared/config/toastConfig'

const store = useDocumentStore()

function getExtension(url) {
  return (url || '').split('?')[0].toLowerCase().split('.').pop() || ''
}

function isPdf(url) {
  return getExtension(url) === 'pdf'
}

function isImage(url) {
  return ['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(getExtension(url))
}

const fileIcon = computed(() => {
  const ext = getExtension(store.item.path_url || store.item.file_name)

  const iconMap = {
    pdf: 'fa fa-file-pdf-o',
    doc: 'fa fa-file-word-o',
    docx: 'fa fa-file-word-o',
    xls: 'fa fa-file-excel-o',
    xlsx: 'fa fa-file-excel-o',
    ppt: 'fa fa-file-powerpoint-o',
    pptx: 'fa fa-file-powerpoint-o',
    zip: 'fa fa-file-archive-o',
    rar: 'fa fa-file-archive-o',
    jpg: 'fa fa-file-image-o',
    jpeg: 'fa fa-file-image-o',
    png: 'fa fa-file-image-o',
    webp: 'fa fa-file-image-o',
    gif: 'fa fa-file-image-o',
  }

  return iconMap[ext] || 'fa fa-file-text-o'
})

async function handleDownload() {
  try {
    await downloadDocument(store.item.id, store.item.file_name || store.item.name)
  } catch (error) {
    console.error(error)
    toast.error('Failed to download document.')
  }
}
</script>
