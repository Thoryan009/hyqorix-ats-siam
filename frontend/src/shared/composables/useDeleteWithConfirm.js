import { showConfirmDialog } from '@/shared/utils/sweetAlertUtils'
import { useTranslate } from '@/shared/composables/useTranslate'

export function useDeleteWithConfirm(removeMutation) {
  const { t } = useTranslate('shared')
  const confirmDelete = async (id, customText = null) => {
    const result = await showConfirmDialog({
      text: customText || t('shared.messages.delete_confirmation'),
    })

    if (result.isConfirmed) {
      await removeMutation.mutateAsync(id)
    }
  }

  return { confirmDelete }
}
