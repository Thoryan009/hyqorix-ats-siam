import { initialAccounts } from './accountData'

export { computeLedgerAmounts, computeLedgerBalances, filterLedgerByDate } from './agentLedgerData'

function buildOpeningEntry(account) {
  const balance = Number(account.current_balance) || 0
  if (balance <= 0) return null

  return {
    id: account.id,
    date: String(account.created_at || '').slice(0, 10) || '2026-01-01',
    particular: 'Opening Balance',
    voucher_no: `OB-${String(account.id).padStart(3, '0')}/26`,
    demand_letter: '',
    job: '',
    client_name: '',
    dr_amount: 0,
    discount: 0,
    cr_amount: balance,
    payment_method: '',
    remarks: 'Opening balance forwarded',
  }
}

export const accountLedgerEntries = initialAccounts.reduce((acc, account) => {
  const entry = buildOpeningEntry(account)
  if (entry) {
    acc[String(account.id)] = [entry]
  }
  return acc
}, {})
