<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Başvurunuz Değerlendirildi</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background-color: #ffffff; padding: 20px; border-radius: 8px;">
        <h2 style="color: #dc3545;">Başvurunuz Sonuçlandı</h2>

        <p><strong>Sayın {{ $candidate->name }},</strong></p>

        <p>Başvurunuzu büyük bir titizlikle inceledik. Ne yazık ki şu an için uygun olmadığınıza karar verdik.</p>

        <p>İleride doğabilecek uygun pozisyonlarda sizinle tekrar iletişime geçmek isteriz.</p>

        <hr>
        <p style="font-size: 12px; color: #888;">Bu e-posta otomatik olarak oluşturulmuştur, lütfen yanıtlamayınız.</p>
    </div>
</body>

</html>
