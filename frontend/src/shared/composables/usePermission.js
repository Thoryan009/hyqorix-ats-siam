import { useAuthStore } from "@/modules/auth/store/authStore"

export function usePermission() {
  const auth = useAuthStore()

  return {
    can: auth.can,
    canAny: auth.canAny,
  }
}
