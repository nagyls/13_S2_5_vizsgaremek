<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email megerosites</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
        }
        .card {
            width: min(92vw, 560px);
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 28px;
        }
        h1 { margin-top: 0; font-size: 26px; }
        p { line-height: 1.5; }
        .ok { color: #047857; }
        .warning { color: #b45309; }
        .error { color: #b91c1c; }
    </style>
</head>
<body>
    <div class="card">
        @if ($status === 'success')
            <h1 class="ok">Sikeres email megerosites</h1>
            <p>Az email cimed sikeresen megerositesre kerult. Most mar be tudsz jelentkezni.</p>
        @elseif ($status === 'already-verified')
            <h1 class="ok">Email mar megerositve</h1>
            <p>Ez az email cim mar korabban meg lett erositve.</p>
        @elseif ($status === 'expired')
            <h1 class="warning">A megerosito link lejart</h1>
            <p>Kerd ujra a megerosito emailt a rendszerben.</p>
        @else
            <h1 class="error">Ervenytelen megerosito link</h1>
            <p>A link hibas vagy mar nem hasznalhato.</p>
        @endif
    </div>
</body>
</html>
