import { useDemandLetterStore } from '../store/demandLetterStore'

export {
  CLIENT_RECRUITMENT_COST_CATEGORY_CODE,
  CLIENT_RECRUITMENT_COST_CATEGORY_CODE as CLIENT_RECRUITMENT_COST_CATEGORY_ID,
} from './expenseCategoryCodes'

export { formatDemandLetterLabel } from '../utils/demandLetterMapper'

export function getDemandLetter(id) {
  return useDemandLetterStore().getDemandLetter(id)
}

export function getDemandLetterOptions() {
  return useDemandLetterStore().getDemandLetterSelectOptions()
}

export function resolveDemandLetterMeta(demandLetterId) {
  return useDemandLetterStore().resolveDemandLetterMeta(demandLetterId)
}
