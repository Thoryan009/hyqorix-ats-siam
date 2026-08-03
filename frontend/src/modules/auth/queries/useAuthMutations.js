import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { loginUser, logoutUser, refreshData } from '../services/authService'
import { toast } from '@/shared/config/toastConfig'

export function useAuthMutations(moduleName, options = {}) {
  const queryClient = useQueryClient()

  const handleSuccess = (data, variables) => {
    toast.success(`${moduleName} operation successful`)
    queryClient.invalidateQueries(['auth'])
    options.onSuccess?.(data, variables)
  }

  const handleRefreshSuccess = () => {
    toast.success(`Data refreshed successfully`)

    // Invalidate ALL queries
    queryClient.invalidateQueries({
      queryKey: [],
      exact: false,
    })

  }

  const handleError = (error) => {
    console.error(error)
    toast.error(`Request Failed: ${error?.message || 'Unknown error'}`)
    options.onError?.(error)
  }

  const login = useMutation({
    mutationFn: loginUser,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const refresh = useMutation({
    mutationFn: refreshData,
    onSuccess: handleRefreshSuccess,
    onError: handleError,
  })

 const logout = useMutation({
  mutationFn: logoutUser,
  onSuccess: (data) => {
    toast.success('Logout successful')

    queryClient.clear() // remove all cached queries

    options.onSuccess?.(data)
  },
  onError: handleError,
})

  return {
    login,
    loginLoading: login.isPending, // ✅ Vue Query v5
    loginError: login.error,
    logout,
    logoutLoading: logout.isPending, // ✅ Vue Query v5
    logoutError: logout.error,
    refresh,
    refreshLoading: refresh.isPending, // ✅ Vue Query v5
  }
}
