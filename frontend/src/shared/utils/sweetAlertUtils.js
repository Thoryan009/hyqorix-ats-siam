// utils/sweetAlertUtils.js
import Swal from 'sweetalert2'

export function showConfirmDialog(customOptions = {}) {
  return Swal.fire({
    title: 'Are you sure?',
    text: 'This action cannot be undone!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#22C55E',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel',
    ...customOptions, // Allows overriding default options
  })
}
