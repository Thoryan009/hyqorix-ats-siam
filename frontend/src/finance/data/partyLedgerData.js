import { staffAccounts } from './staffAccountData'
import { clientAccounts } from './clientAccountData'
import { principalAccounts } from './principalAccountData'
import { vendorAccounts } from './vendorAccountData'

export { computeLedgerBalances, filterLedgerByDate } from './agentLedgerData'

export const partyLedgerTypes = ['vendor', 'staff', 'client', 'principal']

function createOpeningBalanceEntry(amount) {
  return {
    id: 1,
    date: '2026-01-01',
    particular: 'Opening Balance',
    voucher_no: 'OB-001/26',
    demand_letter: '',
    job: '',
    client_name: '',
    dr_amount: 0,
    discount: 0,
    cr_amount: Number(amount) || 0,
    payment_method: '',
    balance: Number(amount) || 0,
    remarks: 'Opening balance forwarded',
    bill_id: null,
    bill_status: '',
  }
}

export function buildPartyLedgerSeed(accounts = []) {
  return accounts.reduce((acc, account) => {
    acc[String(account.id)] = [createOpeningBalanceEntry(account.opening_balance ?? account.balance ?? 0)]
    return acc
  }, {})
}

export const partyLedgerSeedByType = {
  vendor: buildPartyLedgerSeed(vendorAccounts),
  staff: buildPartyLedgerSeed(staffAccounts),
  client: buildPartyLedgerSeed(clientAccounts),
  principal: buildPartyLedgerSeed(principalAccounts),
}

// Legacy vendor-only seed (kept for migration fallback)
export { vendorLedgerEntries } from './vendorLedgerData'
