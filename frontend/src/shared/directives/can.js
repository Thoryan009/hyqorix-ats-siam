import { useAuthStore } from '@/modules/auth/store/authStore'

export default {
  mounted(el, binding) {
    const auth = useAuthStore()

    const value = binding.value

    // Support: string OR array
    const hasPermission = Array.isArray(value)
      ? auth.canAny(value)
      : auth.can(value)

    if (!hasPermission) {
      el.parentNode && el.parentNode.removeChild(el)
    }
  },
}
