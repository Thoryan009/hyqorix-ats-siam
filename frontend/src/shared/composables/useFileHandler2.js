import { ref } from "vue"

export function useFileHandler(formData) {
  const fileName = ref({})
  const fileError = ref({})
  const fileType = ref({})

  const handleFileChange = (event, fileKey, previewKey) => {
    const file = event.target.files[0]

    if (!file) {
      fileName.value[fileKey] = ''
      return
    }

    const maxSize = 10 * 1024 * 1024
    if (file.size > maxSize) {
      fileError.value[fileKey] =
        `File size exceeds 10MB limit. Selected file size: ${(file.size / (1024 * 1024)).toFixed(2)} MB.`
      event.target.value = null
      return
    }

    // ✅ FIX HERE
    formData.value[fileKey] = file
    fileName.value[fileKey] = file.name
    fileType.value[previewKey] =
      file.type === 'application/pdf' ? 'pdf' : 'image'

    if (previewKey) {
      formData.value[previewKey] = URL.createObjectURL(file)
    }

    event.target.value = null
  }

  const cancelImage = (cancelKey) => {
    const previewKey = cancelKey + '_preview'
    const pathKey = cancelKey + '_path'

    // ✅ FIX HERE
    formData.value[pathKey] = null
    formData.value[previewKey] = null

    fileName.value[pathKey] = ''
  }

  return { handleFileChange, fileName, fileError, cancelImage, fileType }
}
