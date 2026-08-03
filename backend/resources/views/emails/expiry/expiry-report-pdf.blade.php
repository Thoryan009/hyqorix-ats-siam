```blade
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
        }

        .brand-header {
            border-bottom: 2px solid #000000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .brand-table {
            width: 100%;
        }

        .logo-cell {
            width: 100px;
            text-align: center;
        }

        .logo {
            max-height: 60px;
        }

        .company-info {
            text-align: right;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #000000;
        }

        .company-meta {
            font-size: 10px;
            color: #4b5563;
            line-height: 1.5;
        }

        .report-title {
            text-align: center;
            margin: 15px;
        }

        .report-title h2 {
            margin: 0;
            font-size: 18px;
        }

        .meta-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 8px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f3f4f6;
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 8px;
        }

        table td {
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 7px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }
    </style>
</head>

<body>

    <div class="brand-header">

        <table class="brand-table">

            <tr>

                <td class="logo-cell">

                    @if(!empty($setting?->company_logo_path))
                        <img
                            src="{{ public_path('storage/' . $setting->company_logo_path) }}"
                            alt="Logo"
                            class="logo">
                    @endif

                </td>

                <td class="company-info">

                    <div class="company-name">
                        {{ $setting?->company_name }}
                    </div>

                    <div class="company-meta">
                        {{ $setting?->company_address }}
                    </div>

                </td>

            </tr>

        </table>

    </div>

    <div class="report-title">
        <h2>Expiry Report</h2>
    </div>

    <div class="meta-box">

        <table>

            <tr>

                <td style="font-size:14px" width="50%">
                    <strong>Department:</strong>
                    {{ $department->name }}
                </td>

                <td style="font-size:14px" width="50%">
                    <strong>Date:</strong>
                    {{ \Carbon\Carbon::parse($reportDate)->format('d M Y') }}
                </td>

            </tr>

            <tr>

                <td style="font-size:14px" width="50%">
                    <strong>Total Records:</strong>
                    {{ $rows->count() }}
                </td>

                <td></td>

            </tr>

        </table>

    </div>

    <table>

        <thead>

            <tr>
                <th>Passport No</th>
                <th>Candidate Name</th>
                <th>Mobile</th>
                <th>Agent Name</th>
                <th>Client Name</th>
                <th>Job Name</th>
                <th>Agent Mobile</th>
                <th>Document</th>
                <th>Expiry Date</th>
                <th>Days Left</th>
                <th>Current Process</th>
            </tr>

        </thead>

        <tbody>

            @forelse($rows as $row)

                <tr>

                    <td>
                        {{ $row['passport_no'] ?? '-' }}
                    </td>

                    <td>
                        {{ $row['candidate_name'] ?? '-' }}
                    </td>

                    <td>
                        {{ $row['mobile'] ?? '-' }}
                    </td>

                    <td>
                        {{ $row['agent_name'] ?? '-' }}
                    </td>

                    <td>
                        {{ $row['client_name'] ?? '-' }}
                    </td>

                    <td>
                        {{ $row['job_name'] ?? '-' }}
                    </td>

                    <td>
                        {{ $row['agent_mobile_no'] ?? '-' }}
                    </td>

                    <td>
                        {{ $row['document'] ?? '-' }}
                    </td>

                    <td>
                        {{ $row['expiry_date_formatted']
                            ?? $row['expiry_date']
                            ?? '-' }}
                    </td>

                    <td>
                        {{ $row['days_left'] ?? '-' }}
                    </td>

                    <td>
                        {{ $row['current_process'] ?? '-' }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="11" style="text-align:center;">
                        No records found
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

    <div class="footer">
        Generated automatically at
        {{ now()->format('d M Y h:i A') }}
        by ATS System
    </div>

</body>

</html>
