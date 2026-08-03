// helpers/file.js
export const clearFileInput = (fileRef, inputId) => {
  if (fileRef) fileRef.value = null

  const input = document.getElementById(inputId)
  if (input) input.value = ''
}

export const handleCsvFileSelect = (fileRef) => (event) => {
  const file = event.target.files[0]

  if (file && file.type === 'text/csv') {
    if (fileRef) fileRef.value = file
  } else {
    alert('Please select a valid CSV file')
    event.target.value = '' // clear invalid selection
    if (fileRef) fileRef.value = null
  }
}
