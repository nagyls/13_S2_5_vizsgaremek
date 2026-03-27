<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Megerősítés</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
            color: #333;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .brand-logo {
            width: 84px;
            height: auto;
            margin: 0 auto 14px;
            display: block;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }

        .message {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.8;
        }

        .button-container {
            text-align: center;
            margin: 40px 0;
        }

        .verify-button {
            display: inline-block;
            padding: 14px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .verify-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .alternative-text {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #999;
        }

        .link-display {
            word-break: break-all;
            font-size: 11px;
            color: #666;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
            font-family: 'Courier New', monospace;
        }

        .footer {
            background-color: #f9f9f9;
            padding: 30px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #eee;
        }

        .footer-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .security-note {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 13px;
            color: #856404;
        }

        .expiry-notice {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 13px;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="email-container">

        <div class="header">
            <img src="cid:logo" alt="EseményTér logó" class="brand-logo">
            <h1>Üdvözöljük az EseményTérben!</h1>
            <p>Email cím megerősítése</p>
        </div>


        <div class="content">
            <div class="greeting">
                Hello {{ $notifiable->name }}!
            </div>

            <div class="message">
                <p>Köszönünk, hogy regisztráltál az EseményTérben!</p>
                <p style="margin-top: 15px;">
                    Az email címed megerősítéséhez kattints az alábbi gombra. Ez egy fontos lépés a fiók biztonságához.
                </p>
            </div>

            <div class="button-container">
                <a href="{{ $actionUrl }}" class="verify-button">
                    Email megerősítése
                </a>
            </div>


            <div class="security-note">
                <strong>Biztonsági megjegyzés:</strong><br>
                Ha nem Te regisztráltál, kérjük, hagyj figyelmen kívül erre az emailt. A link 24 óra múlva lejár.
            </div>

            <div class="expiry-notice">
                <strong>Fontos:</strong> Ez a megerősítési link 24 óra múlva lejár. Addig végezd el a megerősítést!
            </div>

            <div class="alternative-text">
                <p style="margin-bottom: 10px;">Ha a gomb nem működik, másold be ezt az URL-t a böngészőbe:</p>
                <div class="link-display">
                    {{ $actionUrl }}
                </div>
            </div>
        </div>

        <div class="footer">
            <p style="margin-bottom: 15px;">
                © {{ date('Y') }} EseményTér. Minden jog fenntartva.
            </p>
            <div class="footer-links">
                <a href="https://esemenyter.hu/privacy">Adatvédelem</a>
                <a href="https://esemenyter.hu/aszf">ÁSZF</a>
                <a href="https://esemenyter.hu/support">Támogatás</a>
            </div>
            <p style="margin-top: 15px; color: #bbb;">
                Ez az email cím nem fogad válaszokat. Kérdéseid esetén <br>
                lépj kapcsolatba a <a href="mailto:support@esemenyter.hu" style="color: #667eea;">support@esemenyter.hu</a> címmel.
            </p>
        </div>
    </div>
</body>
</html>
