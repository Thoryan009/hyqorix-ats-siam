import { defineStore } from 'pinia'
import { computed } from 'vue'
import { isPartyLedgerType, usePartyLedgerStore } from './partyLedgerStore'

export const useVendorLedgerStore = defineStore('vendorLedger', () => {
  const partyLedgerStore = usePartyLedgerStore()

  const entriesByVendor = computed(() => partyLedgerStore.entriesByParty.vendor ?? {})

  function getVendorLedgerEntries(vendorId) {
    return partyLedgerStore.getLedgerEntries('vendor', vendorId)
  }

  function addOpeningBalanceEntry(vendorId, payload) {
    return partyLedgerStore.addOpeningBalanceEntry('vendor', vendorId, payload)
  }

  function addBillEntry(vendorId, payload) {
    return partyLedgerStore.addBillEntry('vendor', vendorId, payload)
  }

  function addPaymentEntry(vendorId, payload) {
    return partyLedgerStore.addPaymentEntry('vendor', vendorId, payload)
  }

  function updateBillEntryStatus(vendorId, billId, billStatus, remarks = '') {
    return partyLedgerStore.updateBillEntryStatus('vendor', vendorId, billId, billStatus, remarks)
  }

  function removeBillEntry(vendorId, billId) {
    return partyLedgerStore.removeBillEntry('vendor', vendorId, billId)
  }

  return {
    entriesByVendor,
    getVendorLedgerEntries,
    addOpeningBalanceEntry,
    addBillEntry,
    addPaymentEntry,
    updateBillEntryStatus,
    removeBillEntry,
  }
})

export { isPartyLedgerType }
