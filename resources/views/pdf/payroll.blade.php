<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Maaş Bordrosu</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        h2 {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <h2>Maaş Bordrosu – {{ $payroll->period }}</h2>

    <p><strong>Çalışan:</strong> {{ $payroll->employee->user->name }}</p>
    <p><strong>Yayın Tarihi:</strong> {{ \Carbon\Carbon::parse($payroll->issued_at)->format('d.m.Y') }}</p>

    <table>
        <tr>
            <th>Brüt Maaş</th>
            <td>{{ number_format($payroll->gross_salary, 2) }} ₺</td>
        </tr>
        <tr>
            <th>Kesintiler</th>
            <td>{{ number_format($payroll->deductions, 2) }} ₺</td>
        </tr>
        <tr>
            <th>Ek Ödemeler</th>
            <td>{{ number_format($payroll->bonuses, 2) }} ₺</td>
        </tr>
        <tr>
            <th>Net Maaş</th>
            <td><strong>{{ number_format($payroll->net_salary, 2) }} ₺</strong></td>
        </tr>
    </table>
</body>

</html>
