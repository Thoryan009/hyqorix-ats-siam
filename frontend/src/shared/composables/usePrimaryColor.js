import { computed } from 'vue'
import { useSettingsQuery } from '@/modules/home/queries/useSettingsQuery'
import { DEFAULT_PRIMARY_COLOR, isValidHexColor } from '@/shared/utils/themeColor'

export function usePrimaryColor() {
  const { data } = useSettingsQuery()

  return computed(() => {
    const color = data.value?.data?.primary_color
    return isValidHexColor(color) ? color : DEFAULT_PRIMARY_COLOR
  })
}
