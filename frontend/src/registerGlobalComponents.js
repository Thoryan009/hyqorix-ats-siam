// registerGlobalComponents.js

import BaseModal from '@/shared/components/base/BaseModal.vue'
import PageTitle from '@/shared/components/ui/PageTitle.vue'
import BaseButton from '@/shared/components/base/BaseButton.vue'
import BaseTable from '@/shared/components/base/BaseTable.vue'
import BaseLabel from '@/shared/components/base/BaseLabel.vue'
import BaseInput from '@/shared/components/base/BaseInput.vue'
import BasePasswordInput from '@/shared/components/base/BasePasswordInput.vue'
import BaseSelect from '@/shared/components/base/BaseSelect.vue'
import BaseSearchSelect from '@/shared/components/base/BaseSearchSelect.vue'
import BaseFileInput from '@/shared/components/base/BaseFileInput.vue'
import BaseImagePreview from './shared/components/base/BaseImagePreview.vue'
import BasePagination from './shared/components/base/BasePagination.vue'
import BaseTextArea from './shared/components/base/BaseTextArea.vue'
import BaseTableButton from './shared/components/base/BaseTableButton.vue'
import BaseForm from './shared/components/base/BaseForm.vue'
import BaseTableSkeleton from './shared/components/base/BaseTableSkeleton.vue'
import BasePrintTable from './shared/components/base/BasePrintTable.vue'

export function registerGlobalComponents(app) {
  const components = {
    BaseModal,
    PageTitle,
    BaseButton,
    BaseTable,
    BaseLabel,
    BaseInput,
    BasePasswordInput,
    BaseSelect,
    BaseSearchSelect,
    BaseFileInput,
    BaseImagePreview,
    BasePagination,
    BaseTextArea,
    BaseTableButton,
    BaseForm,
    BaseTableSkeleton,
    BasePrintTable
  }

  // Register all components globally in one loop
  Object.entries(components).forEach(([name, component]) => {
    app.component(name, component)
  })
}
