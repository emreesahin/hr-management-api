<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Maaş Bordrosu</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: #333;
            margin: 40px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }

        .logo {
            width: 150px;
        }

        .company-info {
            text-align: right;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
        }

        table th {
            background-color: #f5f5f5;
        }

        .footer {
            margin-top: 50px;
            font-size: 12px;
            text-align: center;
            color: #888;
        }
    </style>
</head>

<body>

    <header>
        <div>
            <img src="{{ public_path('images/company-logo.png') }}" alt="Logo" class="logo">
        </div>
        <div class="company-info">
            <strong>{{ config('app.name') }}</strong><br>
            {{ now()->format('d.m.Y') }}
        </div>
    </header>

    <h1>Maaş Bordrosu</h1>

    <p><strong>Çalışan:</strong> {{ $payroll->employee->user->name }}<br>
        <strong>E-posta:</strong> {{ $payroll->employee->user->email }}<br>
        <strong>Dönem:</strong> {{ $payroll->period }}
    </p>

    <table>
        <tr>
            <th>Brüt Maaş</th>
            <td>{{ number_format($payroll->gross_salary, 2, ',', '.') }} ₺</td>
        </tr>
        <tr>
            <th>Vergi</th>
            <td>{{ number_format($payroll->tax, 2, ',', '.') }} ₺</td>
        </tr>
        <tr>
            <th>Kesintiler</th>
            <td>{{ number_format($payroll->deductions, 2, ',', '.') }} ₺</td>
        </tr>
        <tr>
            <th>Net Maaş</th>
            <td><strong>{{ number_format($payroll->net_salary, 2, ',', '.') }} ₺</strong></td>
        </tr>
    </table>

    <div class="footer">
        Bu belge {{ config('app.name') }} tarafından oluşturulmuştur.
    </div>

</body>

</html>
