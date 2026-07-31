<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 32px;">
    <div style="max-width: 500px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 32px;">
        <h1 style="font-size: 20px; color: #222;">La tua pagina è pronta! 🎉</h1>
        <p style="color: #555; line-height: 1.6;">
            La pagina <strong>{{ $title }}</strong> è stata pubblicata con successo ed è pronta per essere condivisa.
        </p>
        <p style="text-align: center; margin: 32px 0;">
            <a href="{{ $pageUrl }}" style="background: #6d28d9; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; display: inline-block;">
                Visualizza la pagina
            </a>
        </p>
        <p style="color: #777; font-size: 14px; word-break: break-all;">
            Oppure copia il link: {{ $pageUrl }}
        </p>
    </div>
</body>
</html>