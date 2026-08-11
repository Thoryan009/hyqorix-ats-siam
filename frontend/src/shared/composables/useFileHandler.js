import { ref } from 'vue'

export function useFileHandler(formData) {
  const fileName = ref({})
  const fileError = ref({})
  const fileType = ref({}) // 'image' or 'pdf'

  const handleFileChange = (event, fileKey, previewKey, maxSize = 400 * 1024) => {
    const file = event.target.files[0]

    if (!file) {
      fileName.value[fileKey] = ''
      return
    }

    if (file.size > maxSize) {
      fileError.value[fileKey] =
        `File must be ${Math.round(maxSize / 1024)} KB or smaller. Selected file is ${(file.size / 1024).toFixed(0)} KB.`
      fileName.value[fileKey] = ''
      event.target.value = null
      return
    }

    fileError.value[fileKey] = ''

    // Assign file directly
    formData[fileKey] = file
    fileName.value[fileKey] = file.name
    fileType.value[previewKey] = file.type == 'application/pdf' ? 'pdf' : 'image'
    // Handle preview
    if (previewKey) {
      formData[previewKey] = URL.createObjectURL(file)
    }
    // ❗ CRITICAL FIX: reset input so same file triggers change
    event.target.value = null
  }

  const cancelImage = (cancelKey) => {
    const fileKey = cancelKey.concat('_file')
    const previewKey = cancelKey.concat('_preview')
    const pathKey = cancelKey.concat('_path')
    formData[fileKey] = null
    formData[previewKey] = null
    formData[pathKey] = null
    fileName.value[pathKey] = ''
    fileName.value[fileKey] = ''
  }

  return { handleFileChange, fileName, fileError, cancelImage, fileType }
}
