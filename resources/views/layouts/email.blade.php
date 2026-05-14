<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'EasyBooking')</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f3f4f6;
            color: #1f2937;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
        }

        .email-wrapper {
            width: 100%;
            padding: 32px 12px;
            background: #f3f4f6;
        }

        .container {
            max-width: 640px;
            margin: 0 auto;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        }

        .header {
            padding: 28px 32px;
            background: @yield('header_color', '#4F46E5');
            color: #ffffff;
            text-align: center;
        }

        .brand {
            margin: 0 0 10px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0.9;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            line-height: 1.2;
        }

        .content {
            padding: 30px 32px;
            font-size: 16px;
        }

        .content p {
            margin: 0 0 16px;
        }

        .content strong {
            color: #111827;
        }

        .appointment-details,
        .details {
            margin: 24px 0;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #f9fafb;
        }

        .appointment-details h3,
        .details h3 {
            margin: 0 0 14px;
            color: #111827;
            font-size: 18px;
        }

        .appointment-details p,
        .details p {
            margin: 8px 0;
        }

        .notice {
            margin: 22px 0;
            padding: 14px 16px;
            border-left: 4px solid @yield('header_color', '#4F46E5');
            border-radius: 10px;
            background: #f8fafc;
        }

        .footer {
            padding: 22px 32px;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
            color: #6b7280;
            text-align: center;
            font-size: 13px;
        }

        .footer p {
            margin: 0 0 8px;
        }

        @media (max-width: 600px) {
            .email-wrapper {
                padding: 16px 8px;
            }

            .container {
                border-radius: 12px;
            }

            .header,
            .content,
            .footer {
                padding-left: 20px;
                padding-right: 20px;
            }

            .header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="container">
            <div class="header">
                <p class="brand">EasyBooking</p>
                <h1>@yield('header_title')</h1>
            </div>

            <div class="content">
                @yield('content')
            </div>

            <div class="footer">
                <p><strong>EasyBooking</strong></p>
                <p>Este es un mensaje automático. Por favor, no respondas a este email.</p>
            </div>
        </div>
    </div>
</body>
</html>
