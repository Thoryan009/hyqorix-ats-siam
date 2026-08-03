

import axios from 'axios'

const BASE_URL = import.meta.env.VITE_APP_API_URL

export const fetchAll = async () => {
  const response = await axios.get(`${BASE_URL}/public-settings`)
  return response.data

}
