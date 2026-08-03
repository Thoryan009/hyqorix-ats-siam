import { getBillableAgents } from './agentAccountData'

export const companyInfo = {
  name: 'Merchant Overseas RL 1164',
  address: '29/B, Toynobee Circular Road, Motijheel, Dhaka-1000',
  phone: 'Tel: +880 2 8392835-41 | Mobile: +880 1729-004444',
  web: 'Web: www.norqeltechnologies.com | E-mail: info@norqeltechnologies.com',
}

export const currencyOptions = [{ id: 'BDT', name: 'BDT (৳)' }]

export const agents = getBillableAgents()

function buildCandidate(base, salePrice, index = 0) {
  const variance = (index % 4) * 75
  const costs = {
    medical_fee: 400 + variance,
    mofa_fee: 250 + (index % 2) * 25,
    takamol_fee: 150,
    visa_cost: 1200 + variance,
    principal_cost: 1800 + variance,
    air_ticket_cost: 900 + (index % 3) * 50,
  }
  const total_cost = Object.values(costs).reduce((sum, amount) => sum + amount, 0)
  const profit = salePrice - total_cost

  return {
    ...base,
    costs,
    total_cost,
    sale_price: salePrice,
    charge: salePrice,
    profit,
  }
}

export const jobs = [
  {
    id: 1,
    job_code: 'JOB-020',
    job_title: 'Swimmer - Male',
    country: 'KSA',
    demand_letter: 'DL-010',
    client: 'SRACO',
    total_demand: 25,
    agent_id: 1,
    charge: 5000,
    candidates: [
      buildCandidate({ id: 1, passport_no: 'A01377097', candidate_name: 'SHAHORIAR JAMAN SHIKAT', status: 'Selected', joining_date: '' }, 5000, 0),
      buildCandidate({ id: 2, passport_no: 'A01377098', candidate_name: 'MD RASHEDUL ISLAM', status: 'Selected', joining_date: '' }, 5000, 1),
      buildCandidate({ id: 3, passport_no: 'A01377099', candidate_name: 'MD ALAMGIR HOSSAIN', status: 'Selected', joining_date: '' }, 5000, 2),
      buildCandidate({ id: 4, passport_no: 'A01377100', candidate_name: 'MD SHARIFUL ISLAM', status: 'Selected', joining_date: '' }, 5000, 3),
      buildCandidate({ id: 5, passport_no: 'A01377101', candidate_name: 'MD JAHANGIR ALAM', status: 'Selected', joining_date: '' }, 5000, 4),
      buildCandidate({ id: 6, passport_no: 'A01377102', candidate_name: 'MD KAMRUZZAMAN', status: 'Selected', joining_date: '' }, 5000, 5),
      buildCandidate({ id: 7, passport_no: 'A01377103', candidate_name: 'MD ABUL KALAM', status: 'Selected', joining_date: '' }, 5000, 6),
      buildCandidate({ id: 8, passport_no: 'A01377104', candidate_name: 'MD SAIFUL ISLAM', status: 'Selected', joining_date: '' }, 5000, 7),
      buildCandidate({ id: 9, passport_no: 'A01377105', candidate_name: 'MD RUBEL HOSSAIN', status: 'Selected', joining_date: '' }, 5000, 8),
      buildCandidate({ id: 10, passport_no: 'A01377106', candidate_name: 'MD SOHEL RANA', status: 'Selected', joining_date: '' }, 5000, 9),
    ],
  },
  {
    id: 2,
    job_code: 'JOB-015',
    job_title: 'Driver - Light Vehicle',
    country: 'KSA',
    demand_letter: 'DL-008',
    client: 'Al Rajhi Group',
    total_demand: 15,
    agent_id: 1,
    charge: 4500,
    candidates: [
      buildCandidate({ id: 11, passport_no: 'B01456001', candidate_name: 'MD ANWAR HOSSAIN', status: 'Selected', joining_date: '2025-04-10' }, 4500, 0),
      buildCandidate({ id: 12, passport_no: 'B01456002', candidate_name: 'MD HABIBUR RAHMAN', status: 'Selected', joining_date: '2025-04-12' }, 4500, 1),
      buildCandidate({ id: 13, passport_no: 'B01456003', candidate_name: 'MD NURUL ISLAM', status: 'Selected', joining_date: '' }, 4500, 2),
    ],
  },
  {
    id: 3,
    job_code: 'JOB-011',
    job_title: 'Cleaner - Male',
    country: 'UAE',
    demand_letter: 'DL-005',
    client: 'Emirates Facility',
    total_demand: 20,
    agent_id: 2,
    charge: 3500,
    candidates: [
      buildCandidate({ id: 14, passport_no: 'C01567001', candidate_name: 'MD RAKIB HASAN', status: 'Selected', joining_date: '' }, 3500, 0),
      buildCandidate({ id: 15, passport_no: 'C01567002', candidate_name: 'MD TANVIR AHMED', status: 'Selected', joining_date: '' }, 3500, 1),
    ],
  },
]

