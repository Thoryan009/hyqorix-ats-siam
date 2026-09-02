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
        <h2>Gross Profit Breakdown</h2>
        <p>
            Generated {{ now()->format('d M Y h:i A') }}
            @if(!empty($from_date) || !empty($to_date))
                — {{ $from_date ?? '…' }} to {{ $to_date ?? '…' }}
            @endif
        </p>
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="label">Candidates</div>
                <div class="value">{{ number_format(count($rows)) }}</div>
            </td>
            <td>
                <div class="label">Total Revenue</div>
                <div class="value">{{ number_format((float) ($summary['total_revenue'] ?? 0), 2) }}</div>
            </td>
            <td>
                <div class="label">Total Direct Cost</div>
                <div class="value">{{ number_format((float) ($summary['total_direct_cost'] ?? 0), 2) }}</div>
            </td>
            <td>
                <div class="label">Gross Profit</div>
                @php $grossProfit = (float) ($summary['gross_profit'] ?? 0); @endphp
                <div class="value {{ $grossProfit >= 0 ? 'profit' : 'loss' }}">{{ number_format($grossProfit, 2) }}</div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Passport</th>
                <th>Candidate</th>
                <th>Job</th>
                <th>Demand Letter</th>
                <th>Client</th>
                <th>Agent</th>
                <th>Principal</th>
                <th class="num">Revenue</th>
                <th class="num">Direct Cost</th>
                <th class="num">Gross Profit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php $rowProfit = (float) ($row['gross_profit'] ?? 0); @endphp
                <tr>
                    <td>{{ $row['passport_no'] ?? '—' }}</td>
                    <td>{{ $row['candidate_name'] ?? '—' }}</td>
                    <td>{{ $row['job_name'] ?? '—' }}</td>
                    <td>{{ $row['demand_letter'] ?? '—' }}</td>
                    <td>{{ $row['client_name'] ?? '—' }}</td>
                    <td>{{ $row['agent_name'] ?? '—' }}</td>
                    <td>{{ $row['principal_name'] ?? '—' }}</td>
                    <td class="num">{{ number_format((float) ($row['revenue'] ?? 0), 2) }}</td>
                    <td class="num">{{ number_format((float) ($row['direct_cost'] ?? 0), 2) }}</td>
                    <td class="num {{ $rowProfit >= 0 ? 'profit' : 'loss' }}">{{ number_format($rowProfit, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 12px;">No candidate gross profit data found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($rows) > 0)
            <tfoot>
                <tr>
                    <td colspan="7">Total</td>
                    <td class="num">{{ number_format((float) ($summary['total_revenue'] ?? 0), 2) }}</td>
                    <td class="num">{{ number_format((float) ($summary['total_direct_cost'] ?? 0), 2) }}</td>
                    <td class="num {{ $grossProfit >= 0 ? 'profit' : 'loss' }}">{{ number_format($grossProfit, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
