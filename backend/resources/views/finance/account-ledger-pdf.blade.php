<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111827; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 3px 4px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; font-size: 8px; }
        td.num, th.num { text-align: right; white-space: nowrap; }
        .title { text-align: center; margin: 0 0 10px; }
        .title h2 { margin: 0 0 4px; font-size: 16px; }
        .period { font-size: 9px; color: #4b5563; }
        .header { border-bottom: 2px solid #111827; padding-bottom: 6px; margin-bottom: 10px; }
        .header .meta { text-align: right; font-size: 9px; color: #4b5563; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">
            <h2>Account Ledger</h2>
            <div class="period">
                {{ $accountName }} @if(!empty($accountLabel)) — {{ $accountLabel }} @endif
                @if(!empty($fromDate) || !empty($toDate))
                    | Period:
                    {{ !empty($fromDate) ? $fromDate : 'Start' }} to {{ !empty($toDate) ? $toDate : 'End' }}
                @endif
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Particular</th>
                <th>Voucher No</th>
                <th>Demand Letter</th>
                <th>Job</th>
                <th>Reference</th>
                <th class="num">{{ $mode['drLabel'] }}</th>
                <th class="num">Discount</th>
                <th class="num">{{ $mode['crLabel'] }}</th>
                <th>Payment Method</th>
                <th class="num">{{ $mode['amountLabel'] }}</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['particular'] }}</td>
                    <td>{{ $row['voucher_no'] ?: '—' }}</td>
                    <td>{{ $row['demand_letter'] ?: '—' }}</td>
                    <td>{{ $row['job'] ?: '—' }}</td>
                    <td>{{ $row['client_name'] ?: '—' }}</td>
                    <td class="num">{{ $row['dr_amount'] ?: '-' }}</td>
                    <td class="num">{{ $row['discount'] ?: '-' }}</td>
                    <td class="num">{{ $row['cr_amount'] ?: '-' }}</td>
                    <td>{{ $row['payment_method'] ?: '-' }}</td>
                    <td class="num">{{ $row['amount'] ?: '-' }}</td>
                    <td>{{ $row['remarks'] ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align:center;">No ledger entries found for the selected date range.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

