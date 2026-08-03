
// Named export
export const getStatusColor = (status) => {
  const colors = {
    offer_extended: 'bg-blue-100 text-blue-800',
    visa_authorization: 'bg-purple-100 text-purple-800',
    medical_test: 'bg-green-100 text-green-800',
    police_clearance: 'bg-yellow-100 text-yellow-800',
    trade_test: 'bg-orange-100 text-orange-800',
    biometric_enrollment: 'bg-pink-100 text-pink-800',
    embassy_submission: 'bg-indigo-100 text-indigo-800',
    bmet_training: 'bg-teal-100 text-teal-800',
    bmet_biometric_enrollment: 'bg-cyan-100 text-cyan-800',
    immigration_clearance: 'bg-lime-100 text-lime-800',
    pta_request: 'bg-amber-100 text-amber-800',
    onboarding: 'bg-emerald-100 text-emerald-800',
    tra_process: 'bg-violet-100 text-violet-800',
    rejected: 'bg-red-100 text-red-800',
    declined: 'bg-yellow-100 text-yellow-800',
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}
