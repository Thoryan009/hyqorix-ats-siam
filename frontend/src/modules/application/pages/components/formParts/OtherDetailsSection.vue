<template>
  <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
    <div class="flex items-center gap-3 pb-3 mb-2 border-b-2 border-rose-500">
      <span
        class="flex items-center justify-center w-8 h-8 rounded-full bg-rose-500 text-white text-sm font-bold shrink-0"
      >6</span>
      <i class="fa fa-folder-open-o text-rose-500 text-xl"></i>
      <span class="font-semibold text-lg text-gray-800">{{ t('application.other_documents') }}</span>
    </div>

    <!-- DOCUMENTS PREVIEW -->
    <div class="mt-2"></div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-4">
      <!-- NID Details -->
      <div>
        <div>
          <div class="overflow-hidden" v-if="store.formData?.nid_preview">
            <BaseImagePreview
              v-if="store.fileType?.nid_preview === 'image'"
              :src="store.formData?.nid_preview"
              width="250px"
              height="200px"
              @cancelImage="store.cancelImage('nid')"
              cancelKey="nid"
            />
            <div
              v-else
              class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50 relative"
            >
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
        <div>
          <BaseLabel for="nid_path">{{ t('application.upload_nid') }}</BaseLabel>
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
      </div>

      <!-- Upload Offer Letter Preview -->
      <div>
        <div>
          <div v-if="store.formData?.offer_letter_preview">
            <BaseImagePreview
              v-if="store.fileType?.offer_letter_preview === 'image'"
              :src="store.formData?.offer_letter_preview"
              width="250px"
              height="200px"
              @cancelImage="store.cancelImage('offer_letter')"
              cancelKey="offer_letter"
            />
            <div v-else class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50">
              <iframe :src="store.formData?.offer_letter_preview" class="w-full h-full"></iframe>
            </div>
          </div>

          <!-- Existing file from database -->
          <template v-else-if="store.item?.offer_letter_url">
            <div
              v-if="store.item.offer_letter_url.split('?')[0].toLowerCase().endsWith('.pdf')"
              class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50"
            >
              <iframe :src="store.item.offer_letter_url" class="w-full h-full"></iframe>
            </div>
            <BaseImagePreview
              v-else
              :src="store.item.offer_letter_url"
              width="320px"
              height="200px"
              @cancelImage="store.cancelImage('offer_letter')"
              cancelKey="offer_letter"
              :showCancel="false"
            />
          </template>
        </div>
        <div>
          <BaseLabel for="offer_letter_path">{{ t('application.upload_offer_letter') }}</BaseLabel>
          <BaseFileInput
            accept=".pdf, .jpg, .jpeg, .png"
            @change="store.handleFileChange($event, 'offer_letter_path', 'offer_letter_preview')"
            :fileName="store.fileName.offer_letter_path"
          />
          <div
            v-if="store.fileError.offer_letter_path"
            class="text-red-600 text-sm mt-1"
          >{{ store . fileError . offer_letter_path }}</div>
        </div>
      </div>

      <!-- Upload Visa Copy Preview -->
      <div>
        <div>
          <div v-if="store.formData?.visa_copy_preview">
            <BaseImagePreview
              v-if="store.fileType?.visa_copy_preview === 'image'"
              :src="store.formData?.visa_copy_preview"
              width="250px"
              height="200px"
              @cancelImage="store.cancelImage('visa_copy')"
              cancelKey="visa_copy"
            />
            <div v-else class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50">
              <iframe :src="store.formData?.visa_copy_preview" class="w-full h-full"></iframe>
            </div>
          </div>

          <!-- Existing file from database -->
          <template v-else-if="store.item?.visa_copy_url">
            <div
              v-if="store.item.visa_copy_url.split('?')[0].toLowerCase().endsWith('.pdf')"
              class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50"
            >
              <iframe :src="store.item.visa_copy_url" class="w-full h-full"></iframe>
            </div>
            <BaseImagePreview
              v-else
              :src="store.item.visa_copy_url"
              width="320px"
              height="200px"
              @cancelImage="store.cancelImage('visa_copy')"
              cancelKey="visa_copy"
              :showCancel="false"
            />
          </template>
        </div>
        <div>
          <BaseLabel for="visa_copy_path">{{ t('application.upload_visa_copy') }}</BaseLabel>
          <BaseFileInput
            accept=".pdf, .jpg, .jpeg, .png"
            @change="store.handleFileChange($event, 'visa_copy_path', 'visa_copy_preview')"
            :fileName="store.fileName.visa_copy_path"
          />
          <div
            v-if="store.fileError.visa_copy_path"
            class="text-red-600 text-sm mt-1"
          >{{ store . fileError . visa_copy_path }}</div>
        </div>
      </div>

      <!-- Upload Immigration Clearance Preview -->
      <div>
        <div>
          <div class="overflow-hidden" v-if="store.formData?.immigration_clearance_preview">
            <BaseImagePreview
              v-if="store.fileType?.immigration_clearance_preview === 'image'"
              :src="store.formData?.immigration_clearance_preview"
              width="250px"
              height="200px"
              @cancelImage="store.cancelImage('immigration_clearance')"
              cancelKey="immigration_clearance"
            />
            <div v-else class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50">
              <iframe :src="store.formData?.immigration_clearance_preview" class="w-full h-full"></iframe>
            </div>
          </div>

          <!-- Existing file from database -->
          <template v-else-if="store.item?.immigration_clearance_url">
            <div
              v-if="
                store.item.immigration_clearance_url.split('?')[0].toLowerCase().endsWith('.pdf')
              "
              class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50"
            >
              <iframe :src="store.item.immigration_clearance_url" class="w-full h-full"></iframe>
            </div>
            <BaseImagePreview
              v-else
              :src="store.item.immigration_clearance_url"
              width="320px"
              height="200px"
              @cancelImage="store.cancelImage('immigration_clearance')"
              cancelKey="immigration_clearance"
              :showCancel="false"
            />
          </template>
        </div>
        <div>
          <BaseLabel for="immigration_clearance_path">{{ t('application.upload_immigration_clearance') }}</BaseLabel>
          <BaseFileInput
            accept=".pdf, .jpg, .jpeg, .png"
            @change="
              store.handleFileChange(
                $event,
                'immigration_clearance_path',
                'immigration_clearance_preview',
              )
            "
            :fileName="store.fileName.immigration_clearance_path"
          />
          <div
            v-if="store.fileError.immigration_clearance_path"
            class="text-red-600 text-sm mt-1"
          >{{ store . fileError . immigration_clearance_path }}</div>
        </div>
      </div>

      <!-- Upload Ticket Preview -->
      <div>
        <div>
          <div v-if="store.formData?.ticket_preview">
            <BaseImagePreview
              v-if="store.fileType?.ticket_preview === 'image'"
              :src="store.formData?.ticket_preview"
              width="250px"
              height="200px"
              @cancelImage="store.cancelImage('ticket')"
              cancelKey="ticket"
            />
            <div v-else class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50">
              <iframe :src="store.formData?.ticket_preview" class="w-full h-full"></iframe>
            </div>
          </div>

          <!-- Existing file from database -->
          <template v-else-if="store.item?.ticket_url">
            <div
              v-if="store.item.ticket_url.split('?')[0].toLowerCase().endsWith('.pdf')"
              class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50"
            >
              <iframe :src="store.item.ticket_url" class="w-full h-full"></iframe>
            </div>
            <BaseImagePreview
              v-else
              :src="store.item.ticket_url"
              width="320px"
              height="200px"
              @cancelImage="store.cancelImage('ticket')"
              cancelKey="ticket"
              :showCancel="false"
            />
          </template>
        </div>
        <div>
          <BaseLabel for="ticket_path">{{ t('application.upload_ticket') }}</BaseLabel>
          <BaseFileInput
            accept=".pdf, .jpg, .jpeg, .png"
            @change="store.handleFileChange($event, 'ticket_path', 'ticket_preview')"
            :fileName="store.fileName.ticket_path"
          />
          <div
            v-if="store.fileError.ticket_path"
            class="text-red-600 text-sm mt-1"
          >{{ store . fileError . ticket_path }}</div>
        </div>
      </div>

      <!-- Upload Ticket Acknowledgment Preview -->
      <div>
        <div>
          <div v-if="store.formData?.acknowledgment_preview">
            <BaseImagePreview
              v-if="store.fileType?.acknowledgment_preview === 'image'"
              :src="store.formData?.acknowledgment_preview"
              width="250px"
              height="200px"
              @cancelImage="store.cancelImage('acknowledgment')"
              cancelKey="acknowledgment"
            />
            <div v-else class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50">
              <iframe :src="store.formData?.acknowledgment_preview" class="w-full h-full"></iframe>
            </div>
          </div>

          <!-- Existing file from database -->
          <template v-else-if="store.item?.acknowledgment_url">
            <div
              v-if="store.item.acknowledgment_url.split('?')[0].toLowerCase().endsWith('.pdf')"
              class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50"
            >
              <iframe :src="store.item.acknowledgment_url" class="w-full h-full"></iframe>
            </div>
            <BaseImagePreview
              v-else
              :src="store.item.acknowledgment_url"
              width="320px"
              height="200px"
              @cancelImage="store.cancelImage('acknowledgment')"
              cancelKey="acknowledgment"
              :showCancel="false"
            />
          </template>
        </div>

        <div>
          <BaseLabel for="acknowledgment_path">{{ t('application.upload_ticket_acknowledgment') }}</BaseLabel>
          <BaseFileInput
            accept=".pdf, .jpg, .jpeg, .png"
            @change="
              store.handleFileChange($event, 'acknowledgment_path', 'acknowledgment_preview')
            "
            :fileName="store.fileName.acknowledgment_path"
          />
          <div
            v-if="store.fileError.acknowledgment_path"
            class="text-red-600 text-sm mt-1"
          >{{ store . fileError . acknowledgment_path }}</div>
        </div>
      </div>

      <!-- svp Preview -->
      <div>
        <div>
          <div class="overflow-hidden" v-if="store.formData?.svp_preview">
            <BaseImagePreview
              v-if="store.fileType?.svp_preview === 'image'"
              :src="store.formData?.svp_preview"
              width="250px"
              height="200px"
              @cancelImage="store.cancelImage('svp')"
              cancelKey="svp"
            />
            <div v-else class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50">
              <iframe :src="store.formData?.svp_preview" class="w-full h-full"></iframe>
            </div>
          </div>

          <!-- Existing file from database -->
          <template v-else-if="store.item?.svp_url">
            <div
              v-if="store.item.svp_url.split('?')[0].toLowerCase().endsWith('.pdf')"
              class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50"
            >
              <iframe :src="store.item.svp_url" class="w-full h-full"></iframe>
            </div>
            <BaseImagePreview
              v-else
              :src="store.item.svp_url"
              width="320px"
              height="200px"
              @cancelImage="store.cancelImage('svp')"
              cancelKey="svp"
              :showCancel="false"
            />
          </template>
        </div>

        <div>
          <BaseLabel for="svp_path">{{ t('application.upload_svp') }}</BaseLabel>
          <BaseFileInput
            accept=".pdf, .jpg, .jpeg, .png"
            @change="store.handleFileChange($event, 'svp_path', 'svp_preview')"
            :fileName="store.fileName.svp_path"
          />
          <div
            v-if="store.fileError.svp_path"
            class="text-red-600 text-sm mt-1"
          >{{ store . fileError . svp_path }}</div>
        </div>
      </div>

      <!-- qvp Preview -->
      <div>
        <div>
          <div class="overflow-hidden" v-if="store.formData?.qvp_preview">
            <BaseImagePreview
              v-if="store.fileType?.qvp_preview === 'image'"
              :src="store.formData?.qvp_preview"
              width="250px"
              height="200px"
              @cancelImage="store.cancelImage('qvp')"
              cancelKey="qvp"
            />
            <div v-else class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50">
              <iframe :src="store.formData?.qvp_preview" class="w-full h-full"></iframe>
            </div>
          </div>

          <!-- Existing file from database -->
          <template v-else-if="store.item?.qvp_url">
            <div
              v-if="store.item.qvp_url.split('?')[0].toLowerCase().endsWith('.pdf')"
              class="w-80 h-64 rounded-lg border shadow overflow-hidden bg-gray-50"
            >
              <iframe :src="store.item.qvp_url" class="w-full h-full"></iframe>
            </div>
            <BaseImagePreview
              v-else
              :src="store.item.qvp_url"
              width="320px"
              height="200px"
              @cancelImage="store.cancelImage('qvp')"
              cancelKey="qvp"
              :showCancel="false"
            />
          </template>
        </div>

        <div>
          <BaseLabel for="qvp_path">{{ t('application.upload_qvp') }}</BaseLabel>
          <BaseFileInput
            accept=".pdf, .jpg, .jpeg, .png"
            @change="store.handleFileChange($event, 'qvp_path', 'qvp_preview')"
            :fileName="store.fileName.qvp_path"
          />
          <div
            v-if="store.fileError.qvp_path"
            class="text-red-600 text-sm mt-1"
          >{{ store . fileError . qvp_path }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useApplicationStore } from '@/modules/application/store/applicationStore'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useApplicationStore()
</script>
