<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Yeni İzin Talebi Geldi</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            background-color: #f7f7f7;
            padding: 30px;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #2980b9;
            margin-bottom: 20px;
        }

        .info {
            margin-bottom: 15px;
            font-size: 15px;
        }

        .info strong {
            display: inline-block;
            width: 150px;
            color: #555;
        }

        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: #999;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .highlight {
            font-weight: bold;
            background-color: #d9ecff;
            padding: 5px 8px;
            border-radius: 4px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>📩 Yeni İzin Talebi Geldi</h2>

        <div class="info"><strong>Çalışan:</strong> {{ $employee->user->name }} ({{ $employee->user->email }})</div>
        <div class="info"><strong>Başlangıç Tarihi:</strong>
            {{ \Carbon\Carbon::parse($leave->start_date)->format('d.m.Y') }}</div>
        <div class="info"><strong>Bitiş Tarihi:</strong> {{ \Carbon\Carbon::parse($leave->end_date)->format('d.m.Y') }}
        </div>
        <div class="info"><strong>Süre:</strong> <span class="highlight">{{ $leave->duration }} gün</span></div>
        <div class="info"><strong>Gerekçe:</strong> {{ $leave->reason }}</div>

        <div class="footer">Bu talebi onaylamak veya reddetmek için {{ config('app.name') }} sistemine giriş yapınız.
        </div>
    </div>
</body>

</html>
