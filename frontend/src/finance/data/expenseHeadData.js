const directCostHeads = [
  'Medical',
  'Police Clearance',
  'Air Ticket',
  'MOFA',
  'Training',
  'Visa',
  'Manpower',
  'Commission',
  'Other',
]

const directCostHeadPrices = {
  Medical: 3500,
  'Police Clearance': 1200,
  'Air Ticket': 48500,
  MOFA: 2800,
  Training: 7500,
  Visa: 16500,
  Manpower: 22000,
  Commission: 15000,
  Other: 4500,
}

const clientRecruitmentCostHeads = [
  'Advertisement',
  'Interview Venue',
  'Trade Test',
  'Transportation',
  'Entertainment',
  'Other',
]

const operatingCostHeads = [
  'Salary',
  'Rent',
  'Electricity',
  'Internet',
  'Stationery',
  'Maintenance',
  'Tax',
  'VAT',
  'Bank Interest',
  'Other',
]

function buildHeads(categoryId, names, startId, priceMap = {}) {
  return names.map((name, index) => ({
    id: startId + index,
    category_id: categoryId,
    name,
    base_price: priceMap[name] ?? 0,
    status: 'Active',
  }))
}

export const initialExpenseHeads = [
  ...buildHeads(1, directCostHeads, 1, directCostHeadPrices),
  ...buildHeads(2, clientRecruitmentCostHeads, 10),
  ...buildHeads(3, operatingCostHeads, 16),
]

export function normalizeExpenseHead(head) {
  return {
    ...head,
    category_id: Number(head.category_id),
    base_price: Number(head.base_price ?? 0),
    status: head.status ?? 'Active',
    linked_accounts: Array.isArray(head.linked_accounts)
      ? head.linked_accounts.map((link) => ({
          account_category: link.account_category ?? '',
        }))
      : [],
  }
}

export function loadExpenseHeads(saved) {
  const heads =
    !Array.isArray(saved) || saved.length === 0
      ? structuredClone(initialExpenseHeads)
      : saved.map((head) => normalizeExpenseHead(head))

  return heads.map((head) => {
    if (
      head.category_id === 1 &&
      Number(head.base_price) === 0 &&
      directCostHeadPrices[head.name] != null
    ) {
      return {
        ...head,
        base_price: directCostHeadPrices[head.name],
      }
    }

    return head
  })
}
