<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>

        body{
            font-family: DejaVu Sans, sans-serif;
            font-size:10px;
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

        table{
            width:100%;
            border-collapse:collapse;
        }

        th{
            border:1px solid #ddd;
            background:#f3f4f6;
            padding:4px;
            font-size:8px;
        }

        td{
            border:1px solid #ddd;
            padding:4px;
            font-size:8px;
        }

        .title{
            text-align:center;
            margin-bottom:15px;
        }

        .card{
            display:inline-block;
            width:120px;
            border:1px solid #ddd;
            padding:8px;
            margin:4px;
            text-align:center;
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
<div class="title">

    <h2>ATS Summary Report</h2>

    <p>
        Generated At:
        {{ now()->format('d M Y h:i A') }}
    </p>

</div>
<h3>
    ATS Process Summary
</h3>

<table>

    <tr>

        @foreach($cards as $name => $count)

            <td style="text-align:center">

                <strong>
                    {{ ucwords(str_replace('_', ' ', $name)) }}
                </strong>

                <br>

                {{ number_format($count) }}

            </td>

        @endforeach

    </tr>

</table>

<table>
    <caption style="caption-side: top; text-align: left; font-weight: bold; margin-top: 30px; margin-bottom: 10px;">ATS All Process Individual Counts</caption>
    <thead>

    <tr>

        <th>SL</th>
        <th>Client</th>
        <th>Job</th>

        @foreach($processes as $process)

            <th>
                {{ ucwords(str_replace('_', ' ', $process)) }}
            </th>

        @endforeach

    </tr>

    </thead>

    <tbody>

    @foreach($rows as $row)

        <tr>

            <td>
                {{ $loop->iteration }}
            </td>

            <td>
                {{ $row['client_name'] }}
            </td>

            <td>
                {{ $row['job_name'] }}
          
            </td>

            @foreach($processes as $process)

                <td>
                    {{ $row[$process] }}
                </td>

            @endforeach

        </tr>

    @endforeach

    </tbody>

    <tfoot>
        <tr>
            <td colspan="{{ 3 + count($processes) }}" style="text-align:center; font-weight:bold;">
                Total Active Applications: {{ number_format($grandTotal) }}
            </td>
        </tr>
    </tfoot>

</table>

<br><br>



<br>

<p>



    <strong>
       Note:
    </strong>

    Candidates in the TRA Process stage have received their travel tickets and are currently awaiting departure.

</p>

</body>

</html>
