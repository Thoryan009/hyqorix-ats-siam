import { useToast } from 'vue-toastification'

export const toastOptions = {
  position: 'bottom-right',
  timeout: 2254,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
  draggable: true,
  draggablePercent: 0.32,
  showCloseButtonOnHover: true,
  hideProgressBar: false,
  closeButton: 'button',
  icon: true,
  rtl: false,
}

// Optionally, you can export a function to use the toast instance
export const toast = useToast()
