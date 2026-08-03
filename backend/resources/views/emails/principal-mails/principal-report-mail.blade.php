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

.report-wrapper {
    border: 1px solid #d1d5db;
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

.report-date {
    font-size: 10px;
    color: #6b7280;
}

.footer {
    margin-top: 25px;
    border-top: 2px solid #1e40af;
    padding-top: 10px;
    text-align: center;
    font-size: 9px;
    color: #6b7280;
}

.footer-company {
    font-weight: bold;
    color: #1e40af;
}

.meta-box {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    padding: 8px;
    margin-bottom: 15px;
}

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
        }

        .meta {
            margin-bottom: 20px;
        }

        .meta table {
            width: 100%;
        }

        .meta td {
            padding: 4px 4px;
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
            color: #666;
            font-size: 11px;
        }
    </style>
</head>

<body>

  <div class="brand-header">

    <table class="brand-table">

        <tr>

            <td class="logo-cell">

                @if(!empty($setting['company_logo_path']))
                   <img src="{{ public_path('storage/' . $setting['company_logo_path']) }}" alt="Logo" class="logo">
                @endif

            </td>

            <td class="company-info">

                <div class="company-name">
                    {{ $setting['company_name'] }}
                </div>

                <div class="company-meta">
                    {{ $setting['company_address'] }}<br>

                </div>

            </td>

        </tr>

    </table>

</div>

<div class="report-title">

    <h2>Applicant Report</h2>


</div>

  <div class="meta-box">

    <table>

        <tr>

            <td style="font-size: 14px" width="50%">
                <strong>Principal Name:</strong>
                {{ $principal->user->name }}
            </td>

            <td style="font-size: 14px" width="50%">
                <strong>Total Applications:</strong>
                {{ $applications->count() }}
            </td>

        </tr>

    </table>

</div>

    <table>

        <thead>
            <tr>
                <th>#</th>
                <th>Job Name</th>
                <th>Name</th>
                <th>Passport</th>
                <th>Mobile</th>
                <th>Sex</th>
                <th>Country</th>
                <th>Client</th>
                <th>P. Days</th>
                <th>Total P. Days</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($applications as $application)
                <tr>

                    <td>{{ $loop->iteration }}</td>



                     <td>
                        {{ $application->jobList?->name }}
                    </td>
                    <td>
                        {{ $application->given_name }} {{ $application->sur_name }}
                    </td>
                    <td>
                        {{ $application->passport_no }}
                    </td>
                    <td>
                        {{ $application->mobile }}
                    </td>
                    <td>
                        {{ $application->sex }}
                    </td>

                    <td>
                        {{ $application->jobList?->workOrder?->client?->country?->name }}
                    </td>


                    <td>
                        {{ \Illuminate\Support\Str::limit($application->jobList?->workOrder?->client?->user?->name, 26, '...') }}
                    </td>

                    <td>
                        {{ $application->currentProcessDays() }}
                    </td>

                    <td>
                        {{ $application->totalProcessDays() }}
                    </td>

                    <td>
                        {{ ucfirst($application->application_status) }}
                    </td>

                    <td>
                        {{ $application->currentProcess?->remarks }}
                    </td>



                </tr>
            @endforeach

        </tbody>

    </table>



    <div style="margin-top:30px; text-align:center; color:#6b7280; font-size:10px;">
        Generated automatically at {{ now()->format('d M Y h:i A') }} by ATS System
    </div>

</div>

</body>

</html>
