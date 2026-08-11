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
        .totals { background: #f9fafb; font-weight: bold; }
    </style>
</head>
<body>
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
    </table>

    <p style="margin-top: 10px;">
        Rejected / Declined expense ({{ (int) ($summary['rejected_declined_count'] ?? 0) }}):
        {{ number_format((float) ($summary['rejected_declined_expense'] ?? 0), 2) }}
        &nbsp;|&nbsp;
        Total Profit / Loss:
        {{ number_format((float) ($summary['total_profit_loss'] ?? 0), 2) }}
    </p>
</body>
</html>
