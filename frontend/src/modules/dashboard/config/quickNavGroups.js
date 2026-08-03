import { navGroups } from '@/shared/layouts/navGroups'


function getNavItemByPath(path) {
  for (const group of navGroups) {
    const item = group.items.find((entry) => entry.path === path)
    if (item) return { ...item }
  }
  return null
}

export const dashboardQuickNavGroups = [
  {
    label: 'dashboard.quick_nav.recruitment',
    icon: 'fa fa-users',
    description: 'dashboard.quick_nav.recruitment_description',
    paths: ['/applications'],
  },
  {
    label: 'dashboard.quick_nav.ats_workflow',
    icon: 'fa fa-list',
    description: 'dashboard.quick_nav.ats_workflow_description',
    paths: ['/jobs/ats'],
  },
  {
    label: 'dashboard.quick_nav.ksa_visa_processing',
    icon: 'fa fa-building',
    description: 'dashboard.quick_nav.ksa_visa_processing_description',
    paths: ['/applications/embassy-submission',  '/tasheer-appointment-reports',],
    labels: {
      '/applications/embassy-submission': 'dashboard.quick_nav.ksa_visa_processing',
      '/tasheer-appointment-reports': 'dashboard.quick_nav.tasheer_appointment',
    },
  },
  {
    label: 'dashboard.quick_nav.reports_analytics',
    icon: 'fa fa-bar-chart',
    description: 'dashboard.quick_nav.reports_analytics_description',
    paths: [
      '/ats-reports/all',
      '/application-reports/application',
      '/expiry-reports',
      '/ats-summary-report',
    ],
    labels: {
      '/ats-reports/all': 'dashboard.quick_nav.ats_report',
      '/application-reports/application': 'dashboard.quick_nav.application_report',
      '/expiry-reports': 'dashboard.quick_nav.process_expiry_report',
      '/ats-summary-report': 'dashboard.quick_nav.ats_summary_report',
    },
  },
]

export function resolveQuickNavGroups(can) {
  return dashboardQuickNavGroups
    .map((group) => {
      const items = group.paths
        .map((path) => {
          const navItem = getNavItemByPath(path)
          if (!navItem) return null
          if (navItem.permission && !can(navItem.permission)) return null

          return {
            ...navItem,
            name: group.labels?.[path] ?? navItem.name,
          }
        })
        .filter(Boolean)

      return { ...group, items }
    })
    .filter((group) => group.items.length > 0)
}
