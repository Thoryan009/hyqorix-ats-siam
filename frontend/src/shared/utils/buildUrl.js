/**
 * Build a URL with query parameters
 * @param {string} baseUrl - Base API endpoint
 * @param {object} paramsObj - Key-value pairs of query parameters
 * @returns {string} Full URL with encoded query parameters
 */
export function buildUrl(baseUrl, paramsObj = {}) {
  const params = new URLSearchParams()

  Object.entries(paramsObj).forEach(([key, value]) => {
    if (value === undefined || value === null || value === '') return

    if (Array.isArray(value)) {
      value
        .filter((item) => item !== undefined && item !== null && item !== '')
        .forEach((item) => params.append(`${key}[]`, item))
      return
    }

    params.append(key, value)
  })

  const query = params.toString()
  return query ? `${baseUrl}?${query}` : baseUrl
}