export const initialAgentBills = [
  {
    id: 1,
    bill_no: 'A001/26',
    bill_date: '2026-03-20',
    agent_code: 'AGT001',
    agent_name: 'Al Falah Recruitment',
    job_code: 'JOB-020',
    job_title: 'Swimmer - Male',
    candidate_count: 10,
    total_amount: 50000,
    paid_amount: 50000,
    currency: 'BDT',
    status: 'Paid',
  },
  {
    id: 2,
    bill_no: 'A002/26',
    bill_date: '2026-03-20',
    agent_code: 'AGT001',
    agent_name: 'Al Falah Recruitment',
    job_code: 'JOB-020',
    job_title: 'Swimmer - Male',
    candidate_count: 10,
    total_amount: 50000,
    paid_amount: 0,
    currency: 'BDT',
    status: 'Unpaid',
  },
  {
    id: 3,
    bill_no: 'A003/26',
    bill_date: '2026-03-20',
    agent_code: 'AGT001',
    agent_name: 'Al Falah Recruitment',
    job_code: 'JOB-020',
    job_title: 'Swimmer - Male',
    candidate_count: 10,
    total_amount: 50000,
    paid_amount: 25000,
    currency: 'BDT',
    status: 'Partial',
  },
  {
    id: 4,
    bill_no: 'A004/26',
    bill_date: '2026-03-20',
    agent_code: 'AGT001',
    agent_name: 'Al Falah Recruitment',
    job_code: 'JOB-020',
    job_title: 'Swimmer - Male',
    candidate_count: 10,
    total_amount: 50000,
    paid_amount: 0,
    currency: 'BDT',
    status: 'Unpaid',
  },
  {
    id: 5,
    bill_no: 'A005/26',
    bill_date: '2026-03-15',
    agent_code: 'AGT001',
    agent_name: 'Al Falah Recruitment',
    job_code: 'JOB-015',
    job_title: 'Driver - Light Vehicle',
    candidate_count: 3,
    total_amount: 13500,
    paid_amount: 13500,
    currency: 'BDT',
    status: 'Paid',
  },
  {
    id: 6,
    bill_no: 'A006/26',
    bill_date: '2026-03-15',
    agent_code: 'AGT001',
    agent_name: 'Al Falah Recruitment',
    job_code: 'JOB-015',
    job_title: 'Driver - Light Vehicle',
    candidate_count: 3,
    total_amount: 13500,
    paid_amount: 5000,
    currency: 'BDT',
    status: 'Partial',
  },
  {
    id: 7,
    bill_no: 'A007/26',
    bill_date: '2026-03-15',
    agent_code: 'AGT001',
    agent_name: 'Al Falah Recruitment',
    job_code: 'JOB-015',
    job_title: 'Driver - Light Vehicle',
    candidate_count: 3,
    total_amount: 13500,
    paid_amount: 0,
    currency: 'BDT',
    status: 'Unpaid',
  },
  {
    id: 8,
    bill_no: 'A008/26',
    bill_date: '2026-03-10',
    agent_code: 'AGT002',
    agent_name: 'Rahman Overseas Services',
    job_code: 'JOB-011',
    job_title: 'Cleaner - Male',
    candidate_count: 2,
    total_amount: 7000,
    paid_amount: 0,
    currency: 'BDT',
    status: 'Unpaid',
  },
]

export function getJobsByAgent(agentId) {
  return jobs.filter((job) => job.agent_id === Number(agentId))
}

export function getAllJobs() {
  return jobs
}

export function getJobById(jobId) {
  return jobs.find((job) => job.id === Number(jobId)) ?? null
}

export function getAgentById(agentId, accountList) {
  const billableAgents = accountList ? getBillableAgents(accountList) : agents
  return billableAgents.find((agent) => agent.id === Number(agentId)) ?? null
}

export function formatJobOption(job) {
  return {
    id: job.id,
    name: `${job.job_code} - ${job.job_title}`,
  }
}

export function formatAgentOption(agent) {
  return {
    id: agent.id,
    name: `${agent.agent_code} - ${agent.agent_name}`,
  }
}

export function getBillPreviewContext(bill) {
  const agent = agents.find((item) => item.agent_code === bill.agent_code) ?? {
    agent_code: bill.agent_code,
    agent_name: bill.agent_name,
    phone: '-',
    email: '-',
  }

  const matchedJob = jobs.find((item) => item.job_code === bill.job_code)
  const job = matchedJob
    ? {
        job_code: matchedJob.job_code,
        job_title: matchedJob.job_title,
        client: matchedJob.client,
      }
    : {
        job_code: bill.job_code,
        job_title: bill.job_title,
        client: '-',
      }

  const selectedCandidates = bill.candidates?.length
    ? bill.candidates.map((candidate) => ({
        ...candidate,
        costs: { ...candidate.costs },
      }))
    : (matchedJob?.candidates ?? [])
        .slice(0, bill.candidate_count)
        .map((candidate) => ({
          ...candidate,
          costs: { ...candidate.costs },
        }))

  return {
    agent,
    job,
    selectedCandidates,
    totalAmount: bill.total_amount,
    billNo: bill.bill_no,
    billDate: bill.bill_date,
  }
}
