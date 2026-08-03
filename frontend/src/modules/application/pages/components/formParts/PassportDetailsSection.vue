<template>
  <div
    class="bg-gradient-to-br from-gray-50 to-white rounded-xl shadow-sm border border-gray-200 p-6"
  >

    <!-- Header -->
    <div class="flex items-center gap-3 pb-3 mb-4 border-b-2 border-emerald-500">
      <span
        class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-500 text-white text-sm font-bold shrink-0"
      >1</span>
      <i class="fa fa-id-card-o text-emerald-500 text-xl"></i>
      <span class="font-semibold text-lg text-gray-800">{{ t('application.passport_details') }}</span>
    </div>

    <!-- Main Content: Form Left, Preview Right -->
    <div class="flex flex-col lg:flex-row gap-6 mt-4">
      <!-- Passport Form -->
      <div class="flex-1 bg-white rounded-lg p-5 border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Upload + OCR -->

          <div class="md:col-span-2 flex items-end gap-2">
            <div class="flex-1">
              <BaseLabel for="passport_path">{{ t('application.upload_passport') }}</BaseLabel>
              <!-- Progress Bar -->
              <div
                v-if="passportOCRLoading"
                class="w-full my-2 h-2 bg-gray-200 rounded overflow-hidden"
              >
                <div
                  class="h-full bg-green-600 transition-all duration-700"
                  :style="{ width: `${progress}%` }"
                ></div>
              </div>
              <BaseFileInput
                accept=".jpg, .jpeg, .png"
                @change="store.handleFileChange($event, 'passport_path', 'passport_preview', 1024 * 1024)"
                :fileName="store.fileName.passport_path"
              />
              <div
                v-if="store.fileError.passport_path"
                class="text-red-600 text-sm mt-1"
              >{{ store.fileError.passport_path }}</div>
            </div>

            <div class="mb-0.5 flex flex-col items-center w-40">
              <button
                @click="handlePassportOCR(store.formData.passport_path)"
                class="ocr-button px-4 py-2.5 cursor-pointer bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2 w-full justify-center font-semibold disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105"
                :disabled="passportOCRLoading"
                type="button"
              >
                <svg
                  v-if="!passportOCRLoading"
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                <svg v-else class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  />
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  />
                </svg>
                {{ passportOCRLoading ? t('application.processing') : 'OCR' }}
              </button>
            </div>
          </div>

          <!-- Passport Number -->
          <div>
            <BaseLabel for="passport_no">{{ t('shared.labels.passport_no') }} ({{ t('application.optional') }})</BaseLabel>
            <BaseInput
              id="passport_no"
              v-model="store.formData.passport_no"
              placeholder="eg A1234567"
            />
            <p
              v-if="passportExistsWarning"
              class="text-red-600 text-sm mt-1"
            >{{ passportExistsWarning }}</p>
          </div>

          <!-- Date of Issue -->
          <div>
            <BaseLabel for="date_of_issue">{{ t('application.date_of_issue') }} ({{ t('application.optional') }})</BaseLabel>
            <BaseInput id="date_of_issue" type="date" v-model="store.formData.date_of_issue" />
          </div>

          <!-- Date of Expiry -->
          <div>
            <BaseLabel for="date_of_expiry">{{ t('application.date_of_expiry') }} ({{ t('application.optional') }})</BaseLabel>
            <BaseInput id="date_of_expiry" type="date" v-model="store.formData.date_of_expiry" />
          </div>

          <!-- Passport Link -->
          <!-- <div>
            <BaseLabel for="passport_link">{{ t('application.passport_link') }} ({{ t('application.optional') }})</BaseLabel>
            <BaseInput
              id="passport_link"
              v-model="store.formData.passport_link"
              placeholder="eg https://drive..."
            />
          </div>-->

          <div>
            <BaseLabel for="given_name">{{ t('application.given_name') }} ({{ t('application.required') }})</BaseLabel>
            <BaseInput
              required
              id="given_name"
              v-model="store.formData.given_name"
              placeholder="eg Rahim"
            />
          </div>

          <div>
            <BaseLabel for="sur_name">{{ t('application.surname') }} ({{ t('application.required') }})</BaseLabel>
            <BaseInput
              required
              id="sur_name"
              v-model="store.formData.sur_name"
              placeholder="eg Ahmed"
            />
          </div>

          <div>
            <BaseLabel for="dob">{{ t('application.date_of_birth') }} ({{ t('application.optional') }})</BaseLabel>
            <BaseInput id="dob" type="date" v-model="store.formData.date_of_birth" />
          </div>

          <div>
            <BaseLabel for="sex">{{ t('application.sex') }} ({{ t('application.optional') }})</BaseLabel>
            <BaseSelect id="sex" :options="sexOptions" v-model="store.formData.sex" />
          </div>

          <div>
            <BaseLabel for="nationality">{{ t('application.nationality') }} ({{ t('application.optional') }})</BaseLabel>
            <BaseInput id="nationality" v-model="store.formData.nationality" />
          </div>

          <div>
            <BaseLabel for="place_of_birth">{{ t('application.place_of_birth') }} ({{ t('application.optional') }})</BaseLabel>
            <BaseInput id="place_of_birth" v-model="store.formData.place_of_birth" />
          </div>

          <div>
            <BaseLabel for="father_name">{{ t('application.father_name') }} ({{ t('application.optional') }})</BaseLabel>
            <BaseInput
              id="father_name"
              v-model="store.formData.father_name"
              placeholder="eg Rahim"
            />
          </div>

          <div>
            <BaseLabel for="mother_name">{{ t('application.mother_name') }} ({{ t('application.optional') }})</BaseLabel>
            <BaseInput
              id="mother_name"
              v-model="store.formData.mother_name"
              placeholder="eg Rahim"
            />
          </div>

          <div>
            <BaseLabel for="mobile">{{ t('shared.labels.phone') }} ({{ t('application.optional') }})</BaseLabel>
            <BaseInput id="mobile" v-model="store.formData.mobile" placeholder="eg +8801712345678" />
          </div>

          <!-- <div>
            <BaseLabel for="whatsapp_no">WhatsApp No</BaseLabel>
            <BaseInput
              id="whatsapp_no"
              v-model="store.formData.whatsapp_no"
              placeholder="eg +8801712345678"
            />
          </div>-->
          <div>
            <BaseLabel for="nid_no">{{ t('application.nid_number') }} ({{ t('application.optional') }})</BaseLabel>
            <BaseInput id="nid_no" v-model="store.formData.nid_no" placeholder="eg 1998123456789" />
          </div>

          <div class="md:col-span-3">
            <BaseLabel for="address">{{ t('shared.labels.address') }} ({{ t('application.optional') }})</BaseLabel>
            <BaseInput
              type="text"
              id="address"
              v-model="store.formData.address"
              placeholder="eg 123 Main Street, Dhaka, Bangladesh"
            />
          </div>
        </div>
      </div>

      <!-- IMAGE PREVIEW - Right Side -->
      <div class="lg:w-1/3 bg-white rounded-lg p-3 border border-gray-200">
        <div class="mb-3 flex items-center justify-between">
          <h3 class="font-semibold text-gray-700 flex items-center gap-2">
            {{ t('application.passport_preview') }}
            <span
              v-if="imageLoaded"
              class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded"
            >{{ t('application.hover_to_zoom') }}</span>
          </h3>

          <div v-if="hasPreview" class="flex items-center gap-2">
            <button
              @click="zoomOut"
              class="p-1.5 bg-gray-100 hover:bg-gray-200 rounded transition-colors"
              type="button"
              title="Zoom Out"
            >
              <svg
                class="w-4 h-4 text-gray-700"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"
                />
              </svg>
            </button>
            <span class="text-sm text-gray-600 min-w-[3rem] text-center">{{ zoomLevel }}%</span>
            <button
              @click="zoomIn"
              class="p-1.5 bg-gray-100 hover:bg-gray-200 rounded transition-colors"
              type="button"
              title="Zoom In"
            >
              <svg
                class="w-4 h-4 text-gray-700"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"
                />
              </svg>
            </button>
            <button
              @click="resetZoom"
              class="p-1.5 bg-gray-100 hover:bg-gray-200 rounded transition-colors"
              type="button"
              title="Reset Zoom"
            >
              <svg
                class="w-4 h-4 text-gray-700"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                />
              </svg>
            </button>
          </div>
        </div>

        <div
          class="passport-preview-container overflow-auto max-h-[600px] bg-gray-50 rounded-lg border border-gray-200"
        >
          <!-- New uploaded file preview (prioritize over existing) -->
          <div
            v-if="store.formData?.passport_preview && store.formData.passport_preview.length > 0"
            class="p-4"
          >
            <div class="flex items-center justify-center relative">
              <canvas
                ref="passportCanvas"
                @mousemove="handleMouseMove"
                @mouseleave="handleMouseLeave"
                @mouseenter="handleMouseEnter"
                class="max-w-full h-auto rounded shadow-md cursor-crosshair"
              ></canvas>

              <!-- Zoom lens overlay -->
              <div
                v-if="showZoomLens"
                class="zoom-lens absolute pointer-events-none border-4 border-green-500 rounded-xl overflow-hidden shadow-2xl"
                :style="{
                  left: lensPosition.x + 'px',
                  top: lensPosition.y + 'px',
                  width: lensSize + 'px',
                  height: lensSize + 'px',
                  transform: 'translate(-50%, -50%)',
                }"
              >
                <canvas ref="zoomCanvas" :width="lensSize" :height="lensSize"></canvas>
              </div>
            </div>
          </div>

          <!-- Existing file from database -->
          <div
            v-else-if="store.item?.passport_url && store.item.passport_url.length > 0"
            class="p-4"
          >
            <div class="flex items-center justify-center relative">
              <canvas
                ref="passportCanvasExisting"
                @mousemove="handleMouseMove"
                @mouseleave="handleMouseLeave"
                @mouseenter="handleMouseEnter"
                class="max-w-full h-auto rounded shadow-md cursor-crosshair"
              ></canvas>

              <!-- Zoom lens overlay -->
              <div
                v-if="showZoomLens"
                class="zoom-lens absolute pointer-events-none border-4 border-green-500 rounded-full overflow-hidden shadow-2xl"
                :style="{
                  left: lensPosition.x + 'px',
                  top: lensPosition.y + 'px',
                  width: lensSize + 'px',
                  height: lensSize + 'px',
                  transform: 'translate(-50%, -50%)',
                }"
              >
                <canvas ref="zoomCanvasExisting" :width="lensSize" :height="lensSize"></canvas>
              </div>
            </div>
          </div>

          <!-- Empty state -->
          <div v-else class="flex flex-col items-center justify-center h-64 text-gray-400">
            <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
              />
            </svg>
            <p class="text-sm">{{ t('application.no_passport_uploaded') }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { useApplicationMutations } from '@/modules/application/queries/useApplicationMutations'
import { useApplicationStore } from '@/modules/application/store/applicationStore'
import { formatDateToISO } from '@/modules/application/utils/formateDateToISO'
import { formatSex } from '@/modules/application/utils/formatSex'
import { sexOptions } from '@/modules/application/data/applicationData'
import {useTranslate} from '@/shared/composables/useTranslate'

const { t } = useTranslate()
const store = useApplicationStore()
const { passportOCR, passportOCRLoading, checkPassportExists } = useApplicationMutations(
  store.moduleName
)

// Progress bar ref
const progress = ref(0)
const passportExistsWarning = ref('')
const passportCheckTimeout = ref(null)
const passportCheckRequestId = ref(0)

const getPassportCheckPayload = (passportNo) => ({
  passport_no: passportNo,
  ignore_id: store.item?.id || null,
})

const checkPassportNumberExists = async (passportNo) => {
  if (!passportNo) {
    passportExistsWarning.value = ''
    return
  }

  const requestId = ++passportCheckRequestId.value

  try {
    const result = await checkPassportExists.mutateAsync(getPassportCheckPayload(passportNo))

    if (requestId !== passportCheckRequestId.value) {
      return
    }

    passportExistsWarning.value = result?.exists ? 'This passport number already exists.' : ''
  } catch (error) {
    if (requestId !== passportCheckRequestId.value) {
      return
    }

    passportExistsWarning.value = ''
    console.error('Passport number check failed:', error)
  }
}

// Zoom functionality
const zoomLevel = ref(100)
const hasPreview = computed(() => store.formData?.passport_preview || store.item?.passport_url)

const zoomIn = () => {
  if (zoomLevel.value < 200) zoomLevel.value += 10
}

const zoomOut = () => {
  if (zoomLevel.value > 50) zoomLevel.value -= 10
}

const resetZoom = () => {
  zoomLevel.value = 100
}

// Canvas refs
const passportCanvas = ref(null)
const passportCanvasExisting = ref(null)
const zoomCanvas = ref(null)
const zoomCanvasExisting = ref(null)

// Zoom lens state
const showZoomLens = ref(false)
const lensPosition = ref({ x: 0, y: 0 })
const lensSize = 250
const lensMagnification = 0.9

// Image state
const passportImage = ref(null)
const imageLoaded = ref(false)

// Load and draw image on canvas
const loadImage = (src) => {
  if (!src || src.length === 0) {
    console.warn('loadImage: No valid source provided')
    return
  }

  console.log('Loading passport image from:', src)

  const img = new Image()

  // Set crossOrigin for all cross-origin requests
  // This allows the image to be drawn on canvas even when CORS headers are present
  if (src.startsWith('http')) {
    img.crossOrigin = 'anonymous'
  }

  img.onload = () => {
    console.log('Passport image loaded successfully')
    passportImage.value = img
    imageLoaded.value = true
    nextTick(() => {
      redrawCanvas()
    })
  }
  img.onerror = (error) => {
    console.error('Failed to load passport image:', src, error)
    imageLoaded.value = false
    passportImage.value = null
  }
  img.src = src
}

// Watch for image changes
watch(
  () => store.formData?.passport_preview,
  (newVal) => {
    console.log('passport_preview changed:', newVal)
    if (newVal && newVal.length > 0) {
      // Check if it's an image by checking the data URL or file type
      const isImage =
        newVal?.startsWith('data:image/') || store.fileType?.passport_preview === 'image'
      if (isImage) {
        loadImage(newVal)
      }
    }
  }
)

watch(
  () => store.item?.passport_url,
  (newVal) => {
    console.log('passport_url changed:', newVal)
    if (newVal && newVal.length > 0) {
      // Wait a bit to ensure canvas ref is available
      setTimeout(() => {
        loadImage(newVal)
      }, 100)
    }
  },
  { immediate: true } // Load immediately if value exists
)

// Watch for store.item changes (in case it's set after mount)
watch(
  () => store.item,
  (newItem) => {
    console.log('store.item changed:', newItem)
    if (newItem?.passport_url && newItem.passport_url.length > 0) {
      setTimeout(() => {
        loadImage(newItem.passport_url)
      }, 100)
    }
  },
  { deep: true, immediate: true }
)

// Watch zoom level changes
watch(zoomLevel, () => {
  if (passportImage.value) {
    nextTick(() => {
      redrawCanvas()
    })
  }
})

// Redraw canvas with current zoom level
const redrawCanvas = () => {
  const canvas = passportCanvas.value || passportCanvasExisting.value
  if (!canvas) {
    console.warn('Canvas ref not available yet, retrying...')
    // Retry after a short delay
    setTimeout(() => {
      if (passportImage.value) {
        redrawCanvas()
      }
    }, 50)
    return
  }

  if (!passportImage.value) {
    console.warn('Image not loaded yet')
    return
  }

  console.log('Drawing on canvas:', canvas)

  const ctx = canvas.getContext('2d')
  const img = passportImage.value

  // Calculate dimensions based on zoom
  const scale = zoomLevel.value / 100
  const maxWidth = 350 // Max width in the preview container
  const aspectRatio = img.height / img.width

  let drawWidth = Math.min(img.width, maxWidth) * scale
  let drawHeight = drawWidth * aspectRatio

  // Set canvas dimensions
  canvas.width = drawWidth
  canvas.height = drawHeight

  // Clear and draw with high quality
  ctx.clearRect(0, 0, canvas.width, canvas.height)
  ctx.imageSmoothingEnabled = true
  ctx.imageSmoothingQuality = 'high'
  ctx.drawImage(img, 0, 0, drawWidth, drawHeight)

  console.log('Canvas drawn successfully:', drawWidth, 'x', drawHeight)
}

// Mouse hover zoom handlers
const handleMouseEnter = () => {
  if (imageLoaded.value) {
    showZoomLens.value = true
  }
}

const handleMouseLeave = () => {
  showZoomLens.value = false
}

const handleMouseMove = (event) => {
  if (!imageLoaded.value || !showZoomLens.value) return

  const canvas = event.target
  const rect = canvas.getBoundingClientRect()

  // Calculate mouse position relative to canvas
  const x = event.clientX - rect.left
  const y = event.clientY - rect.top

  // Update lens position (relative to viewport)
  lensPosition.value = {
    x: event.clientX - rect.left,
    y: event.clientY - rect.top,
  }

  // Draw zoomed portion
  drawZoomLens(x, y, canvas)
}

const drawZoomLens = (mouseX, mouseY, canvas) => {
  const zoomCtx = (zoomCanvas.value || zoomCanvasExisting.value)?.getContext('2d')
  if (!zoomCtx || !passportImage.value) {
    return
  }

  const img = passportImage.value
  const scale = zoomLevel.value / 100

  // Calculate the source area to zoom from
  const sourceSize = lensSize / lensMagnification / scale
  const halfSource = sourceSize / 2

  // Calculate source coordinates on the original image
  const scaleX = img.width / canvas.width
  const scaleY = img.height / canvas.height

  const sourceX = Math.max(0, Math.min(img.width - sourceSize, mouseX * scaleX - halfSource))
  const sourceY = Math.max(0, Math.min(img.height - sourceSize, mouseY * scaleY - halfSource))

  // Clear and draw zoomed portion with high quality
  zoomCtx.clearRect(0, 0, lensSize, lensSize)
  zoomCtx.imageSmoothingEnabled = true
  zoomCtx.imageSmoothingQuality = 'high'
  zoomCtx.drawImage(img, sourceX, sourceY, sourceSize, sourceSize, 0, 0, lensSize, lensSize)
}

// Initialize canvas on mount to ensure refs are available
onMounted(() => {
  console.log('Component mounted')
  console.log('store.item:', store.item)
  console.log('store.formData:', store.formData)

  // Redraw canvas if image already loaded before mount
  if (passportImage.value) {
    nextTick(() => {
      redrawCanvas()
    })
  }

  // Load new passport preview if available (prioritize new uploads)
  if (store.formData?.passport_preview && store.formData.passport_preview.length > 0) {
    const isImage =
      store.formData.passport_preview?.startsWith('data:image/') ||
      store.fileType?.passport_preview === 'image'
    if (isImage) {
      console.log('Loading new passport preview')
      loadImage(store.formData.passport_preview)
      return
    }
  }

  // Load existing passport from store.item if available
  if (store.item?.passport_url && store.item.passport_url.length > 0) {
    console.log('Loading existing passport from store.item')
    // Use setTimeout to ensure canvas ref is available
    setTimeout(() => {
      loadImage(store.item.passport_url)
    }, 150)
  }
})

// Watch loading to animate progress bar
watch(passportOCRLoading, (loading) => {
  if (loading) {
    progress.value = 0
    const interval = setInterval(() => {
      if (progress.value < 90) progress.value += 10
      else clearInterval(interval)
    }, 300)
  } else {
    progress.value = 100
    setTimeout(() => (progress.value = 0), 500)
  }
})

watch(
  () => store.formData?.passport_no,
  (newValue) => {
    const passportNo = (newValue || '').trim()

    if (passportCheckTimeout.value) {
      clearTimeout(passportCheckTimeout.value)
    }

    if (!passportNo) {
      passportExistsWarning.value = ''
      return
    }

    passportCheckTimeout.value = setTimeout(() => {
      checkPassportNumberExists(passportNo)
    }, 400)
  }
)

onBeforeUnmount(() => {
  if (passportCheckTimeout.value) {
    clearTimeout(passportCheckTimeout.value)
  }
})

const handlePassportOCR = async (file) => {
  if (!file) {
    alert('Please select a passport file first.')
    return
  }

  try {
    const result = await passportOCR.mutateAsync(file)

    if (result) {
      store.formData.passport_no = result.passport_number || ''
      store.formData.date_of_issue = formatDateToISO(result.date_of_issue) || ''
      store.formData.date_of_expiry = formatDateToISO(result.date_of_expiry) || ''
      store.formData.given_name = result.given_name || ''
      store.formData.sur_name = result.surname || ''
      store.formData.date_of_birth = formatDateToISO(result.date_of_birth) || ''
      store.formData.sex = formatSex(result.sex) || ''
      store.formData.nationality = result.nationality || ''
      store.formData.place_of_birth = result.place_of_birth || ''
      store.formData.mobile = result.emergency_contact_telephone || ''
      store.formData.father_name = result.father_name || ''
      store.formData.mother_name = result.mother_name || ''
      store.formData.address = result.permanent_address || ''
      store.formData.nid_no = result.personal_no || ''
      store.formData.spouse_name = result.spouse_name || null
    }
  } catch (err) {
    console.error('OCR failed', err)
    alert('Failed to extract passport info.')
  }
}
</script>

<style scoped>
@keyframes highlight-pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
    border-color: #22c55e;
  }
  50% {
    box-shadow: 0 0 0 8px rgba(34, 197, 94, 0);
    border-color: #22c55e;
  }
  100% {
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
    border-color: #d1d5db;
  }
}

:deep(.ocr-highlight input),
:deep(.ocr-highlight select) {
  animation: highlight-pulse 2s ease-out;
  border-color: #22c55e !important;
}

.passport-preview-container {
  position: sticky;
  top: 1rem;
}

.passport-preview-container::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.passport-preview-container::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.passport-preview-container::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.passport-preview-container::-webkit-scrollbar-thumb:hover {
  background: #555;
}

.zoom-lens {
  background: white;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
  z-index: 1000;
}

.zoom-lens canvas {
  display: block;
  width: 100%;
  height: 100%;
}
</style>
