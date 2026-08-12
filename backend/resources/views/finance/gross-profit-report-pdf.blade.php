<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111827; }
        .brand-header { border-bottom: 2px solid #111827; padding-bottom: 8px; margin-bottom: 12px; }
        .brand-table { width: 100%; }
        .logo { max-height: 48px; }
        .company-info { text-align: right; }
        .company-name { font-size: 16px; font-weight: bold; }
        .company-meta { font-size: 9px; color: #4b5563; }
        .title { text-align: center; margin: 0 0 12px; }
        .title h2 { margin: 0 0 4px; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 3px 4px; }
        th { background: #f3f4f6; font-size: 8px; text-align: left; }
        td.num, th.num { text-align: right; white-space: nowrap; }
        .summary { width: 100%; margin-bottom: 12px; }
        .summary td { border: 1px solid #e5e7eb; padding: 6px 8px; width: 25%; }
        .label { color: #6b7280; font-size: 8px; }
        .value { font-size: 11px; font-weight: bold; }
        .profit { color: #047857; }
        .loss { color: #b91c1c; }
        tfoot td { background: #f3f4f6; font-weight: bold; }
        .calc { width: 420px; margin-top: 12px; margin-left: auto; border-collapse: collapse; }
        .calc td { border: none; padding: 3px 4px; }
        .calc .line { border-top: 1px dashed #9ca3af; }
        .calc .result { border-top: 1px solid #111827; font-weight: bold; font-size: 10px; }
    </style>
</head>
<body>
@php
    $headTotals = [];
    foreach ($expenseHeads as $head) {
        $headTotals[(string) $head['id']] = 0.0;
    }
    $totalAvgCre = 0.0;
    $totalExpense = 0.0;
    $totalSale = 0.0;
    $totalProfitLoss = 0.0;
    foreach ($rows as $row) {
        foreach ($expenseHeads as $head) {
            $key = (string) $head['id'];
            $headTotals[$key] += (float) ($row['expenses'][$key] ?? 0);
        }
        $totalAvgCre += (float) ($row['avg_cre'] ?? 0);
        $totalExpense += (float) ($row['total_expense'] ?? 0);
        $totalSale += (float) ($row['sale_price'] ?? 0);
        $totalProfitLoss += (float) ($row['profit_loss'] ?? 0);
    }
@endphp
    <div class="brand-header">
        <table class="brand-table">
            <tr>
                <td style="width: 110px;">
                    @if(!empty($setting['company_logo_path']))
                        <img src="{{ public_path('storage/' . $setting['company_logo_path']) }}" alt="Logo" class="logo">
                    @endif
                </td>
                <td class="company-info">
                    <div class="company-name">{{ $setting['company_name'] ?? '' }}</div>
                    <div class="company-meta">{{ $setting['company_address'] ?? '' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="title">
        <h2>Gross Profit Report</h2>
        <p>
            Generated {{ now()->format('d M Y h:i A') }}
            @if(!empty($filters['from_date']) || !empty($filters['to_date']))
                — {{ $filters['from_date'] ?? '…' }} to {{ $filters['to_date'] ?? '…' }}
            @endif
        </p>
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="label">Candidates</div>
                <div class="value">{{ number_format((int) ($summary['candidate_count'] ?? 0)) }}</div>
            </td>
            <td>
                <div class="label">Total Sale Price</div>
                <div class="value">{{ number_format((float) ($summary['total_sale'] ?? 0), 2) }}</div>
            </td>
            <td>
                <div class="label">Total Direct Expense</div>
                <div class="value">{{ number_format((float) ($summary['total_expense'] ?? 0), 2) }}</div>
            </td>
            <td>
                <div class="label">Adjusted Gross Profit / Loss</div>
                @php $adjusted = (float) ($summary['adjusted_gross_profit_loss'] ?? 0); @endphp
                <div class="value {{ $adjusted >= 0 ? 'profit' : 'loss' }}">{{ number_format($adjusted, 2) }}</div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Candidate</th>
                <th>Passport</th>
                <th>Job</th>
                @foreach($expenseHeads as $head)
                    <th class="num">{{ $head['name'] }}</th>
                @endforeach
                <th class="num">Avg C.R.E</th>
                <th class="num">Total Expense</th>
                <th class="num">Sale Price</th>
                <th class="num">Profit / Loss</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['candidate_name'] }}</td>
                    <td>{{ $row['passport_no'] ?: '—' }}</td>
                    <td>{{ trim(($row['job_code'] ?? '') . ' ' . ($row['job_name'] ?? '')) ?: '—' }}</td>
                    @foreach($expenseHeads as $head)
                        <td class="num">{{ number_format((float) ($row['expenses'][(string) $head['id']] ?? 0), 2) }}</td>
                    @endforeach
                    <td class="num">{{ number_format((float) ($row['avg_cre'] ?? 0), 2) }}</td>
                    <td class="num">{{ number_format((float) ($row['total_expense'] ?? 0), 2) }}</td>
                    <td class="num">{{ number_format((float) ($row['sale_price'] ?? 0), 2) }}</td>
                    @php $pl = (float) ($row['profit_loss'] ?? 0); @endphp
                    <td class="num {{ $pl >= 0 ? 'profit' : 'loss' }}">{{ number_format($pl, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 7 + count($expenseHeads) }}" style="text-align:center;">No candidate data found.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($rows))
            <tfoot>
                <tr>
                    <td>Total ({{ count($rows) }})</td>
                    <td>—</td>
                    <td>—</td>
                    @foreach($expenseHeads as $head)
                        <td class="num">{{ number_format($headTotals[(string) $head['id']] ?? 0, 2) }}</td>
                    @endforeach
                    <td class="num">{{ number_format($totalAvgCre, 2) }}</td>
                    <td class="num">{{ number_format($totalExpense, 2) }}</td>
                    <td class="num">{{ number_format($totalSale, 2) }}</td>
                    <td class="num {{ $totalProfitLoss >= 0 ? 'profit' : 'loss' }}">{{ number_format($totalProfitLoss, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    @php
        $rejectedExpense = (float) ($summary['rejected_declined_expense'] ?? 0);
        $rejectedCount = (int) ($summary['rejected_declined_count'] ?? 0);
        $lessDlExpense = (float) ($summary['less_dl_expense'] ?? 0);
        $lessDlCount = (int) ($summary['less_dl_count'] ?? 0);
        $activeProfitLoss = (float) ($summary['total_profit_loss'] ?? $totalProfitLoss);
        $adjusted = (float) ($summary['adjusted_gross_profit_loss'] ?? ($activeProfitLoss - $rejectedExpense - $lessDlExpense));
    @endphp
    <table class="calc">
        <tr>
            <td>Total Profit / Loss (Active Candidates)</td>
            <td class="num {{ $activeProfitLoss >= 0 ? 'profit' : 'loss' }}">{{ number_format($activeProfitLoss, 2) }}</td>
        </tr>
        <tr>
            <td>
                Less: Rejected / Declined Candidate Expense
                ({{ $rejectedCount }} candidate{{ $rejectedCount === 1 ? '' : 's' }})
            </td>
            <td class="num loss">− {{ number_format($rejectedExpense, 2) }}</td>
        </tr>
        <tr>
            <td>
                Less: DL Expenses
                ({{ $lessDlCount }} DL{{ $lessDlCount === 1 ? '' : 's' }} with no ATS candidate)
            </td>
            <td class="num loss">− {{ number_format($lessDlExpense, 2) }}</td>
        </tr>
        <tr>
            <td class="result">Adjusted Gross Profit / Loss</td>
            <td class="num result {{ $adjusted >= 0 ? 'profit' : 'loss' }}">{{ number_format($adjusted, 2) }}</td>
        </tr>
    </table>
</body>
</html>
