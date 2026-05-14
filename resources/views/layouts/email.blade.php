@php($accentColor = trim($__env->yieldContent('header_color', '#4F46E5')))

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'EasyBooking')</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;color:#1f2937;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;background-color:#f3f4f6;margin:0;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellpadding="0" cellspacing="0" style="width:100%;max-width:640px;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:18px;overflow:hidden;">
                    <tr>
                        <td align="center" style="background-color:{{ $accentColor }};padding:30px 32px;color:#ffffff;">
                            <p style="margin:0 0 10px;font-size:13px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#ffffff;">EasyBooking</p>
                            <h1 style="margin:0;color:#ffffff;font-size:28px;line-height:1.2;font-weight:800;">@yield('header_title')</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px 32px;color:#1f2937;font-size:16px;line-height:1.65;">
                            @yield('content')
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:22px 32px;background-color:#f9fafb;border-top:1px solid #e5e7eb;color:#6b7280;font-size:13px;line-height:1.5;">
                            <p style="margin:0 0 8px;color:#374151;font-weight:700;">EasyBooking</p>
                            <p style="margin:0;">Este es un mensaje automático. Por favor, no respondas a este email.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
