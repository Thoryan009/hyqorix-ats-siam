import { createI18n } from 'vue-i18n'
import shared_en from './shared/en'
import shared_bn from './shared/bn'
import home_en from '../modules/home/localization/en'
import home_bn from '../modules/home/localization/bn'
import dashboard_en from '../modules/dashboard/localization/en'
import dashboard_bn from '../modules/dashboard/localization/bn'
import country_en from '../modules/country/localization/en'
import country_bn from '../modules/country/localization/bn'
import client_en from '../modules/client/localization/en'
import client_bn from '../modules/client/localization/bn'
import vendor_en from '../modules/vendor/localization/en'
import vendor_bn from '../modules/vendor/localization/bn'
import agent_en from '../modules/agent/localization/en'
import agent_bn from '../modules/agent/localization/bn'
import principal_en from '../modules/principal/localization/en'
import principal_bn from '../modules/principal/localization/bn'
import job_en from '../modules/job/localization/en'
import job_bn from '../modules/job/localization/bn'
import work_order_en from '../modules/work_order/localization/en'
import work_order_bn from '../modules/work_order/localization/bn'
import passport_handover_en from '../modules/passport_handover/localization/en'
import passport_handover_bn from '../modules/passport_handover/localization/bn'
import application_en from '../modules/application/localization/en'
import application_bn from '../modules/application/localization/bn'
import tasheer_en from '../modules/reports/tasheer_appointment_reports/localization/en'
import tasheer_bn from '../modules/reports/tasheer_appointment_reports/localization/bn'
import setting_en from '../modules/setting/localization/en'
import setting_bn from '../modules/setting/localization/bn'
import access_control_en from '../modules/access-control/localization/en'
import access_control_bn from '../modules/access-control/localization/bn'
import ats_reports_en from '../modules/reports/localization/en'
import ats_reports_bn from '../modules/reports/localization/bn'
import accounts_en from '../modules/accounts/localization/en'
import accounts_bn from '../modules/accounts/localization/bn'
import parties_en from '../modules/parties/localization/en'
import parties_bn from '../modules/parties/localization/bn'
const savedLang = localStorage.getItem(import.meta.env.VITE_LANG) || 'en'

const i18n = createI18n({
  legacy: false,
  locale: savedLang,
  fallbackLocale: 'en',
  messages: {
    en: {
    ...shared_en,
    ...home_en,
    ...dashboard_en,
    ...country_en,
    ...client_en,
    ...vendor_en,
    ...agent_en,
    ...principal_en,
    ...job_en,
    ...work_order_en,
    ...passport_handover_en,
    ...application_en,
    ...tasheer_en,
    ...ats_reports_en,
    ...setting_en,
    ...access_control_en,
    ...accounts_en,
    ...parties_en,
  },
  bn: {
    ...shared_bn,
    ...home_bn,
    ...dashboard_bn,
    ...country_bn,
    ...client_bn,
    ...vendor_bn,
    ...agent_bn,
    ...principal_bn,
    ...job_bn,
    ...work_order_bn,
    ...passport_handover_bn,
    ...application_bn,
    ...tasheer_bn,
    ...ats_reports_bn,
    ...setting_bn,
    ...access_control_bn,
    ...accounts_bn,
    ...parties_bn,
  },
  },
})

export default i18n
