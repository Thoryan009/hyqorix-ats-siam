import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { backupDBFn, changePasswordFn, updateData , updateEmbassyData} from '../services/settingService'
import { toast } from '@/shared/config/toastConfig'

export function useSettingMutations(moduleName, options = {}) {
  const queryClient = useQueryClient()

  const handleSuccess = (data, variables) => {
    toast.success(`${moduleName} updated successfully`)
    queryClient.invalidateQueries(['settings'])
    queryClient.invalidateQueries(['public-settings'])
    options.onSuccess?.(data, variables)
  }

  const handleError = (error) => {
    console.error(error)
    const validationMessage = error?.errors && Object.values(error.errors).flat()[0]
    toast.error(validationMessage || error?.message || 'Request failed')
    options.onError?.(error)
  }

  const update = useMutation({
    mutationFn: updateData,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const updateEmbassy = useMutation({
    mutationFn: updateEmbassyData,
    onSuccess: handleSuccess,
    onError: handleError,
  })



  const changePassword = useMutation({
    mutationFn: changePasswordFn,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const backupDB = useMutation({
    mutationFn: backupDBFn,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  return {
    update,
    updateLoading: update.isPending,
    changePassword,
    changePasswordLoading: changePassword.isPending,
    updateEmbassy,
    updateEmbassyLoading: updateEmbassy.isPending,
    backupDB,
    backupDBLoading: backupDB.isPending,
  }
}
