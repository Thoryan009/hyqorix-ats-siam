// composables/useCSVParser.js
import { ref } from 'vue'

export function useCSVParser() {
  const jsonData = ref([])
  const error = ref(null)

  const parseCSV = (csvText) => {
    try {
      error.value = null
      const lines = csvText.trim().split('\n')
      if (lines.length < 2) {
        throw new Error('CSV file must contain headers and at least one data row')
      }

      const parseCSVLine = (line) => {
        const result = []
        let current = ''
        let inQuotes = false

        for (let i = 0; i < line.length; i++) {
          const char = line[i]
          const nextChar = line[i + 1]

          if (char === '"') {
            if (inQuotes && nextChar === '"') {
              current += '"'
              i++
            } else {
              inQuotes = !inQuotes
            }
          } else if (char === ',' && !inQuotes) {
            result.push(current.trim())
            current = ''
          } else {
            current += char
          }
        }

        result.push(current.trim())
        return result
      }

      const headers = parseCSVLine(lines[0])
      const data = []

      for (let i = 1; i < lines.length; i++) {
        if (!lines[i].trim()) continue

        const values = parseCSVLine(lines[i])
        const row = {}

        headers.forEach((header, index) => {
          let value = values[index] || ''
          if (value.startsWith('"') && value.endsWith('"')) {
            value = value.slice(1, -1)
          }
          row[header] = value
        })

        Object.keys(row).forEach((key) => {
          if (row[key] === '' || row[key] === 'null') {
            row[key] = null
          }
        })

        data.push(row)
      }

      jsonData.value = data
      return data
    } catch (err) {
      error.value = err.message
      jsonData.value = []
      return []
    }
  }

  return {
    jsonData,
    error,
    parseCSV
  }
}
